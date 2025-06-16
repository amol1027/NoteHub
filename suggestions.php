<?php
header("Content-Type: application/json");
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

$query = $_GET['query'] ?? '';
$query = $conn->real_escape_string($query);

// Improved query to search in multiple fields and ensure only approved content is shown
$sql = "SELECT DISTINCT description FROM homedata 
        WHERE (description LIKE '%$query%' OR username LIKE '%$query%') 
        AND status = 'approved'
        LIMIT 10";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row['description'];
}

echo json_encode($suggestions);

?>
