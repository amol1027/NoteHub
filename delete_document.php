<?php
session_start();
if (!isset($_SESSION['t_id']) || !isset($_SESSION['college_name'])) {
    header("Location: ./teacher/tech_log.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "justclick";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doc_id = $_POST['doc_id'] ?? null;
$collegeName = $_SESSION['college_name'];

if (!$doc_id) {
    die("Invalid request.");
}

// Verify document ownership and get file path
$sql = "SELECT content FROM documents WHERE id = ? AND col_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $doc_id, $collegeName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die("Document not found or access denied.");
}

$row = $result->fetch_assoc();
$filePath = $row['content'];

// Delete from database
$deleteSql = "DELETE FROM documents WHERE id = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("i", $doc_id);
$deleteStmt->execute();

if ($deleteStmt->affected_rows > 0) {
    // Delete the file
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    header("Location: ./tech_doc.php?status=deleted");
} else {
    header("Location: ./tech_doc.php?status=error");
}

$deleteStmt->close();
$stmt->close();
$conn->close();
exit();
?>