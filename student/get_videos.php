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

// Get parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$username = isset($_GET['username']) ? $_GET['username'] : '';
$limit = 6;
$offset = ($page - 1) * $limit;

// Database query
$stmt = $conn->prepare("SELECT id, username, description, links, timestamp, like_count 
                       FROM homedata 
                       WHERE username = ? 
                       ORDER BY timestamp DESC 
                       LIMIT ? OFFSET ?");
$stmt->bind_param("sii", $username, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Process results
function processLink($link) {
    if (preg_match("/youtu(?:\.be|be\.com)\/(?:watch\?v=|embed\/)?([a-zA-Z0-9\-_]+)(?:[&?\/]t=(\d+))?/i", $link, $matches)) {
        return "<iframe class='w-full h-full border border-gray-300 rounded-md' 
                src='https://www.youtube.com/embed/$matches[1]?start=($matches[2] ?? 0)' 
                allowfullscreen></iframe>";
    }
    return "<a href='$link' target='_blank' class='text-blue-500 underline'>$link</a>";
}

$output = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $output .= '<div class="bg-gray-100 p-4 rounded-lg hover-scale smooth-transition flex flex-col">
                      <!-- Video HTML same as in main file -->
                   </div>';
    }
} else {
    $output .= '<p class="text-center col-span-full text-gray-600 py-4">No more videos to load</p>';
}

echo $output;
?>