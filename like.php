<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "justclick";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || !isset($data['video_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit();
}

$video_id = (int)($data['video_id'] ?? 0);
$user_id = $_SESSION['user']['id'];

if ($video_id <= 0 || !isset($_SESSION['user']['id']) || !is_numeric($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid video ID or user session']);
    exit();
}

try {
    if (!$conn->begin_transaction()) {
        echo json_encode(['success' => false, 'error' => 'Transaction initiation failed']);
        exit();
    }

    // Check existing like
    $stmt = $conn->prepare("SELECT 1 FROM video_likes WHERE user_id = ? AND video_id = ?");
    $stmt->bind_param("ii", $user_id, $video_id);
    $stmt->execute();
    $hasLiked = $stmt->get_result()->num_rows > 0;

    if ($hasLiked) {
        // Unlike
        $stmt = $conn->prepare("DELETE FROM video_likes WHERE user_id = ? AND video_id = ?");
        $stmt->bind_param("ii", $user_id, $video_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE homedata SET like_count = like_count - 1 WHERE id = ?");
        $stmt->bind_param("i", $video_id);
        $stmt->execute();
    } else {
        // Like
        $stmt = $conn->prepare("INSERT INTO video_likes (user_id, video_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $video_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE homedata SET like_count = like_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $video_id);
        $stmt->execute();
    }

    if (!$conn->commit()) {
        echo json_encode(['success' => false, 'error' => 'Transaction commit failed']);
        exit();
    }

    // Get updated count
    $stmt = $conn->prepare("SELECT like_count FROM homedata WHERE id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $likeCount = $result->fetch_assoc()['like_count'];

    echo json_encode([
        'success' => true,
        'liked' => !$hasLiked,
        'newCount' => $likeCount
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}