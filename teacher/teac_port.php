<?php
session_start();

// Check if the user is logged in (i.e., session variables are set)
if (!isset($_SESSION['college_id']) || !isset($_SESSION['t_id'])) {
    // If not logged in, redirect to login page
    header("Location: teach_log.html");
    exit();
}

// Initialize message from session
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after retrieval
}

// Handle Logout
if (isset($_POST['logout'])) {
    // Destroy all session data
    session_unset();
    session_destroy();

    // Redirect to the login page after logout
    header("Location: teach_log.html");
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "justclick"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the college_id from the session
$college_id = $_SESSION['college_id'];
$teacher_id = $_SESSION['t_id'];
$college_name = $_SESSION['college_name'];

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmPassword']) && isset($_POST['newPassword']) && isset($_POST['currentPassword'])) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Fetch the teacher's password from the database
    $sql = "SELECT t_password FROM $college_name WHERE t_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();
    $storedPassword = $teacher['t_password'];
    
    // Verify current password
    if ($currentPassword !== $storedPassword) {
        $message = "Current password is incorrect.";
    } else {
        // Check if new password and confirm password match
        if ($newPassword === $confirmPassword) {
            // Update the password in the database
            $update_sql = "UPDATE $college_name SET t_password = ? WHERE t_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $newPassword, $teacher_id);
            $update_stmt->execute();
            $update_stmt->close();
            $message = "Password changed successfully.";
        } else {
            $message = "New password and confirm password do not match.";
        }
    }
    
    // Store message in session and redirect to avoid resubmission
    $_SESSION['message'] = $message;
    header("Location: teac_port.php");
    exit();
}

// Display message if exists
if ($message) {
    echo "<script>document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('toast');
        toast.textContent = '" . addslashes($message) . "';
        toast.classList.add('show');
        setTimeout(function() {
            toast.classList.remove('show');
        }, 3000);
    });</script>";
    $message = ""; // Clear message after displaying
}




// Construct the table name based on the college_id
$college_table = $college_name;

// Query the respective college table for teacher details
$sql = "SELECT t_id, t_fname, t_branch, t_email FROM $college_table WHERE t_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the teacher exists in the respective college table
if ($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
    $t_name = $teacher['t_fname'];
    $t_branch = $teacher['t_branch'];
    $t_email = $teacher['t_email'];
} else {
    echo "Teacher not found in the college database.";
    exit();
}

// Query to count the total uploads by the teacher
$upload_sql = "SELECT COUNT(*) AS upload_count FROM documents WHERE t_id = ?";
$upload_stmt = $conn->prepare($upload_sql);
$upload_stmt->bind_param("s", $teacher_id);
$upload_stmt->execute();
$upload_result = $upload_stmt->get_result();
$upload_count = $upload_result->fetch_assoc()['upload_count'];

// Fetch the last login time from the database
$last_login_sql = "SELECT last_login FROM teachers WHERE t_id = ?";
$last_login_stmt = $conn->prepare($last_login_sql);
$last_login_stmt->bind_param("s", $teacher_id);
$last_login_stmt->execute();
$last_login_result = $last_login_stmt->get_result();
$last_login_time = $last_login_result->fetch_assoc()['last_login'];

$engagement_sql = "
    SELECT COUNT(DISTINCT id) * 100 AS engagement_rate
    FROM documents
    WHERE id IN (SELECT id FROM documents WHERE t_id = ?)
";
$engagement_stmt = $conn->prepare($engagement_sql);
$engagement_stmt->bind_param("s", $teacher_id);
$engagement_stmt->execute();
$engagement_result = $engagement_stmt->get_result();
$engagement_rate = $engagement_result->fetch_assoc()['engagement_rate'];

// Close the statement
$engagement_stmt->close();

// Close the statement
$last_login_stmt->close();

// Close the database connection
$stmt->close();
$upload_stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/v2.0.18/24/outline/index.js"></script>
    <link rel="stylesheet" href="../css/css.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
    body { font-family: 'Inter', sans-serif; }
    /* Toast Notification Styles */
    .toast {
        visibility: hidden;
        max-width: 300px;
        margin: auto;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 5px;
        padding: 16px;
        position: fixed;
        z-index: 1;
        left: 50%;
        bottom: 30px;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.5s, visibility 0.5s;
    }

    .toast.show {
        visibility: visible;
        opacity: 1;
    }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Bar -->
    <header>
        <nav>
            <div class="logo"><a href="../index.php">NoteHub</a></div>
            <ul class="nav-links">
                <li><a href="teac_port.php">My Profile</a></li>
                <li><a href="../cupload.php">Upload Content</a></li>
                <li><a href="../tech_doc.php">My Uploads</a></li>
                <li><a href="../student/log_out.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 mt-20">
            <div>
                <h1 class="text-3xl font-bold text-black">
                    Teacher Profile
                </h1>
                <p class="text-gray-600 mt-1">Welcome to your professional dashboard</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="space-y-8">
            <!-- Profile Details -->
            <div class="space-y-6">
                <!-- College Card -->
                <div class="flex items-center gap-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-blue-600 font-medium">College Name</p>
                        <p class="text-lg text-gray-800 font-semibold"><?php echo $_SESSION['college_name']; ?></p>
                    </div>
                </div>

                <!-- Profile Grid -->
                <div class="grid md:grid-cols-2 gap-4">
                    <!-- Personal Info Card -->
                    <div class="p-6 bg-white rounded-lg border border-gray-100 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Personal Information
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Teacher ID</p>
                                <p class="font-medium text-gray-800"><?php echo $teacher_id; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Full Name</p>
                                <p class="font-medium text-gray-800"><?php echo $t_name; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email Address</p>
                                <p class="font-medium text-gray-800 break-all"><?php echo $t_email; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Department</p>
                                <p class="font-medium text-gray-800"><?php echo $t_branch; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Card -->
                    <div class="p-6 bg-white rounded-lg border border-gray-100 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            Recent Activity
                        </h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-2xl font-bold text-blue-600"><?php echo $upload_count ?></p>
                                <p class="text-sm text-gray-600">Uploads</p>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-2xl font-bold text-green-600"><?php echo $engagement_rate/2; ?>%</p>
                                <p class="text-sm text-gray-600">Engagement</p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Last login:</span>
                                <span class="font-medium text-gray-800"><?php
                                // Format the last login time
                                $formatted_last_login = date("F j, Y, g:i a", strtotime($last_login_time));
                                echo $formatted_last_login;
                                ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Change Password</h2>
                <form method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-1 gap-6">
                        <div>
                            <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                            <input type="password" id="currentPassword" name="currentPassword" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                            <input type="password" id="newPassword" name="newPassword" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        Message goes here
    </div>

</body>
</html>
