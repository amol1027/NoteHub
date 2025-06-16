<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Database Connection
$host = 'localhost';
$db_user = 'root';
$password = '';
$database = 'justclick';

$conn = new mysqli($host, $db_user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// User Data
$user = $_SESSION['user'];
$username = $user['username'];


// Fetch user's college from users table
$userCollege = '';
$username = $conn->real_escape_string($username);
$userQuery = $conn->query("SELECT collegeName FROM users WHERE username = '$username'");
if ($userQuery && $userQuery->num_rows > 0) {
    $userData = $userQuery->fetch_assoc();
    $userCollege = $userData['collegeName'] ?? '';
}

// Check if college exists in college table
$collegeExists = false;
if (!empty($userCollege)) {
    $escapedCollege = $conn->real_escape_string($userCollege);
    $collegeCheck = $conn->query("SELECT COUNT(*) AS count FROM college WHERE col_name = '$escapedCollege'");
    if ($collegeCheck && $collegeCheck->fetch_assoc()['count'] > 0) {
        $collegeExists = true;
    }
}




// Check for session messages and clear them
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message'], $_SESSION['message_type']);

// Password Change Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current-password'])) {
    $currentPassword = $_POST['current-password'];
    $newPassword = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];

    if ($newPassword !== $confirmPassword) {
        $_SESSION['message'] = "New password and confirm password do not match.";
        $_SESSION['message_type'] = "error";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if ($currentPassword === $row['password']) {
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $updateStmt->bind_param("ss", $newPassword, $username);

                if ($updateStmt->execute()) {
                    $_SESSION['message'] = "Password updated successfully!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Error updating password. Please try again.";
                    $_SESSION['message_type'] = "error";
                }
            } else {
                $_SESSION['message'] = "Current password is incorrect.";
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Error updating password. Please try again.";
            $_SESSION['message_type'] = "error";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?section=change-password");
    exit();
}



// Link Processing Function
function processLink($link) {
    if (preg_match("/youtu(?:\.be|be\.com)\/(?:watch\?v=|embed\/)?([a-zA-Z0-9\-_]+)(?:[&?\/]t=(\d+))?/i", $link, $matches)) {
        $videoId = $matches[1];
        $startTime = $matches[2] ?? 0;
        return "<iframe class='w-full h-full border border-gray-300 rounded-md' 
                src='https://www.youtube.com/embed/$videoId?start=$startTime' 
                allowfullscreen></iframe>";
    }
    return "<a href='" . htmlspecialchars($link) . "' target='_blank' class='text-blue-500 underline'>" . htmlspecialchars($link) . "</a>";
}

// Handle Video Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_id'])) {
    header('Content-Type: application/json');
    
    $response = ['status' => 'error', 'message' => 'Unknown error'];
    $record_id = (int)$_POST['record_id'];
    $username = $_SESSION['user']['username'];

    try {
        $delete_stmt = $conn->prepare("DELETE FROM homedata 
                                     WHERE id = ? 
                                     AND username = ?
                                     AND status = 'approved'");
        $delete_stmt->bind_param("is", $record_id, $username);
        
        if ($delete_stmt->execute()) {
            $affected_rows = $delete_stmt->affected_rows;
            
            if ($affected_rows > 0) {
                $response = [
                    'status' => 'success',
                    'message' => 'Record deleted successfully',
                    'deleted_id' => $record_id
                ];
            } else {
                $response = ['status' => 'info', 'message' => 'No record found to delete'];
            }
        } else {
            throw new Exception($delete_stmt->error);
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Deletion failed: ' . $e->getMessage()];
    }

    echo json_encode($response);
    exit();
}
// Fetch User Videos
// Change the video query to include LIMIT
$is_approved = "approved";
$video_query = $conn->prepare("SELECT id, username, description, links, timestamp, like_count 
                              FROM homedata 
                              WHERE username = ? and status = ?
                              ORDER BY timestamp DESC 
                              LIMIT 6");
$video_query->bind_param("ss", $username,$is_approved);
$video_query->execute();
$result = $video_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Base styles */
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        .profile-card:hover .profile-icon {
            transform: rotateY(180deg);
        }
        .animate-slideIn {
            animation: slideIn 0.6s forwards;
        }
        .bg-gradient-header {
            background: linear-gradient(to right, #1e3a8a, #2563eb);
        }
        .profile-stat-card {
            transition: all 0.3s ease;
        }
        .profile-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .tab-active {
            border-bottom: 3px solid #3b82f6;
            color: #1e40af;
            font-weight: 600;
        }
        
        /* Custom navigation styles */
        .main-nav {
            padding: 0 !important;
            margin: 0 !important;
            box-sizing: border-box;
        }
        .logo-link {
            padding: 0 !important;
            margin: 0 !important;
            box-sizing: border-box;
            padding-left: 0 !important;
            margin-left: 0 !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Add this after the body opening tag -->
<div id="message-data" data-message="<?= htmlspecialchars($message) ?>" data-type="<?= htmlspecialchars($message_type) ?>"></div>

<div class="fixed top-0 left-0 w-full z-50" style="padding: 0; margin: 0;">
    <div class="bg-[#000] shadow-lg w-full" style="padding: 0; margin: 0;">
        <div style="display: flex; align-items: center; height: 70px; padding-right: 15px;">
            <a href="../index.php" style="padding: 0; font-weight: bold; font-size: 28px; color: white;" class="ml-4">NoteHub</a>
            <div style="margin-left: auto; display: flex; align-items: center;">
                <div class="hidden md:flex space-x-4">
                    <a href="../home.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md transition-all duration-300">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="stud_prof.php" class="bg-gray-900 text-white px-3 py-2 rounded-md">
                        <i class="fas fa-user-circle mr-2"></i>Profile
                    </a>
                    <?php if ($collegeExists): ?>
                    <a href="../documents.php" class="text-gray-300 hover:text-white px-3 py-2 rounded-md transition-all duration-300">
                        <i class="fas fa-university mr-2"></i>My College
                    </a>
                    <?php endif; ?>
                </div>
                <div class="md:hidden ml-2">
                    <button id="mobile-menu-button" class="text-gray-300 hover:text-white">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                <a href="../student/log_out.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-all duration-300 flex items-center ml-4">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-gray-900 pb-4 px-4">
            <a href="../home.php" class="block text-gray-300 hover:text-white px-3 py-2 rounded-md my-1">
                <i class="fas fa-home mr-2"></i>Home
            </a>
            <a href="stud_prof.php" class="block bg-gray-800 text-white px-3 py-2 rounded-md my-1">
                <i class="fas fa-user-circle mr-2"></i>Profile
            </a>
            <?php if ($collegeExists): ?>
            <a href="../documents.php" class="block text-gray-300 hover:text-white px-3 py-2 rounded-md my-1">
                <i class="fas fa-university mr-2"></i>My College
            </a>
            <?php endif; ?>
            
        </div>
    </div>
</div>

    <main class="max-w-7xl mx-auto px-4 pt-24 pb-10">
        <!-- Profile Tabs -->
        <div class="mb-6 bg-white rounded-xl shadow-md p-2 flex justify-center space-x-6">
            <button onclick="showSection('user-profile')" class="tab-button px-4 py-2 rounded-md transition-all duration-300 tab-active" data-section="user-profile">
                <i class="fas fa-user mr-2"></i>Profile
            </button>
            <button onclick="showSection('videos')" class="tab-button px-4 py-2 rounded-md transition-all duration-300" data-section="videos">
                <i class="fas fa-video mr-2"></i>Videos
            </button>
            <button onclick="showSection('change-password')" class="tab-button px-4 py-2 rounded-md transition-all duration-300" data-section="change-password">
                <i class="fas fa-lock mr-2"></i>Password
            </button>
        </div>
        
        <!-- Profile Section -->
        <section id="user-profile" class="mb-10" data-aos="fade-up">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-header text-white p-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                        <div class="relative group">
                            <div class="w-28 h-28 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-4xl 
                                     text-white profile-icon transition-all duration-500 shadow-lg border-4 border-white/30">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="text-center md:text-left">
                            <h1 class="text-3xl font-bold tracking-wide"><?= htmlspecialchars($user['firstName'].' '.$user['lastname']) ?></h1>
                            <p class="text-blue-100 mt-1 flex items-center justify-center md:justify-start">
                                <i class="fas fa-graduation-cap mr-2"></i><?= htmlspecialchars($user['collegeName']) ?>
                            </p>
                            <div class="mt-3 flex flex-wrap justify-center md:justify-start gap-2">
                                <span class="bg-blue-700/50 text-white text-sm px-3 py-1 rounded-full">
                                    <i class="fas fa-code-branch mr-1"></i><?= htmlspecialchars($user['branch']) ?>
                                </span>
                                <span class="bg-blue-700/50 text-white text-sm px-3 py-1 rounded-full">
                                    <i class="fas fa-user-graduate mr-1"></i>Student
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Personal Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php foreach([
                            'Username' => ['icon' => 'user-tag', 'value' => $user['username'], 'color' => 'blue'],
                            'Email' => ['icon' => 'envelope', 'value' => $user['email'], 'color' => 'indigo'],
                            'Phone' => ['icon' => 'mobile-alt', 'value' => $user['contact'], 'color' => 'purple'],
                            'Date of Birth' => ['icon' => 'calendar-day', 'value' => $user['DoB'], 'color' => 'pink'],
                            'College' => ['icon' => 'university', 'value' => $user['collegeName'], 'color' => 'green'],
                            'Branch' => ['icon' => 'code-branch', 'value' => $user['branch'], 'color' => 'yellow']
                        ] as $title => $data): ?>
                        <div class="profile-stat-card bg-white p-5 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                            <div class="flex items-center">
                                <div class="bg-<?= $data['color'] ?>-100 w-12 h-12 rounded-lg flex items-center justify-center mr-4">
                                    <i class="fas fa-<?= $data['icon'] ?> text-<?= $data['color'] ?>-600 text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 mb-1"><?= $title ?></p>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($data['value']) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
        
<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative z-10 transform transition-all">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Confirm Deletion</h3>
        <p class="mb-6 text-gray-600">Are you sure you want to delete this video? This action cannot be undone.</p>
        <form id="deleteForm" method="POST" class="flex justify-end space-x-4">
            <input type="hidden" name="record_id" id="deleteRecordId" value="">
            <button type="button" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition-colors" onclick="closeDeleteModal()">Cancel</button>
            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">Delete</button>
        </form>
    </div>
</div>

        <!-- Videos Section -->
        <section id="videos" class="mb-10 hidden" data-aos="fade-up">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-header text-white p-6">
                    <h2 class="text-2xl font-bold"><i class="fas fa-video mr-3"></i>My Videos</h2>
                    <p class="text-blue-100 text-sm mt-1">Manage your uploaded videos</p>
                </div>
                <div class="p-8">
                    <?php if ($result->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 overflow-hidden" data-video-id="<?= $row['id'] ?>">
                            <div class="aspect-video bg-gray-100 rounded-t-lg overflow-hidden">
                                <?= processLink($row['links']) ?>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-medium text-gray-800 line-clamp-2"><?= htmlspecialchars($row['description']) ?></h3>
                                    <button data-delete-button data-id="<?= $row['id'] ?>" class="text-gray-400 hover:text-red-500 transition-colors duration-200 ml-2">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                <div class="flex justify-between items-center text-sm text-gray-500 mt-2 pt-2 border-t border-gray-100">
                                    <span class="flex items-center"><i class="far fa-clock mr-1"></i><?= date('M d, Y', strtotime($row['timestamp'])) ?></span>
                                    <span class="flex items-center"><i class="fas fa-heart text-red-500 mr-1"></i><?= $row['like_count'] ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-16 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="fas fa-video-slash"></i>
                        </div>
                        <p class="text-gray-600 text-lg mb-2">No videos found</p>
                        <p class="text-gray-500 text-sm mb-6">Your uploaded videos will appear here</p>
                        <a href="../new_upload.html" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>Upload a video
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Password Section -->
        <section id="change-password" class="hidden" data-aos="fade-up">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-header text-white p-6">
                    <h2 class="text-2xl font-bold"><i class="fas fa-lock mr-3"></i>Change Password</h2>
                    <p class="text-blue-100 text-sm mt-1">Update your account password</p>
                </div>
                <form method="POST" class="p-8 space-y-6">
                    <div class="space-y-5 max-w-md mx-auto">
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Current Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" name="current-password" required
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">New Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" name="new-password" required
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2 font-medium">Confirm Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-check-circle"></i>
                                </span>
                                <input type="password" name="confirm-password" required
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-8 rounded-lg font-medium 
                                       transition-all duration-300 transform hover:scale-[1.02] shadow-md">
                            <i class="fas fa-save mr-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-5 right-5 z-50 hidden">
        <div class="px-6 py-4 rounded-lg shadow-lg text-white flex items-center space-x-4">
            <i id="toast-icon" class="text-xl"></i>
            <span id="toast-message"></span>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });
        
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const msg = document.getElementById('toast-message');
            
            const types = {
                success: { class: 'bg-green-500', icon: 'fa-check-circle' },
                error: { class: 'bg-red-500', icon: 'fa-times-circle' },
                info: { class: 'bg-blue-500', icon: 'fa-info-circle' }
            };
            
            toast.className = `fixed top-5 right-5 z-50 ${types[type].class} px-6 py-4 rounded-lg shadow-lg text-white flex items-center space-x-4 animate-slideIn`;
            icon.className = `fas ${types[type].icon} text-xl`;
            msg.textContent = message;
            
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }
        
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
        
        function showSection(sectionId) {
            // Update tabs
            document.querySelectorAll('.tab-button').forEach(tab => {
                if (tab.dataset.section === sectionId) {
                    tab.classList.add('tab-active');
                } else {
                    tab.classList.remove('tab-active');
                }
            });
            
            // Show selected section, hide others
            document.querySelectorAll('main > section').forEach(s => s.classList.add('hidden'));
            document.getElementById(sectionId).classList.remove('hidden');
            
            // Close mobile menu if open
            document.getElementById('mobile-menu').classList.add('hidden');
            
            // Smooth scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    
    // Add after AOS initialization
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    const username = "<?= $username ?>";

    window.addEventListener('scroll', () => {
        const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
        
        if (scrollHeight - scrollTop <= clientHeight + 100 && !isLoading && hasMore) {
            loadMoreVideos();
        }
    });

    async function loadMoreVideos() {
        isLoading = true;
        document.getElementById('loading').classList.remove('hidden');
        currentPage++;

        try {
            const response = await fetch(`get_videos.php?page=${currentPage}&username=${username}`);
            const html = await response.text();
            
            if (html.includes('No more videos')) {
                hasMore = false;
            } else {
                document.querySelector('#videos .grid').insertAdjacentHTML('beforeend', html);
            }
        } catch (error) {
            console.error('Error loading videos:', error);
        }

        isLoading = false;
        document.getElementById('loading').classList.add('hidden');
    }

    
    // Delete Modal Handling
    function toggleDeleteModal(recordId) {
        document.getElementById('deleteRecordId').value = recordId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // AJAX Delete Handling
document.getElementById('deleteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const recordId = document.getElementById('deleteRecordId').value;
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        // Close delete modal
        closeDeleteModal();
        
        // Show message
        showToast(data.message, data.status);
        
        // Remove deleted item from DOM if successful
        if (data.status === 'success' && data.deleted_id) {
            const deletedElement = document.querySelector(`[data-video-id="${data.deleted_id}"]`);
            if (deletedElement) {
                deletedElement.remove();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Delete failed. Please try again.', 'error');
    });
});


// Show messages on page load
document.addEventListener('DOMContentLoaded', function() {
    // Handle section parameter
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    if (section) showSection(section);

    // Handle messages
    const messageData = document.getElementById('message-data');
    if (messageData.dataset.message) {
        showToast(messageData.dataset.message, messageData.dataset.type);
        messageData.dataset.message = ''; // Clear the data
    }
});

// Add to close message function
function closeMessage() {
    document.getElementById('messageBox').classList.add('translate-x-[150%]');
}

    // Update the delete buttons to use event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-delete-button]')) {
            const recordId = e.target.closest('[data-delete-button]').dataset.id;
            toggleDeleteModal(recordId);
        }
    });

    // Add this code to show the correct section on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    
    if (section) {
        showSection(section);
        // Clear the parameter from URL
        history.replaceState(null, null, window.location.pathname);
    }
});

</script>

</body>
</html>