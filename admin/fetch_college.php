<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "justclick");

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get parameters safely
$month = isset($_GET['month']) ? (int) $_GET['month'] : 'all';
$year = isset($_GET['year']) ? (int) $_GET['year'] : 'all';

// Base query
$query = "SELECT DAY(registered_date) AS day, COUNT(*) AS count FROM college WHERE status = 'approved'";

// Apply filters
if ($month !== 'all') $query .= " AND MONTH(registered_date) = $month";
if ($year !== 'all') $query .= " AND YEAR(registered_date) = $year";

$query .= " GROUP BY DAY(registered_date) ORDER BY day ASC";

$result = $conn->query($query);

// Check for query errors
if (!$result) {
    die(json_encode(['error' => 'SQL Error: ' . $conn->error]));
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'day_of_month' => $row['day'],
        'total_colleges' => $row['count']
    ];
}

// Output JSON
echo json_encode($data);

$conn->close();
?>
