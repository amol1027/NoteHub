<?php
session_start();
require "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$conn = new mysqli('localhost', 'root', '', 'justclick');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    // Retrieve form data
$froll = $_POST['roll'];
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$email = $_POST['email'];
$contact = $_POST['phone-no']; // Cast to integer
$dob = $_POST['dob'];
$gender = $_POST['gender'];
$collegename = $_POST['college'];
$branch = $_POST['branch'];
$c_year = $_POST['cyear'];
$username = $_POST['username'];
$password = $_POST['fpass']; // Hashed password
$confirm_password = $_POST['password'];

// Handle 'other' college name
if ($collegename === 'other') {
    $collegename = $_POST['other_college'] ?? '';
    if (empty($collegename)) {
        $errors[] = "College name is required for 'Other'.";
    }
}

// Check password match
if ($_POST['fpass'] !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Insert into database
if (empty($errors)) {
    $isApproved = ($collegename === 'other') ? 1 : 0; // Set approval logic

    $sql = "INSERT INTO `users` 
        (`id`,`firstName`, `lastname`, `username`, `password`, `gender`, `email`, `contact`, `DoB`, `collegeName`, `branch`, `cur_year`, `is_approved`) 
        VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssssssi", 
        $froll,$fname, $lname, $username, $password, $gender, 
        $email, $contact, $dob, $collegename, $branch, $c_year, $isApproved
    );

    if ($stmt->execute()) {
        $mail = new PHPMailer(true);

        $mail->isSMTP();                            
        $mail->Host = 'smtp.gmail.com '; 
        $mail->SMTPAuth = true;                     
        $mail->Username = 'notehub11@gmail.com';                
        $mail->Password = 'wjgf dhdy wvkr wvnr';                         
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // recipients
        $mail->setFrom('notehub11@gmail.com', 'NoteHub');
        $mail->addAddress($email,$fname);     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Welcome to NoteHub - Registration Successful';
        $mail->Body    = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #dddddd;
            border-radius: 5px;
        }
        .header {
            background-color: #000000;
            color: #ffffff;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 12px;
            color: #777777;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #000000;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .details {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .details table {
            width: 100%;
        }
        .details td {
            padding: 8px;
            border-bottom: 1px solid #eeeeee;
        }
        .details td:first-child {
            font-weight: bold;
            width: 40%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to NoteHub!</h1>
        </div>
        <div class="content">
            <p>Hello <strong>' . $fname . ' ' . $lname . '</strong>,</p>
            <p>Thank you for registering with NoteHub. Your account has been successfully created!</p>
            
            <div class="details">
                <h3>Your Account Information:</h3>
                <table>
                    <tr>
                        <td>Student ID:</td>
                        <td>' . $froll . '</td>
                    </tr>
                    <tr>
                        <td>Username:</td>
                        <td>' . $username . '</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>' . $email . '</td>
                    </tr>
                    <tr>
                        <td>College:</td>
                        <td>' . $collegename . '</td>
                    </tr>
                    <tr>
                        <td>Branch:</td>
                        <td>' . $branch . '</td>
                    </tr>
                    <tr>
                        <td>Year:</td>
                        <td>' . $c_year . '</td>
                    </tr>
                </table>
            </div>
            
            <p>You can now log in to your account using your username and password.</p>
            
            <div style="text-align: center;">
                <a href="http://localhost/NoteHub/student/login.php">Login to Your Account</a>
            </div>
            
            <p>If you have any questions or need assistance, please don\'t hesitate to contact us.</p>
            
            <p>Best regards,<br>The NoteHub Team</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date("Y") . ' NoteHub. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>';
         
        $mail->send();
        echo "<script>alert('Registration Successful!'); window.location.href='../student/login.php';</script>";

    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
}
}
// Fetch approved colleges from database
$colleges = [];
$result = $conn->query("SELECT col_name FROM college WHERE status = 'approved'");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $colleges[] = $row['col_name'];
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Metadata and stylesheets -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/css.css">
    <!-- Tailwind CSS integration -->
    <script src="https://cdn.tailwindcss.com/"></script>
    
    
    <!-- Form validation styling -->
    <style>
        input:invalid, select:invalid {
            border-color: #ff0000;
            box-shadow: 0 0 3px #ff0000;
        }
        input:valid, select:valid {
            border-color: #00ff00;
            box-shadow: 0 0 3px #00ff00;
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
    <title>Justkclick</title>
</head>

<body class="h-screen bg-slate-200">
    <!-- Navigation Header -->
    <header>
        <nav>
            <div class="logo"><a href="../index.php">NoteHub</a></div>
            <ul class="nav-links">
                <li class="dropdown">
                    <a href="#">Register</a>
                    <div class="dropdown-content">
                        <a href="../college/collage_reg.php">College Register</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Login</a>
                    <div class="dropdown-content">
                        <a href="./login.php">Student Login</a>
                        <a href="../teacher/teach_log.php">Teacher Login</a>
                        <a href="../college/col_login.php">College Login</a>
                        <a href="../admin/admin_login.php">Admin Login</a>
                    </div>
                </li>
                <li><a href="../index.php#about">About Us</a></li>
            </ul>
        </nav>
    </header>
    <!-- Registration Form Container -->
    <div class="md:max-w-[40%] mx-auto mt-[7%] bg-white shadow-lg rounded-lg overflow-hidden ">
        <div class="text-2xl py-4 px-6 bg-black text-white text-center font-bold uppercase">
            STUDENT REGISTER
        </div>
        
        <!-- Registration Form -->
        <form class="py-4 px-6 form-animation"  method="POST">
            <!-- ID Input -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="college-id">
                    ID
                </label>
                <input
                    class="shadow appearance-none border hover:scale-105 duration-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="text" placeholder="Roll Number or College Id" name="roll" id="roll" required
                    pattern="\d+" title="Please enter numeric College ID/Roll Number">
            </div>

            <!-- Name Fields -->
            <div class="md:grid grid-cols-2">
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="first name">
                        FIRST NAME 
                    </label>
                    <input
                        class="shadow appearance-none border rounded hover:scale-105 duration-300 w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" placeholder="Enter Your First Name" name="fname" required
                        pattern="[A-Za-z\s'-]+" title="Only letters, spaces, apostrophes, and hyphens allowed">
                </div>
                <div class="mb-4 md:ml-4">
                    <label class="block text-gray-700 font-bold mb-2" for="last name">
                        LAST NAME 
                    </label>
                    <input
                        class="shadow appearance-none border rounded hover:scale-105 duration-300 w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        type="text" placeholder="Enter Your Last Name" name="lname" required
                        pattern="[A-Za-z\s'-]+" title="Only letters, spaces, apostrophes, and hyphens allowed">
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="email">
                    Email
                </label>
                <input
                    class="shadow appearance-none border rounded w-full hover:scale-105 duration-300 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="email" name="email" placeholder="Enter your email" required
                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="phone">
                    Phone Number
                </label>
                <input
                    class="shadow appearance-none border hover:scale-105 duration-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    name="phone-no" type="tel" placeholder="Enter your phone number" required
                    pattern="[0-9]{10}" title="10-digit phone number required">
            </div>

            <!-- Date of Birth -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="date">
                    Date of Birth
                </label>
                <input
                    class="shadow appearance-none border hover:scale-105 duration-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    name="dob" type="date" required 
                    max="<?= date('Y-m-d', strtotime('-13 years')) ?>">
            </div>

            <!-- Gender Selection -->
            <div class="mb-4">
                <h1 class="block text-gray-700 font-bold mb-2">Gender</h1>
            </div>
            <div class="md:grid grid-cols-2">
                <div class="flex items-center mb-4">
                    <input type="radio" value="male" name="gender" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500" required>
                    <label class="ms-2 text-lg font-semibold text-gray-900">Male</label>
                </div>
                <div class="flex items-center">
                    <input type="radio" value="female" name="gender" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                    <label class="ms-2 text-lg font-medium text-gray-900">Female</label>
                </div>
            </div>

            <!-- College Selection -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="college-select">
                    SELECT COLLEGE
                </label>
                <select
                    class="shadow appearance-none border hover:scale-105 duration-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="college-select" name="college" onchange="showCourses()" required>
                    <option value="">Select college</option>
                    <?php foreach ($colleges as $college): ?>
                        <option value="<?= htmlspecialchars($college) ?>"><?= htmlspecialchars($college) ?></option>
                    <?php endforeach; ?>
                    <option value="other">Other college</option>
                </select>
            </div>

            <!-- Dynamic Form Elements -->
            <div id="other-college" style="display: none;">
                <label class="block text-gray-700 font-bold mb-2">
                    College Name
                </label>
                <input
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight"
                    type="text" name="other_college" placeholder="Enter college name">
            </div>
            
            <!-- Course Selection -->
            <div id="courses" style="display: none;">
                <label class="block text-gray-700 font-bold mb-2" for="course-select">
                    COURSES
                </label>
                <select
                    class="shadow appearance-none border hover:scale-105 duration-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="course-select" name="branch">
                    <option value="">Select Your Branch</option>
                    <option value="BCA">BCA</option>
                    <option value="BBA">BBA</option>
                    <option value="MBA">MBA</option>
                </select>
            </div>
            
            <!-- Year Selection -->
            <div id="year" style="display: none;">
                <label class="block text-gray-700 font-bold mb-2" for="year-select">
                    YEAR
                </label>
                <select
                    class="shadow appearance-none border hover:scale-105 duration-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="year-select" name="cyear">
                    <option value="">----</option>
                    <option value="first-year">FY</option>
                    <option value="second-year">SY</option>
                    <option value="third-year">TY</option>
                </select>
            </div>

            <!-- Username -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="user name">
                    User Name
                </label>
                <input
                    class="shadow appearance-none border rounded hover:scale-105 duration-300 w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="text" placeholder="Choose Unique Username" name="username" required
                    pattern="[A-Za-z0-9_]+" title="Only letters, numbers, and underscores allowed">
            </div>

            <!-- Password Section -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="Password">
                    Password
                </label>
                <input
                    class="shadow appearance-none border rounded hover:scale-105 duration-300 w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="password" 
                    placeholder="******" 
                    name="fpass" 
                    required
                    minlength="8" 
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                    title="Must contain at least one number and one uppercase letter"
                    onkeyup="validatePasswordStrength(this.value)"
                >
                <!-- Password Requirements -->
                <div id="password-hints" class="text-sm text-gray-600 mt-2">
                    <span class="block">Password must contain:</span>
                    <span id="length" class="text-red-500">✓ 8+ characters</span>
                    <span id="uppercase" class="text-red-500">✓ Uppercase letter</span>
                    <span id="lowercase" class="text-red-500">✓ Lowercase letter</span>
                    <span id="number" class="text-red-500">✓ Number</span>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="Confirm Password">
                    Confirm Password
                </label>
                <input
                    class="shadow appearance-none border rounded hover:scale-105 duration-300 w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    type="password" 
                    name="password" 
                    placeholder="******" 
                    required
                    oninput="validatePassword(this)"
                >
                <div class="flex items-center mt-2">
                    <input type="checkbox" id="showPassword" class="w-4 h-4" onclick="togglePasswordVisibility()">
                    <label for="showPassword" class="ml-2 text-sm text-gray-600">Show passwords</label>
                </div>
            </div>

            <!-- Form Submission -->
            <div class="flex items-center justify-center mb-4">
                <button
                    class="bg-black  text-white py-2 px-4 hover:scale-105 duration-300 rounded hover:bg-violet-700  focus:outline-none focus:shadow-outline"
                    type="submit">
                    Submit
                </button>
            </div>
            <div>
                <h2 class="text-center"> Already have an account? <a href="../student/login.php">Log in</a></h2>
            </div>
        </form>
    </div>

    <!-- Form Validation Scripts -->
    <script>
        /**
         * Toggles visibility of college-related form elements
         */
        function showCourses() {
            const college = document.getElementById("college-select").value;
            const coursesDiv = document.getElementById("courses");
            const yearDiv = document.getElementById("year");
            const otherCollegeDiv = document.getElementById("other-college");
            
            if (college === "other") {
                coursesDiv.style.display = "none";
                yearDiv.style.display = "none";
                otherCollegeDiv.style.display = "block";
                document.querySelector("[name='other_college']").required = true;
                document.querySelector("[name='branch']").required = false;
                document.querySelector("[name='cyear']").required = false;
            } else if (college) {
                coursesDiv.style.display = "block";
                yearDiv.style.display = "block";
                otherCollegeDiv.style.display = "none";
                document.querySelector("[name='branch']").required = true;
                document.querySelector("[name='cyear']").required = true;
                document.querySelector("[name='other_college']").required = false;
            } else {
                coursesDiv.style.display = "none";
                yearDiv.style.display = "none";
                otherCollegeDiv.style.display = "none";
            }
        }

        /**
         * Validates password confirmation match
         */
        function validatePassword(confirm) {
            const password = document.querySelector("[name='fpass']");
            if (confirm.value !== password.value) {
                confirm.setCustomValidity("Passwords do not match");
            } else {
                confirm.setCustomValidity('');
            }
        }

        /**
         * Visual feedback for password strength requirements
         */
        function validatePasswordStrength(value) {
            document.getElementById('length').style.color = value.length >= 8 ? '#22c55e' : '#ef4444';
            document.getElementById('uppercase').style.color = /[A-Z]/.test(value) ? '#22c55e' : '#ef4444';
            document.getElementById('lowercase').style.color = /[a-z]/.test(value) ? '#22c55e' : '#ef4444';
            document.getElementById('number').style.color = /\d/.test(value) ? '#22c55e' : '#ef4444';
        }

        /**
         * Toggles password visibility for both password fields
         */
        function togglePasswordVisibility() {
            const passwordFields = document.querySelectorAll('[type="password"]');
            const showPassword = document.getElementById('showPassword').checked;
            
            passwordFields.forEach(field => {
                field.type = showPassword ? 'text' : 'password';
            });
        }
    </script>
</body>
</html>