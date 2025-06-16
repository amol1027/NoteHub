<?php
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'justclick';
$username = 'root';
$password = '';

// Error handling
try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO contact_form (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $message]);

    echo json_encode(['success' => true, 'message' => 'Thank you for your message!']);
    
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}