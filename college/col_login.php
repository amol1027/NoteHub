<?php
session_start();
?>
<?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Create database connection
            $conn = new mysqli('localhost', 'root', '', 'justclick');

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Sanitize input to prevent SQL injection
            $username = $conn->real_escape_string($username);
            $password = $conn->real_escape_string($password);

            // Query to fetch college details
            $query = "SELECT * FROM college WHERE col_name = '$username'";  
            $result = $conn->query($query);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if ($user['status'] == 'pending') {
                    $error_message = "Your registration is pending...";
                } elseif ($password == $user['col_password']) {
                    // Set session data
                    $_SESSION['college_id'] = $user['col_id'];
                    $_SESSION['college_name'] = $user['col_name'];
                    $_SESSION['college_mode'] = $user['col_mode'];
                    $_SESSION['user'] = $user;  
                    
                    // Redirect to home page
                    header('Location:./mycollage3.php');
                    exit();
                } else {
                    $error_message = "Invalid credentials";
                }
            } else {
                $error_message = "Invalid credentials";
            }
            
            // Close the database connection
            $conn->close();
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
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
            <div class="logo">NoteHub</div>
            <ul class="nav-links">
                <li class="dropdown">
                    <a href="#">Register</a>
                    <div class="dropdown-content">
                        <a href="./student/stud_register.php">Student Register</a>
                        <a href="./collage_reg.php">College Register</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Login</a>
                    <div class="dropdown-content">
                        <a href="../student/login.php">Student Login</a>
                        <a href="../teacher/teach_log.php">Teacher Login</a>
                        <a href="./col_login.php">College Login</a>
                        <a href="../admin/admin_login.php">Admin Login</a>
                    </div>
                </li>
                <li><a href="../#about">About Us</a></li>
            </ul>
        </nav>
    </header>
<body class="bg-gray-100 flex justify-center items-center h-screen">

    <div class="w-96 bg-white p-6 rounded shadow-md">
        <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
        
        <form method="POST" action="">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">College Name</label>
                <input type="text" name="username" class="mt-1 p-2 w-full border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password" class="mt-1 p-2 w-full border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center mt-1 overflow-hidden">
                        <svg class="h-5 w-5 text-gray-400 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="w-full p-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Login</button>
            <p class="text-sm text-center text-gray-600">Don't have account?
        <a href="./collage_reg.php" rel="noopener noreferrer" class="focus:underline hover:underline">Sign up here</a><br>
    </p>
        </form>
    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($error_message)): ?>
    <div id="errorModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="bg-white p-6 rounded shadow-md w-96">
            <p class="text-center text-red-600 font-bold"><?php echo $error_message; ?></p>
            <button onclick="closeModal()" class="mt-4 w-full p-2 bg-blue-600 text-white rounded hover:bg-blue-700">Close</button>
        </div>
    </div>
    <script>
        function closeModal() {
            document.getElementById("errorModal").style.display = "none";
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
    <?php endif; ?>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const eyeIcon = document.querySelector('.h-5.w-5');
                if (eyeIcon) {
                    if (type === 'password') {
                        // Show eye icon (password is hidden)
                        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>`;
                    } else {
                        // Show crossed eye icon (password is visible)
                        eyeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>`;
                    }
                }
            }
        }
    </script>
</body>
</html>