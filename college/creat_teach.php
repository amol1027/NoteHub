<?php
session_start();
require "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if (isset($_SESSION['college_id'])) {
  $college_id = $_SESSION['college_id'];
  $college_name = $_SESSION['college_name'];
  $college_mode = $_SESSION['college_mode'];
} else {
  echo "You are not logged in.";
  exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $t_fname = $_POST['t_fname'];
  $t_lname = $_POST['t_lname'];
  $t_branch = $_POST['t_branch'];
  $t_email = $_POST['t_email'];
  $t_password = $_POST['t_password'];
  $t_phone = $_POST['t_phone'];

  $conn = new mysqli('localhost', 'root', '', 'justclick');
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Sanitize input
  $t_fname = mysqli_real_escape_string($conn, $t_fname);
  $t_lname = mysqli_real_escape_string($conn, $t_lname);
  $t_branch = mysqli_real_escape_string($conn, $t_branch);
  $t_email = mysqli_real_escape_string($conn, $t_email);
  $t_password = mysqli_real_escape_string($conn, $t_password);
  $t_phone = mysqli_real_escape_string($conn, $t_phone);

  // Hash password
  

  $checkQuery = "SELECT * FROM teachers WHERE t_email = '$t_email'";
  $result = $conn->query($checkQuery);
  if ($result->num_rows > 0) {
    echo "<script>
        alert('User $t_fname $t_lname already exists.');
        window.location.href='creat_teach.php';
      </script>";
  } else {
    // Insert into the master table
    $insertMaster = "INSERT INTO `$college_name`(`t_id`, `t_fname`, `t_lname`, `t_branch`, `t_email`, `t_password`, `t_phone`, `created_at`)
      VALUES (NULL, '$t_fname', '$t_lname', '$t_branch', '$t_email', '$t_password', '$t_phone', current_timestamp())";

    $full_name = $t_fname . " " . $t_lname;

    if ($conn->query($insertMaster) === TRUE) {
      $fetchquery = "SELECT * FROM `$college_name` WHERE t_email = '$t_email'";
      $result = $conn->query($fetchquery);
      $temp_data = $result->fetch_assoc();

      $tempid = $temp_data['t_id'];

      $sql = "INSERT INTO `teachers`(`t_id`, `t_name`, `col_name`, `t_email`, `t_branch`) 
              VALUES ('$tempid', '$full_name', '$college_name', '$t_email', '$t_branch')";

      if ($conn->query($sql) === TRUE) {

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
        $mail->addAddress($t_email, $full_name);     // Add a recipient

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Welcome to NoteHub - Teacher Account Created';
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
                    background-color: #4CAF50;
                    color: white;
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
                    background-color: #4CAF50;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 15px;
                }
                .info-box {
                    background-color: #e8f5e9;
                    border-left: 4px solid #4CAF50;
                    padding: 10px;
                    margin: 15px 0;
                }
                .credentials {
                    background-color: #fff3e0;
                    border: 1px solid #ffcc80;
                    border-radius: 5px;
                    padding: 15px;
                    margin: 15px 0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Welcome to NoteHub!</h2>
                </div>
                <div class="content">
                    <p>Dear <strong>' . $full_name . '</strong>,</p>
                    <p>Congratulations! Your teacher account has been created at <strong>' . $college_name . '</strong> on NoteHub.</p>
                    
                    <div class="info-box">
                        <p><strong>College:</strong> ' . $college_name . '</p>
                        <p><strong>College ID:</strong> ' . $college_id . '</p>
                        <p><strong>Department:</strong> ' . $t_branch . '</p>
                        <p><strong>Email:</strong> ' . $t_email . '</p>
                    </div>
                    
                    <p>Here are your login credentials:</p>
                    
                    <div class="credentials">
                        <p><strong>Teacher ID:</strong> ' . $tempid . '</p>
                        <p><strong>Password:</strong> ' . $t_password . '</p>
                    </div>
                    
                    <p><strong>Important:</strong> For security reasons, please change your password after your first login.</p>
                    
                    <p>As a teacher on NoteHub, you can:</p>
                    
                    <ul>
                        <li>Upload and share educational materials with your students</li>
                        <li>Create and organize course notes</li>
                        <li>Communicate with students and other faculty members</li>
                        <li>Access teaching resources and tools</li>
                    </ul>
                    
                    <p>To get started, click the button below to log in to your NoteHub account:</p>
                    
                    <div style="text-align: center;">
                        <a href="http://localhost/NoteHub/login.php" class="button">Log In to NoteHub</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please contact your college administrator or our support team at <a href="mailto:notehub11@gmail.com">notehub11@gmail.com</a>.</p>
                    
                    <p>Thank you for joining NoteHub!</p>
                    
                    <p>Best regards,<br>The NoteHub Team</p>
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' NoteHub. All rights reserved.</p>
                    <p>This is an automated email, please do not reply.</p>
                </div>
            </div>
        </body>
        </html>';
        
        // Plain text alternative for non-HTML mail clients
        $mail->AltBody = 'Welcome to NoteHub! 
        
Dear ' . $full_name . ',

Congratulations! Your teacher account has been created at ' . $college_name . ' on NoteHub.

College: ' . $college_name . '
Department: ' . $t_branch . '
Email: ' . $t_email . '

Your login credentials:
Teacher ID: ' . $tempid . '
Password: ' . $t_password . '

IMPORTANT: For security reasons, please change your password after your first login.

To get started, visit: http://localhost/NoteHub/login.php

If you have any questions, please contact your college administrator or our support team at notehub11@gmail.com.

Thank you for joining NoteHub!

Best regards,
The NoteHub Team';
        
        $mail->send();
        
        echo "<script>
              alert('Teacher added successfully');window.location.href='./mycollage3.php';</script>
            </script>";
      } else {
        echo "Error: " . $conn->error;
      }
    } else {
      echo "Error in inserting into `$college_name`: " . $conn->error;
    }
  }

  $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>

    <title>Document</title>
</head>
<body>
    <form action="" method="POST" class="max-w-lg mx-auto p-6 bg-white rounded-lg shadow-md">
        
      <h1 class="text-2xl font-bold">Teacher Registration</h1>
      <br>
        <div class="mb-4">
          <label for="t_fname" class="block text-sm font-medium text-gray-700">First Name:</label>
          <input type="text" name="t_fname" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>
      
        <div class="mb-4">
          <label for="t_lname" class="block text-sm font-medium text-gray-700">Last Name:</label>
          <input type="text" name="t_lname" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>
      
        <div class="mb-4">
          <label for="t_branch" class="block text-sm font-medium text-gray-700">Branch:</label>
          <input type="text" name="t_branch" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>
      
        <div class="mb-4">
          <label for="t_email" class="block text-sm font-medium text-gray-700">Email:</label>
          <input type="email" name="t_email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>
      
        <div class="mb-4">
          <label for="t_password" class="block text-sm font-medium text-gray-700">Password:</label>
          <input type="password" name="t_password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>
      
        <div class="mb-4">
          <label for="t_phone" class="block text-sm font-medium text-gray-700">Phone Number:</label>
          <input type="tel" name="t_phone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
        </div>
      
        <div class="mt-6">
          <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Submit</button>
        </div>
      </form>
      
</body>
</html>