<?php
session_start();

// Redirect authenticated users away from login page
if (isset($_SESSION['user'])) {
    header('Location: ../home.php');
    exit();
}
// Create connection
$conn = new mysqli('localhost', 'root', '', 'justclick');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Clear previous errors on initial load
if (!isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
    unset($_SESSION['form_data']);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']); 
    $password = $conn->real_escape_string($_POST['password']);

    
    // Store form data in session
    $_SESSION['form_data'] = ['username' => htmlspecialchars($username)];

    // Regular user authentication
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($password == $user['password']) {
            // Check if user is approved
            if ($user['is_approved'] == 1) { 
                $_SESSION['user'] = $user;
                unset($_SESSION['form_data']);
                unset($_SESSION['errors']);
                header('Location: ../home.php');
                exit();
            } else if ($user['is_approved'] == 0) {
                // User exists but not approved
                $_SESSION['errors'] = ['login' => 'Your account is awaiting approval. Please wait.'];
                header('Location: login.php');
                exit();
            }
            else {
                // User exists but is banned
                $_SESSION['errors'] = ['login' => 'Your request has been denied. Please contact the college administrator.'];
                header('Location: login.php');
                exit();
            }
            
        } else {
            // Password mismatch
            $_SESSION['errors'] = ['login' => 'Invalid username or password'];
            header('Location: login.php');
            exit();
        }
    }

    // Set error and redirect back
    $_SESSION['errors'] = ['login' => 'Invalid username or password'];
    header('Location: login.php');
    exit();
}

// Retrieve errors and form data from session
$errors = $_SESSION['errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors']);
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/css.css">
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            margin-top: 10%;
        }
        .password-container {
        position: relative;
    }
    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6b7280;
    }
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
<body class="bg-slate-200">
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
                        <a href="../teacher/teach_log.php">Teacher Login</a>
                        <a href="../college/col_login.php">College Login</a>
                        <a href="../admin/admin_login.php">Admin Login</a>
                    </div>
                </li>
                <li><a href="../index.php#about">About Us</a></li>
            </ul>
        </nav>
    </header>
<div class=" w-full max-w-md p-4 rounded-md shadow sm:p-8 bg-gray-50 text-gray-800">
    <h2 class="mb-3 text-3xl font-semibold text-center">Login to your account</h2>
   
    
    <div class="flex items-center w-full my-4">
        <hr class="w-full text-gray-600">
        
        <hr class="w-full text-gray-600">
    </div>

    <?php if (isset($errors['login'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $errors['login']; ?></span>
            </div>
        <?php endif; ?>

    
    <form action="login.php" method="post" class="space-y-6">
        <div class="space-y-4">
            <div class="space-y-2">
                <label for="username" class="block text-sm">User Name</label>
                <input type="text" name="username" placeholder="user123" 
                       class="shadow appearance-none border hover:scale-105 duration-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       required>
            </div>
            <div class="space-y-2">
    <div class="flex justify-between">
        <label for="password" class="text-sm">Password</label>
    </div>
    <div class="password-container">
        <input type="password" name="password" id="password" placeholder="*****" 
               class="shadow appearance-none border hover:scale-105 duration-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
               required>
        <svg class="toggle-password w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </div>
</div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 duration-300 w-full ">
            Log in
        </button>
        <p class="text-sm text-center text-gray-600">Don't have account?
        <a href="../student/stud_register.php" rel="noopener noreferrer" class="focus:underline hover:underline">Sign up here</a><br>
    </p>
    </form>
</div>
<script>
document.querySelector('.toggle-password').addEventListener('click', function(e) {
    const passwordInput = document.querySelector('#password');
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // Toggle eye icon
    this.querySelectorAll('path').forEach(path => {
        path.style.stroke = type === 'password' ? 'currentColor' : '#3b82f6';
    });
});
</script>
</body>
</html>