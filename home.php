<?php
session_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const suggestionBox = document.createElement('div');
    
    // Configure suggestion box styles
    Object.assign(suggestionBox.style, {
        position: 'absolute',
        background: 'white',
        border: '1px solid #ddd',
        zIndex: '1000',
        marginTop: '60px',
        width: `${searchInput.offsetWidth}px`
    });
    searchInput.parentNode.appendChild(suggestionBox);

    // Unified input handler with debouncing
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchTimeout);
        
        suggestionBox.innerHTML = query.length < 2 
            ? '' 
            : '<div class="Absolute p-4 text-gray-500 z-50">Searching...</div>';

        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                fetch(`suggestions.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(suggestions => {
                        suggestionBox.innerHTML = suggestions
                            .map(suggestion => `
                                <div class="Absolute  p-2 cursor-pointer z-50 hover:bg-gray-100 border-b"
                                     onclick="this.parentElement.innerHTML='';
                                              searchInput.value='${suggestion.replace(/'/g, "\\'")}'">
                                    ${suggestion}
                                </div>
                            `).join('');
                    })
                    .catch(() => suggestionBox.innerHTML = '');
            }, 300);
        }
    });

    // Click outside handler
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionBox.contains(e.target)) {
            suggestionBox.innerHTML = '';
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        suggestionBox.style.width = `${searchInput.offsetWidth}px`;
    });
});
</script>

    <?php
   

    if (!isset($_SESSION['user'])) {
        echo "<script>alert('User not logged in.')</script>";
        echo "<script>window.location.href='./student/login.php';</script>";
        exit();
    }
    $user = $_SESSION['user'];
    $username = isset($user['username']) ? htmlspecialchars($user['username']) : 'Unknown';

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");

    $hostName = "localhost";
    $userName = "root";
    $password = "";
    $databaseName = "justclick";

    $conn = new mysqli($hostName, $userName, $password, $databaseName);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
            }

 


function getLikeCount($conn, $video_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM video_likes WHERE video_id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['count'];
}

$colleges = [];
$collegeQuery = $conn->query("SELECT col_name FROM college WHERE status = 'approved'");
if ($collegeQuery) {
    $colleges = $collegeQuery->fetch_all(MYSQLI_ASSOC);
}

      // Fetch user's college from the users table (adjust column/table names as needed)
$userCollege = '';
$username = $conn->real_escape_string($username);
$userQuery = $conn->query("SELECT firstName, collegeName FROM users WHERE username = '$username'");
if ($userQuery && $userQuery->num_rows > 0) {
    $userData = $userQuery->fetch_assoc();
    $userCollege = $userData['collegeName'] ?? '';
    $userFirstName = $userData['firstName'] ?? '';
}

// Check if user's college exists in college table
$collegeExists = false;
if (!empty($userCollege)) {
    $escapedCollege = $conn->real_escape_string($userCollege);
    $collegeCheck = $conn->query("SELECT COUNT(*) AS count FROM college WHERE col_name = '$escapedCollege'");
    if ($collegeCheck && $collegeCheck->fetch_assoc()['count'] > 0) {
        $collegeExists = true;
    }
}

    $tableName = "homedata";

    function fetch_data($conn, $table, $columns, $search = '', $branch = '', $college = '', $offset = 0, $limit = 10, $sort_by = '') {
        $conditions = ["status = 'approved'"];

        if ($college) {
            $college = $conn->real_escape_string($college);
            $conditions[] = "col_name = '$college'";
        }
    
        if ($search) {
            $search = $conn->real_escape_string($search);
            // Improved search to look for partial matches in more fields
            $conditions[] = "(description LIKE '%$search%' OR links LIKE '%$search%' OR username LIKE '%$search%')";
        }
        if ($branch) {
            $branch = $conn->real_escape_string($branch);
            $conditions[] = "branch = '$branch'";
        }
        
        $where = 'WHERE ' . implode(' AND ', $conditions);
        
        // Define order based on sort_by parameter
        $order = "ORDER BY ";
        switch ($sort_by) {
            case 'most_likes':
                $order .= "like_count DESC";
                break;
            case 'most_recent':
                $order .= "id DESC";
                break;
            case 'oldest':
                $order .= "id ASC";
                break;
            default:
                $order .= "id DESC"; // Default sort by newest
        }
        
        $query = "SELECT " . implode(", ", $columns) . " FROM $table $where $order LIMIT $limit OFFSET $offset";

        $result = $conn->query($query);

        if (!$result) {
            die("Query failed: " . $conn->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

   // Calculate total records - FIXED
$conditions = ["status = 'approved'"];
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    // Make sure this matches the search condition in fetch_data
    $conditions[] = "(description LIKE '%$search%' OR links LIKE '%$search%' OR username LIKE '%$search%')";
}
if (!empty($branch)) {
    $branch = $conn->real_escape_string($branch);
    $conditions[] = "branch = '$branch'";
}
if (!empty($college)) { // ADD THIS
    $college = $conn->real_escape_string($college);
    $conditions[] = "col_name = '$college'";
}

$totalQuery = "SELECT COUNT(*) as total FROM $tableName WHERE " . implode(' AND ', $conditions);
$totalResult = $conn->query($totalQuery);
if (!$totalResult) {
    die("Query failed: " . $conn->error);
}
$totalRows = $totalResult->fetch_assoc()['total'];
$limit = 9;
$totalPages = ceil($totalRows / $limit);

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page);
    $offset = ($page - 1) * $limit;

    $columns = ['id','links','like_count', 'description', 'username'];
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $branch = isset($_GET['branch']) ? $_GET['branch'] : '';
    $college = isset($_GET['college']) ? $_GET['college'] : '';
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';
    
    $fetchData = fetch_data($conn, $tableName, $columns, $search, $branch, $college, $offset, $limit, $sort_by);
    
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com/"></script>
        <link rel="stylesheet" href="./css/css.css">
        <link rel="stylesheet" href="https://cdn.tailwindcss.com/3/css/tailwind.min.css">
        <title>Home</title>
    <link rel="shortcut icon" href="https://img.icons8.com/?size=100&id=79257&format=png&color=000000" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        
    <style>
    .fa-heart {
        transition: all 0.2s ease-in-out;
    }
    .fa-heart:hover {
        transform: scale(1.2);
    }.aspect-w-16 {
    position: relative;
}

.aspect-w-16::before {
    display: block;
    content: "";
    width: 100%;
    padding-bottom: 56.25%;
}

.aspect-w-16 iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.transition-colors {
    transition: color 0.2s ease-in-out;
}
.video-card {
        @apply bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300;
    }
    .aspect-16x9 {
        @apply relative pb-[56.25%] bg-gray-100;
    }
    .video-iframe {
        @apply absolute top-0 left-0 w-full h-full;
    }
    .like-button {
        @apply flex items-center gap-2 transition-colors;
    }

/* Progress Indicator Styles */
#loading {
    transition: opacity 0.3s ease-in-out;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* NoteMate AI Styles */
.notemate-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.notemate-panel {
    width: 400px;
    height: 600px;
    background-color: #1f2937;
    border-radius: 12px;
    margin-bottom: 16px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
}

.notemate-panel.active {
    display: flex;
}

.notemate-toggle {
    background-color: #3b82f6;
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.notemate-toggle:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.notemate-header {
    background-color: #111827;
    padding: 16px;
    border-bottom: 1px solid #374151;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notemate-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
}

.notemate-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background-color: #1f2937;
}

.notemate-body::-webkit-scrollbar {
    width: 5px;
}

.notemate-body::-webkit-scrollbar-track {
    background: #1f2937;
}

.notemate-body::-webkit-scrollbar-thumb {
    background-color: #4b5563;
    border-radius: 20px;
}

.notemate-input-container {
    padding: 16px;
    background-color: #111827;
    border-top: 1px solid #374151;
    display: flex;
    gap: 8px;
}

.notemate-input {
    flex: 1;
    padding: 12px;
    background-color: #1f2937;
    border: 1px solid #374151;
    border-radius: 8px;
    color: white;
    outline: none;
}

.notemate-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

.notemate-send {
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0 16px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notemate-send:hover {
    background-color: #2563eb;
}

.message {
    padding: 12px;
    border-radius: 12px;
    max-width: 80%;
    color: white;
}

.message.user {
    background-color: #3b82f6;
    margin-left: auto;
    border-radius: 12px 12px 0 12px;
}

.message.ai {
    background-color: #374151;
    margin-right: auto;
    border-radius: 12px 12px 12px 0;
}

.quick-questions {
    padding: 16px;
    border-top: 1px solid #374151;
    background-color: #111827;
}

.quick-question-btn {
    width: 100%;
    text-align: left;
    padding: 8px 12px;
    background-color: #1f2937;
    color: #d1d5db;
    border: none;
    border-radius: 6px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.quick-question-btn:hover {
    background-color: #374151;
}

@keyframes pulse {
    0%, 100% { opacity: 0.4; transform: scale(0.8); }
    50% { opacity: 1; transform: scale(1); }
}

.typing-indicator {
    display: flex;
    gap: 4px;
}

.typing-dot {
    width: 8px;
    height: 8px;
    background-color: #9ca3af;
    border-radius: 50%;
}

.message-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    overflow-y: auto;
    padding: 16px;
    height: calc(100% - 160px);
}

/* Custom CSS for dropdown */
.dropdown {
    position: relative;
    display: inline-block;
}
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 145px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
}
.dropdown-content a {
    color: black;
    padding: 12px 12px;
    text-decoration: none;
    display: block;
}
.dropdown-content a:hover {
    background-color: #f1f1f1;
}
.dropdown:hover .dropdown-content {
    display: block;
}

.message {
    padding: 12px 16px;
    border-radius: 12px;
    max-width: 80%;
    word-wrap: break-word;
}
</style>
    </head>

    <body class="h-screen bg-slate-200">
        <header>
            <nav>
                <div class="logo "><a href="home.php">NoteHub</a></div>
                <ul class="nav-links flex items-center">
                    <li><a href="#">Home</a></li>
                    
                            <li><a href="./student/stud_prof.php">My Profile</a></li>
                            <?php if ($collegeExists): ?>
                            <li><a href="./documents.php">My College</a></li>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="mr-">
                        <a href="./student/log_out.php" 
                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg smooth-transition flex items-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </header>

        <form method="GET" action="" class="mt-[7%]">
            <div class="flex justify-center items-center w-full px-4">
                <div class="flex flex-col md:flex-row p-6 space-y-4 md:space-y-0 md:space-x-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 w-full max-w-5xl">
                    <div class="flex bg-gray-100 p-4 w-full md:w-72 space-x-4 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input class="bg-gray-100 outline-none w-full" type="text" name="search" 
                            placeholder="Article name or keyword..." 
                            value="<?php echo htmlspecialchars($search); ?>" />
                    </div>
                    <div class="flex space-x-4 py-3 px-4 rounded-lg text-gray-500 font-semibold">
                        <select name="branch" class="w-36 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Branch</option>
                            <option value="bba" <?= ($branch === 'bba') ? 'selected' : '' ?>>BBA</option>
                            <option value="bca" <?= ($branch === 'bca') ? 'selected' : '' ?>>BCA</option>
                            <option value="mba" <?= ($branch === 'mba') ? 'selected' : '' ?>>MBA</option>
                        </select>

                        <select name="sort_by" class="w-36 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Sort by</option>
                            <option value="most_likes" <?= ($sort_by === 'most_likes') ? 'selected' : '' ?>>Most Likes</option>
                            <option value="most_recent" <?= ($sort_by === 'most_recent') ? 'selected' : '' ?>>Most Recent</option>
                            <option value="oldest" <?= ($sort_by === 'oldest') ? 'selected' : '' ?>>Oldest</option>
                        </select>
                    </div>
                    <div class="flex justify-center space-x-4">
                        <button type="submit" class="bg-indigo-600 py-3 px-5 text-white font-semibold rounded-lg hover:shadow-lg transition duration-300">Search</button>
                       
                    </div>
                    <label for="uploadFile1" class="flex bg-gray-800 hover:bg-gray-700 text-white text-center px-5 py-3 outline-none rounded w-max cursor-pointer mx-auto font-[sans-serif]">
                        <a href="new_upload.html" class="text-center mt-3">Upload</a>
                    </label>
                    <?php if (!empty($search) || !empty($branch) || !empty($college) || !empty($sort_by)): ?>
                            
                            <button type="button" class="bg-rose-600 py-3 px-5 text-white font-semibold rounded-lg hover:shadow-lg transition duration-300"><a href="home.php">Reset</a></button>
                        
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <?php if (!empty($search) || !empty($branch) || !empty($college)):  ?>
<div class="text-center mt-4 mb-6">
    <span class="bg-gray-100 px-4 py-2 rounded-lg text-lg">
        Showing results for
        <?php if (!empty($search)): ?>
            '<span class="font-semibold"><?= htmlspecialchars($search) ?></span>'
        <?php endif; ?>
        <?php if (!empty($branch)): ?>
            in branch '<span class="font-semibold"><?= htmlspecialchars(strtoupper($branch)) ?></span>'
        <?php endif; ?>
        <?php if (!empty($college)): ?>
            from college '<span class="font-semibold"><?= htmlspecialchars($college) ?></span>'
        <?php endif; ?>
    </span>
</div>
<?php endif; ?>

<div class="video-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-10 px-4">
<?php $userId = $_SESSION['user']['id'] ?? 0;?>
    <?php foreach ($fetchData as $data): 
    
        $videoId = $data['id'];
        $likeCount = $data['like_count'];
        $hasLiked = $conn->query("SELECT 1 FROM video_likes WHERE user_id=$userId AND video_id=$videoId")->num_rows > 0;
        
        // Extract YouTube embed URL 
        preg_match('/src="([^"]+)"/', $data['links'], $matches);
        $embedUrl = $matches[1] ?? '';
    
        // Extract YouTube ID from embed URL 
        $ytVideoId = '';
        if (!empty($embedUrl)) {
            // Handle both embed URLs and watch URLs
            if (strpos($embedUrl, 'youtube.com/embed/') !== false) {
                $path = parse_url($embedUrl, PHP_URL_PATH);
                $ytVideoId = basename($path);
            } elseif (strpos($embedUrl, 'youtube.com/watch') !== false) {
                parse_str(parse_url($embedUrl, PHP_URL_QUERY), $params);
                $ytVideoId = $params['v'] ?? '';
            }
        }
    ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 mt-8">
            <!-- Video Embed -->
            <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                <?php if ($embedUrl): ?>
                    <iframe 
                        class="w-full h-64"
                        src="<?= htmlspecialchars($embedUrl) ?>"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <div class="w-full h-64 flex items-center justify-center text-gray-500">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    
                <?php endif; ?>
            </div>

            <!-- Card Content -->
            <div class="p-6">
                <h3 class="text-xl font-semibold mb-2 text-gray-800">
                    <!-- Fixed display of video ID -->
                    <?= htmlspecialchars($data['description']) ?>
                </h3>
                
                <!-- Like Section -->
<div class="flex items-center justify-between mt-4">
    <div class="flex items-center gap-2">
        <button 
            onclick="handleLike(<?= $videoId ?>)" 
            class="flex items-center gap-2 group transition-colors like-button"
            data-liked="<?= $hasLiked ? 'true' : 'false' ?>"
            data-video-id="<?= $videoId ?>"
            aria-label="<?= $hasLiked ? 'Unlike' : 'Like' ?> this video"
        >
            <svg class="w-6 h-6 <?= $hasLiked ? 'text-red-500 fill-current' : 'text-gray-400' ?> 
                  group-hover:text-red-600 transition-colors heart-icon"
                viewBox="0 0 24 24" stroke-width="<?= $hasLiked ? '0' : '2' ?>">
                <path stroke-linecap="round" stroke-linejoin="round" 
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span class="<?= $hasLiked ? 'text-red-600' : 'text-gray-600' ?> 
                  font-medium group-hover:text-red-700 transition-colors like-count">
                <?= number_format($likeCount) ?>
            </span>
        </button>
    </div>

                    
                    <!-- Upload Info -->
                    <div class="text-sm text-gray-500">
                        <span>Uploaded by </span>
                        <span class="font-medium"><?= htmlspecialchars($data['username']) ?></span>
                    </div>
                </div>
<div class="flex items-center gap-2 mt-2">
    <a href="download.php?id=<?= $ytVideoId ?>" 
       class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition-colors">
       Download
    </a>
</div>
            </div>
        </div>

    <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="flex justify-center mt-10 p-4">
    <div class="pagination flex justify-center space-x-4 mt-6">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&branch=<?= urlencode($branch) ?>&college=<?= urlencode($college) ?>" 
            class="bg-gray-300 py-2 px-4 rounded hover:bg-gray-400 transition">
                Previous
            </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&branch=<?= urlencode($branch) ?>&college=<?= urlencode($college) ?>" 
            class="py-2 px-4 rounded transition <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-300 hover:bg-gray-400' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&branch=<?= urlencode($branch) ?>&college=<?= urlencode($college) ?>" 
            class="bg-gray-300 py-2 px-4 rounded hover:bg-gray-400 transition">
                Next
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Progress Indicator -->
<div id="loading" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
        <p class="text-lg">Preparing your download...</p>
        <div class="mt-4 w-12 h-12 center border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>
</div>

<script>
async function handleLike(videoId) {

 

    const button = document.querySelector(`[data-video-id="${videoId}"]`);
    const icon = button.querySelector('.heart-icon');
    const countElement = button.querySelector('.like-count');
    
    try {
        const response = await fetch('like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ video_id: videoId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update like count
            countElement.textContent = data.newCount.toLocaleString();
            

            
            // Toggle visual states
            if (data.liked) {
                icon.classList.add('text-red-500', 'fill-current');
                icon.classList.remove('text-gray-400');
                countElement.classList.add('text-red-600');
                countElement.classList.remove('text-gray-600');
                icon.setAttribute('stroke-width', '0');
            } else {
                icon.classList.remove('text-red-500', 'fill-current');
                icon.classList.add('text-gray-400');
                countElement.classList.remove('text-red-600');
                countElement.classList.add('text-gray-600');
                icon.setAttribute('stroke-width', '2');
            }
        }
    } catch (error) {
        // Add this inside the catch block
countElement.textContent = 'Error';
icon.classList.add('text-yellow-500'); // Visual error indication
        console.error('Error:', error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const downloadLinks = document.querySelectorAll('a[href^="download.php"]');
    const loadingDiv = document.getElementById('loading');

    downloadLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent the default link behavior
            loadingDiv.classList.remove('hidden'); // Show the progress indicator

            // Redirect to the download URL after a short delay
            setTimeout(() => {
                window.location.href = this.href;
            }, 500); // Adjust the delay as needed
        });
    });

    // Hide the progress indicator when the page loads (optional)
    window.addEventListener('load', () => {
        loadingDiv.classList.add('hidden');
    });
});
</script>
    </body>
    <!-- NoteMate AI Chat Interface -->
    <div class="notemate-container">
        <div class="notemate-panel" id="notemate-panel">
            <div class="notemate-header">
                <div class="notemate-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <h1 class="text-xl font-semibold">NoteMate AI</h1>
                        <p class="text-xs text-gray-400">Connected to NoteHub</p>
                    </div>
                </div>
                <button class="text-gray-400 hover:text-white" id="close-notemate">&times;</button>
            </div>
            <div class="notemate-body" id="notemate-body">
                <div class="message ai">Hello <?php echo $userFirstName; ?>!<br> I'm NoteMate AI. How can I help you today?</div>
            </div>
            <div class="notemate-input-container">
                <input type="text" class="notemate-input" id="notemate-input" placeholder="Ask me anything...">
                <button class="notemate-send" id="notemate-send">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </div>
            <div class="quick-questions">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Quick Questions</h3>
                <button onclick="sendPredefinedMessage('What is NoteHub?')" class="quick-question-btn">
                    What is NoteHub?
                </button>
                <button onclick="sendPredefinedMessage('What can you do?')" class="quick-question-btn">
                    What can you do?
                </button>
                <button onclick="sendPredefinedMessage('Why use NoteHub?')" class="quick-question-btn">
                    Why use NoteHub?
                </button>
            </div>
        </div>
        <div class="notemate-toggle" id="notemate-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </div>
    </div>

    <script>
        // NoteMate AI Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const noteMateToggle = document.getElementById('notemate-toggle');
            const noteMatePanel = document.getElementById('notemate-panel');
            const noteMateClose = document.getElementById('close-notemate');
            const noteMateInput = document.getElementById('notemate-input');
            const noteMateSend = document.getElementById('notemate-send');
            const noteMateBody = document.getElementById('notemate-body');
            
            // Toggle chat panel
            noteMateToggle.addEventListener('click', function() {
                noteMatePanel.classList.toggle('active');
            });
            
            // Close chat panel
            noteMateClose.addEventListener('click', function() {
                noteMatePanel.classList.remove('active');
            });

            function createTypingIndicator() {
                const indicator = document.createElement('div');
                indicator.className = 'message ai typing-indicator';
                
                for (let i = 0; i < 3; i++) {
                    const dot = document.createElement('div');
                    dot.className = 'typing-dot';
                    dot.style.animation = `pulse 1s infinite ${i * 0.15}s`;
                    indicator.appendChild(dot);
                }
                
                return indicator;
            }
            
            // Function to send predefined messages
            window.sendPredefinedMessage = function(question) {
                noteMateInput.value = question;
                sendMessage();
            }
            
            // Handle sending messages
            function sendMessage() {
                const message = noteMateInput.value.trim();
                if (message) {
                    // Add user message
                    noteMateBody.innerHTML += `<div class="message user">${message}</div>`;
                    
                    // Clear input
                    noteMateInput.value = '';
                    
                    // Add typing indicator
                    const typingIndicator = createTypingIndicator();
                    noteMateBody.appendChild(typingIndicator);
                    
                    // Scroll to bottom
                    noteMateBody.scrollTop = noteMateBody.scrollHeight;
                    
                    // Fetch response from API
                    fetch("demo.php", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ message: message })
                    }).then(response => response.json())
                      .then(data => {
                          noteMateBody.removeChild(typingIndicator);
                          noteMateBody.innerHTML += `<div class="message ai">${data.error ? data.error : data.response}</div>`;
                          noteMateBody.scrollTop = noteMateBody.scrollHeight;
                      }).catch(error => {
                          noteMateBody.removeChild(typingIndicator);
                          noteMateBody.innerHTML += `<div class="message ai">Sorry, I encountered an error. Please try again.</div>`;
                          noteMateBody.scrollTop = noteMateBody.scrollHeight;
                      });
                }
            }
            
            // Send message on button click
            noteMateSend.addEventListener('click', sendMessage);
            
            // Send message on Enter key
            noteMateInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
    </script>
    </html>