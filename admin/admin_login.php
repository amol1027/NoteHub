<?php
session_start();

// Create connection
$conn = new mysqli('localhost', 'root', '', 'justclick');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = false; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Corrected: Use 'college_id' from form input instead of 'username'
    $username = $conn->real_escape_string($_POST['college_id']); 
    $password = $conn->real_escape_string($_POST['password']); 
    // Hardcoded admin credentials
    $admin_username = 'admin';
    $admin_password = 'admin123'; // Hardcoded admin password

    // Check if the credentials match the admin credentials
    if ($username === $admin_username && $password === $admin_password) {
        // Admin authentication successful
        $_SESSION['user'] = [
            'username' => $admin_username,
            'role' => 'admin' // Add a role to identify the user as an admin
        ];
        header('Location: ./d_board.php'); // Redirect to admin dashboard
        exit();
    } else {
        $error = "Invalid username or password"; // Set error message if credentials don't match
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/css.css">

    <style>
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
    </style>
</head>
<header>
        <nav>
            <div class="logo"><a href="../index.php">NoteHub</a></div>
            <ul class="nav-links">
                <li class="dropdown">
                    <a href="#">Register</a>
                    <div class="dropdown-content">
                        <a href="../student/stud_register.php">Student Register</a>
                        <a href="../college/collage_reg.php">College Register</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Login</a>
                    <div class="dropdown-content">
                        <a href="../student/login.php">Student Login</a>
                        <a href="../teacher/teach_log.php">Teacher Login</a>
                        <a href="../college/col_login.php">College Login</a>
                    </div>
                </li>
                <li><a href="../index.php#about">About Us</a></li>
            </ul>
        </nav>
    </header>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-semibold text-center mb-6">Admin Login</h2>
        
        <?php if ($error): ?>
            <p class="text-red-500 text-center mb-4"> <?php echo $error; ?> </p>
        <?php endif; ?>
        
        <form action="" method="POST">
            <div class="mb-4">
                <label for="college_id" class="block text-gray-700 font-medium">User Id</label>
                <input type="text" name="college_id" class="w-full px-4 py-2 mt-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your College ID" required>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 mt-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="w-full py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600">Login</button>
        </form>
    </div>
</body>
</html>
