<?php
// Database connection details
$host = 'localhost';
$dbname = 'justclick';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get filter parameters
    $month = isset($_GET['month']) && $_GET['month'] !== 'all' ? intval($_GET['month']) : null;
    $year = isset($_GET['year']) && $_GET['year'] !== 'all' ? intval($_GET['year']) : null;

    // Build the SQL query
    $query = "
        SELECT 
            DAY(timeStamp) AS day_of_month,
            COUNT(*) AS total_users
        FROM 
            users
        WHERE 
            (:month IS NULL OR MONTH(timeStamp) = :month)
            AND (:year IS NULL OR YEAR(timeStamp) = :year)
        GROUP BY 
            DAY(timeStamp)
        ORDER BY 
            day_of_month;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':month', $month, PDO::PARAM_INT);
    $stmt->bindValue(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($results);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>