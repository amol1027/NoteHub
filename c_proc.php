<?php
// Start the session to access session variables
session_start();
require "vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "justclick";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$college_id = $_SESSION['college_id'];
$teacher_id = $_SESSION['t_id'];  
$college_name = $_SESSION['college_name'];
$branch = $_POST['branch'];

// Directory to store uploaded files
$uploadDir = 'documents/';

// Check if a file is uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && isset($_POST['title'])) {
    // Get file information
    $fileName = basename($_FILES['file']['name']);
    $fileType = $_FILES['file']['type'];
    $fileSize = $_FILES['file']['size'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    
    // Generate a unique filename to prevent duplicates
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $fileBaseName = pathinfo($fileName, PATHINFO_FILENAME);
    $uniqueFileName = $fileBaseName . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $uniqueFileName;

    // Get document title
    $title = $_POST['title'];

    // Allow only specific file types
    $allowedTypes = [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg', 
        'image/png',
        'image/gif'
    ];

    // Check if file type is allowed
    if (in_array($fileType, $allowedTypes)) {
        // Check if the folder exists; if not, create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($fileTmpName, $filePath)) {
            // Prepare SQL statement to insert file details into the database
            $stmt = $conn->prepare("INSERT INTO documents (title, name, t_id, col_name, type, size, content, branch) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisssss", $title, $uniqueFileName, $teacher_id, $college_name, $fileType, $fileSize, $filePath, $branch);

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['message'] = ['type' => 'success', 'content' => 'Document uploaded successfully.'];
               
                // Get students from the same college and branch
                $studentQuery = "SELECT firstName, lastname, email FROM users 
                                WHERE collegeName = ? AND branch = ? AND email IS NOT NULL";
                $studentStmt = $conn->prepare($studentQuery);
                $studentStmt->bind_param("ss", $college_name, $branch);
                $studentStmt->execute();
                $result = $studentStmt->get_result();
                
                // If students found, send emails
                if ($result->num_rows > 0) {
                    // Get teacher's name for the email
                    $teacherQuery = "SELECT t_name FROM teachers WHERE t_id = ?";
                    $teacherStmt = $conn->prepare($teacherQuery);
                    $teacherStmt->bind_param("i", $teacher_id);
                    $teacherStmt->execute();
                    $teacherResult = $teacherStmt->get_result();
                    $teacherData = $teacherResult->fetch_assoc();
                    $teacherName = $teacherData ? $teacherData['t_name'] : 'Your teacher';
                    $teacherStmt->close();
                    
                    // Configure PHPMailer
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'notehub11@gmail.com';
                    $mail->Password = 'wjgf dhdy wvkr wvnr';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->setFrom('notehub11@gmail.com', 'NoteHub');
                    $mail->isHTML(true);
                    
                    // Email subject
                    $mail->Subject = 'New Document Available on NoteHub';
                    
                    // Prepare email body - same for all recipients
                    $emailBody = '<!DOCTYPE html>
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
                                                color: #ffffff !important;
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
                                        </style>
                                    </head>
                                    <body>
                                        <div class="container">
                                            <div class="header">
                                                <h1>New Document Available!</h1>
                                            </div>
                                            <div class="content">
                                                <p>Hello,</p>
                                                <p>' . $teacherName . ' has uploaded a new document for your branch.</p>
                                                
                                                <div class="details">
                                                    <h3>Document Details:</h3>
                                                    <p><strong>Title:</strong> ' . $title . '</p>
                                                    <p><strong>File Name:</strong> ' . $uniqueFileName . '</p>
                                                    <p><strong>Branch:</strong> ' . $branch . '</p>
                                                    <p><strong>College:</strong> ' . $college_name . '</p>
                                                </div>
                                                
                                                <p>You can log in to your NoteHub account to view and download this document.</p>
                                                
                                                <div style="text-align: center;">
                                                    <a href="http://localhost/NoteHub/student/login.php" class="button">Login to View Document</a>
                                                </div>
                                                
                                                <p>If you have any questions, please contact your teacher or the NoteHub support team.</p>
                                                
                                                <p>Best regards,<br>The NoteHub Team</p>
                                            </div>
                                            <div class="footer">
                                                <p>&copy; ' . date("Y") . ' NoteHub. All rights reserved.</p>
                                                <p>This is an automated email, please do not reply.</p>
                                            </div>
                                        </div>
                                    </body>
                                    </html>';
                    
                    $mail->Body = $emailBody;
                    
                    // Batch size for sending emails (adjust based on your server's capacity)
                    $batchSize = 2000;
                    $totalStudents = $result->num_rows;
                    $emailsSent = 0;
                    $currentBatch = 0;
                    $recipients = [];
                    
                    // Process students in batches
                    while ($student = $result->fetch_assoc()) {
                        $recipients[] = [
                            'email' => $student['email'],
                            'name' => $student['firstName'] . ' ' . $student['lastname']
                        ];
                        $currentBatch++;
                        
                        // When batch is full or we've reached the end, send the emails
                        if ($currentBatch >= $batchSize || $emailsSent + $currentBatch >= $totalStudents) {
                            try {
                                $mail->clearAllRecipients();
                                
                                // Add all recipients in this batch as BCC
                                foreach ($recipients as $recipient) {
                                    $mail->addBCC($recipient['email'], $recipient['name']);
                                }
                                
                                // Send the batch
                                $mail->send();
                                $emailsSent += count($recipients);
                                
                                // Reset for next batch
                                $recipients = [];
                                $currentBatch = 0;
                            } catch (Exception $e) {
                                // Log email sending errors but continue
                                error_log("Failed to send email batch: {$mail->ErrorInfo}");
                            }
                        }
                    }
                    
                    // Add to success message
                    $_SESSION['message']['content'] .= " Notification emails sent to {$emailsSent} students.";
                }
                
                $studentStmt->close();
            } else {
                $_SESSION['message'] = ['type' => 'error', 'content' => 'Failed to upload the file. Error: ' . $stmt->error];
            }

            // Close the statement
            $stmt->close();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'content' => 'Failed to move the uploaded file.'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'content' => 'Invalid file type. Only PDF, DOC, DOCX, JPEG, PNG, and GIF files are allowed.'];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'content' => 'No file uploaded or title provided.'];
}

// Close the connection
$conn->close();

// Redirect back to the upload form page with the message
header("Location: ./cupload.php");
exit();
