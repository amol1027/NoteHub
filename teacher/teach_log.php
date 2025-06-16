<?php
session_start();

$error = '';

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "justclick";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect input data
    $college_id = $_POST['college_id'] ?? '';
    $teacher_id = $_POST['teacher_id'] ?? '';
    $password_input = $_POST['password'] ?? '';

    // Validate college
    $sql = "SELECT * FROM college WHERE col_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $college_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $college_row = $result->fetch_assoc();
        $college_name = $college_row['col_name'];

        // Validate teacher
        $sql_teacher = "SELECT * FROM $college_name WHERE t_id = ? AND t_password = ?";
        $stmt_teacher = $conn->prepare($sql_teacher);
        $stmt_teacher->bind_param("ss", $teacher_id, $password_input);
        $stmt_teacher->execute();
        $result_teacher = $stmt_teacher->get_result();

        if ($result_teacher->num_rows > 0) {
            $_SESSION['college_id'] = $college_id;
            $_SESSION['college_name'] = $college_name;
            $_SESSION['t_id'] = $teacher_id;
            date_default_timezone_set('Asia/Kolkata');
            $current_time = date("Y-m-d H:i:s");

            // Update the last login time in the database
            $update_sql = "UPDATE teachers SET last_login = ? WHERE t_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $current_time, $teacher_id);
            $update_stmt->execute();
            $update_stmt->close();

            header("Location: ./teac_port.php");
            exit();
        } else {
            $error = "Invalid Teacher ID or Password.";
        }
        $stmt_teacher->close();
    } else {
        $error = "Invalid College ID.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
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
                        <a href="../college/col_login.php">College Login</a>
                        <a href="../admin/admin_login.php">Admin Login</a>
                    </div>
                </li>
                <li><a href="#about">About Us</a></li>
            </ul>
        </nav>
    </header>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-semibold text-center mb-6">Teacher Login</h2>
        
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-4">
                <label for="college_id" class="block text-gray-700 font-medium">College ID</label>
                <input type="text" name="college_id" 
                    value="<?php echo htmlspecialchars($_POST['college_id'] ?? ''); ?>"
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="Enter your College ID" required>
            </div>

            <div class="mb-4">
                <label for="teacher_id" class="block text-gray-700 font-medium">Teacher ID</label>
                <input type="text" name="teacher_id" 
                    value="<?php echo htmlspecialchars($_POST['teacher_id'] ?? ''); ?>"
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="Enter your Teacher ID" required>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-medium">Password</label>
                <input type="password" name="password" 
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="Enter your password" required>
            </div>

            <button type="submit" class="w-full py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600">
                Login
            </button>
            
        </form>
    </div>
</body>
</html>