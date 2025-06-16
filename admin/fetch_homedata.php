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

    // Build the SQL query to count links
    $query = "
        SELECT 
            DAY(timestamp) AS day_of_month,
            SUM(LENGTH(links) - LENGTH(REPLACE(links, ',', '')) + 1) AS total_links
        FROM 
            homedata
        WHERE 
            (:month IS NULL OR MONTH(timestamp) = :month)
            AND (:year IS NULL OR YEAR(timestamp) = :year)
        GROUP BY 
            DAY(timestamp)
        ORDER BY 
            day_of_month;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':month', $month, PDO::PARAM_INT);
    $stmt->bindValue(':year', $year, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fill missing days with zero values
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month ?: date('m'), $year ?: date('Y'));
    $fullData = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $found = false;
        foreach ($results as $row) {
            if ($row['day_of_month'] == $day) {
                $fullData[] = $row;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $fullData[] = ['day_of_month' => $day, 'total_links' => 0];
        }
    }

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($fullData);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>