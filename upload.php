<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from POST request
    $link = $_POST['link'];
    $des = $_POST['description'];
    $branch = $_POST['branch'];

    // Function to convert YouTube link to embed iframe
    function convertYoutube($string) {
        return preg_replace(
            "/[a-zA-Z\/\/:\.]*youtu(?:be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)(?:[&?\/]t=)?(\d*)(?:[a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
            "<iframe width=\"420\" height=\"315\" src=\"https://www.youtube.com/embed/$1?start=$2\" allowfullscreen></iframe>",
            $string
        );
    }

    // Convert YouTube link to embed format
    $link = convertYoutube($link);
    $new_link = trim($link, "</iframe>");

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'justclick');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve username from session
    if (!isset($_SESSION['user'])) {
        echo "<script>alert('User not logged in.');</script>";
        echo "<script>window.location.href='login.php';</script>";
        exit();
    }
    $user = $_SESSION['user'];
    $username = $user['username'];

    // Prepare and execute SQL statement with status = 'pending'
    $stmt = $conn->prepare("INSERT INTO homedata (username, description, links, branch, status, timestamp) VALUES (?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP())");
    $stmt->bind_param("ssss", $username, $des, $new_link, $branch);

    if ($stmt->execute()) {
        echo "<script>alert('Upload sent for admin approval!');</script>";
        echo "<script>window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Error uploading link. Please try again.');</script>";
        echo "<script>window.location.href='upload.php';</script>";
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>
