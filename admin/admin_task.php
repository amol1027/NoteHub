<?php
session_start();

require "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header('Location: ./admin_login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'justclick');
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle Actions
if (isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $type = $_GET['type'];
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    
    if ($id && in_array($action, ['approve', 'reject', 'delete']) && in_array($type, ['college', 'yt'])) {
        if ($action === 'delete' && $type === 'college') {
            $stmt = $conn->prepare("SELECT col_name FROM college WHERE col_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $collegeName = $row['col_name'];
                
                $deleteStmt = $conn->prepare("DELETE FROM college WHERE col_id = ?");
                $deleteStmt->bind_param("i", $id);
                
                if ($deleteStmt->execute()) {
                    $conn->query("DROP TABLE IF EXISTS `$collegeName`");
                    $message = "College and all associated data deleted successfully!";
                    $message_type = 'success';
                } else {
                    $message = "Error deleting college: " . $deleteStmt->error;
                    $message_type = 'error';
                }
                $deleteStmt->close();
            } else {
                $message = "College not found!";
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $table = ($type === 'college') ? 'college' : 'homedata';
            $id_column = ($type === 'college') ? 'col_id' : 'id';
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            
            $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE $id_column = ?");
            $stmt->bind_param("si", $status, $id);
            
            if ($stmt->execute()) {
                $message = ucfirst($type) . " request " . $status . " successfully!";
                $message_type = ($action === 'approve') ? 'success' : 'error';
                
                if ($action === 'approve' && $type === 'college') {
                    $stmt_select = $conn->prepare("SELECT col_name, col_email FROM college WHERE col_id = ?");
                    $stmt_select->bind_param("i", $id);
                    $stmt_select->execute();
                    $result = $stmt_select->get_result();
                    $row = $result->fetch_assoc();
                    $collegeName = $row['col_name'];
                    $email = $row['col_email'];
                    $stmt_select->close();
                    
                    $createTableQuery = "CREATE TABLE IF NOT EXISTS `$collegeName` (
                        t_id INT AUTO_INCREMENT PRIMARY KEY,
                        t_fname VARCHAR(50) NOT NULL,
                        t_lname VARCHAR(50) NOT NULL,
                        t_branch VARCHAR(32) NOT NULL,                
                        t_email VARCHAR(255) NOT NULL,
                        t_password VARCHAR(255) NOT NULL,
                        t_phone VARCHAR(20) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";
                    
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
        $mail->addAddress($email, $name);     // Add a recipient

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
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Welcome to NoteHub!</h2>
                </div>
                <div class="content">
                    <p>Dear <strong>' . $name . '</strong>,</p>
                    <p>Congratulations! Your college <strong>' . $collegeName . '</strong> has been successfully registered with NoteHub.</p>
                    
                    <div class="info-box">
                        <p><strong>College Name:</strong> ' . $collegeName . '</p>
                        <p><strong>Registration Status:</strong> Approved</p>
                        <p><strong>Account Email:</strong> ' . $email . '</p>
                    </div>
                    
                    <p>You can now start using NoteHub to manage your college notes, resources, and educational materials. Here\'s what you can do next:</p>
                    
                    <ul>
                        <li>Add teachers and students to your college account</li>
                        <li>Upload and organize educational materials</li>
                        <li>Create and share notes with your academic community</li>
                        <li>Manage your college resources efficiently</li>
                    </ul>
                    
                    <p>To get started, click the button below to log in to your NoteHub account:</p>
                    
                    <div style="text-align: center;">
                        <a href="http://localhost/NoteHub/login.php" class="button">Log In to NoteHub</a>
                    </div>
                    
                    <p>If you have any questions or need assistance, please don\'t hesitate to contact our support team at <a href="mailto:notehub11@gmail.com">notehub11@gmail.com</a>.</p>
                    
                    <p>Thank you for choosing NoteHub for your educational needs!</p>
                    
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
        
Dear ' . $name . ',

Congratulations! Your college ' . $collegeName . ' has been successfully registered with NoteHub.

College Name: ' . $collegeName . '
Registration Status: Approved
Account Email: ' . $email . '

You can now start using NoteHub to manage your college notes, resources, and educational materials.

To get started, visit: http://localhost/NoteHub/login.php

If you have any questions, please contact us at notehub11@gmail.com.

Thank you for choosing NoteHub!

Best regards,
The NoteHub Team';
        
        $mail->send();

                    if ($conn->query($createTableQuery) !== TRUE) {
                        $message .= " Error creating table: " . $conn->error;
                        $message_type = 'error';
                    }
                }
            } else {
                $message = "Error processing request: " . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
        
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $message_type;
        header('Location: admin_task.php');
        exit();
    }
}

// Fetch college data for editing
$college = [];
if (isset($_GET['edit_id'])) {
    $edit_id = filter_var($_GET['edit_id'], FILTER_VALIDATE_INT);
    if ($edit_id) {
        $stmt = $conn->prepare("SELECT col_id, col_name,col_email,col_phone,col_address, col_mode FROM college WHERE col_id = ?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $college = $result->fetch_assoc();
        } else {
            $message = "College not found!";
            $message_type = 'error';
            $_SESSION['message'] = $message;
            $_SESSION['message_type'] = $message_type;
            header('Location: admin_task.php');
            exit();
        }
        $stmt->close();
    }
}


// Handle College Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_college'])) {
    $col_id = filter_input(INPUT_POST, 'col_id', FILTER_VALIDATE_INT);
    $col_name = trim($_POST['col_name'] ?? '');
    $col_mode = trim($_POST['col_mode'] ?? '');
    $col_email = trim($_POST['col_email'] ?? '');
    $col_phone = trim($_POST['col_phone'] ?? '');
    $col_address = trim($_POST['col_address'] ?? '');

    if ($col_id && $col_name && $col_mode && $col_address) {
        // Get original college name first
        $stmt = $conn->prepare("SELECT col_name FROM college WHERE col_id = ?");
        $stmt->bind_param("i", $col_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $old_college = $result->fetch_assoc();
            $old_name = $old_college['col_name'];
            
            // Update college details
            $update_stmt = $conn->prepare("UPDATE college SET col_name = ?, col_email = ?, col_phone = ?, col_address = ?, col_mode = ? WHERE col_id = ?");
            $update_stmt->bind_param("sssssi", $col_name, $col_email, $col_phone, $col_address, $col_mode, $col_id);
            
            if ($update_stmt->execute()) {
                // Rename table if name changed
                if ($old_name !== $col_name) {
                    if (!$conn->query("ALTER TABLE `$old_name` RENAME TO `$col_name`")) {
                        $message = "College updated but table rename failed: " . $conn->error;
                        $message_type = 'error';
                    }
                }
                $message = "College updated successfully!";
                $message_type = 'success';
            } else {
                $message = "Error updating college: " . $update_stmt->error;
                $message_type = 'error';
            }
            $update_stmt->close();
        } else {
            $message = "College not found!";
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "All fields are required!";
        $message_type = 'error';
    }

    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $message_type;
    header('Location: admin_task.php');
    exit();
}

// Fetch pending college requests
$collegeResult = $conn->query("SELECT * FROM college WHERE status = 'pending'");
if ($collegeResult === false) {
    die("Error fetching college requests: " . $conn->error);
}

// Fetch pending YouTube links
$youtubeResult = $conn->query("SELECT * FROM homedata WHERE status = 'pending'");
if ($youtubeResult === false) {
    die("Error fetching YouTube links: " . $conn->error);
}

// Common Functions
function processLink($link) {
    if (preg_match("/youtu(?:\.be|be\.com)\/(?:watch\?v=|embed\/)?([a-zA-Z0-9\-_]+)(?:[&?\/]t=(\d+))?/i", $link, $matches)) {
        $videoId = $matches[1];
        $startTime = $matches[2] ?? 0;
        return "<iframe class='w-50 h-30 border border-gray-300 rounded-md' 
                src='https://www.youtube.com/embed/$videoId?start=$startTime' 
                allowfullscreen></iframe>";
    }
    return "<a href='" . htmlspecialchars($link) . "' target='_blank' 
            class='text-blue-500 underline'>" . htmlspecialchars($link) . "</a>";
}


// Display Notification Modals
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';

if ($message) {
    echo "<div x-data='{ show: true }' x-show='show' x-init='setTimeout(() => show = false, 5000)' 
         class='fixed right-5 top-5 z-50 transition-transform duration-300 ease-out'>
        <div class='flex items-center p-4 w-full max-w-xs rounded-lg shadow-md 
                    " . ($message_type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800') . "'>
            <div class='inline-flex flex-shrink-0 justify-center items-center w-8 h-8 
                        " . ($message_type === 'success' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100') . " 
                        rounded-lg'>
                <i class='mdi " . ($message_type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle') . " text-lg'></i>
            </div>
            <div class='ml-3 text-sm font-normal'>" . htmlspecialchars($message) . "</div>
        </div>
    </div>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin @ NoteHub</title>
    <meta name="description" content="Black and white theme description">
    <meta name="keywords" content="black, white, theme">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        .bg-black-alt  { background: #000000; }
        .text-black-alt  { color: #000000; }
        .border-black-alt { border-color: #000000; }
        .bg-white-alt { background: #dcd5d5; }
        .text-white-alt { color: #ffffff; }
        .border-white-alt { border-color: #ffffff; }
        .text-gray-light { color: #d3d3d3; }
    </style>
</head>
<body class="bg-slate-200 font-sans leading-normal tracking-normal">

<!-- Original Navbar -->
<nav id="header" class="bg-black-alt fixed w-full z-10 top-0 shadow-md">
    <div class="w-full container mx-auto flex flex-wrap items-center mt-0 pt-3 pb-3 md:pb-0">
        <!-- Navbar content unchanged -->
        <div class="w-1/2 pl-2 md:pl-0"></div>
        <div class="w-1/2 pr-0">
            <div class="flex relative inline-block float-right">
                <div class="relative text-sm text-white-alt">
                    <button id="userButton" class="flex items-center focus:outline-none mr-3">
                        <span class="hidden md:inline-block text-white-alt">Hi, Admin</span>
                    
                    </button>
                    <div id="userMenu" class="bg-gray-900 rounded shadow-md mt-2 absolute mt-12 top-5 right-0 min-w-full overflow-auto z-30 ">
						  <ul class="list-reset">
							
							<li><a href="../student/log_out.php" class="px-4 py-2 block text-gray-100 hover:bg-gray-800 no-underline hover:no-underline">Logout</a></li>
						  </ul>
					  </div>
                </div>

                <div class="block lg:hidden pr-4">
                    <button id="nav-toggle" class="flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="fill-current h-3 w-3" viewBox="0 0 20 20">
                            <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="w-full flex-grow lg:flex lg:items-center lg:w-auto hidden lg:block mt-2 lg:mt-0 bg-black-alt z-20" id="nav-content">
            <ul class="list-reset lg:flex flex-1 items-center px-4 md:px-0">
                <li class="mr-6 my-2 md:my-0">
                    <a href="d_board.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-300 no-underline hover:text-white-alt border-b-2 border-gray-600 hover:border-gray-400">
                        <i class="fas fa-home fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Home</span>
                    </a>
                </li>
                <li class="mr-6 my-2 md:my-0">
                    <a href="#" class="block py-1 md:py-3 pl-1 align-middle text-blue-300 hover:text-white-alt border-b-2 border-gray-600 hover:border-gray-400">
                        <i class="fas fa-tasks fa-fw mr-3 text-gray-300"></i><span class="pb-1 md:pb-0 text-sm">Tasks</span>
                    </a>
                </li>
                <li class="mr-6 my-2 md:my-0">
                    <a href="user_feedback.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-300 no-underline hover:text-white-alt border-b-2 border-gray-600 hover:border-gray-400">
                        <i class="fa fa-envelope fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Messages</span>
                    </a>
                </li>
                <li class="mr-6 my-2 md:my-0">
                    <a href="analytic.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-300 no-underline hover:text-white-alt border-b-2 border-gray-600 hover:border-gray-400">
                        <i class="fas fa-chart-area fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Analytics</span>
                    </a>
                </li>
            </ul>

          
            </div>
        </div>
    </div>
</nav>

<!-- Notification Modals -->
<!-- Modified Notification -->
<?php if ($message): ?>
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
     class="fixed right-5 top-5 z-50 transition-transform duration-300 ease-out">
    <div class="flex items-center p-4 w-full max-w-xs rounded-lg shadow-md 
                <?= $message_type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' ?>">
        <div class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 
                    <?= $message_type === 'success' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100' ?> 
                    rounded-lg">
            <i class="mdi <?= $message_type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle' ?> text-lg"></i>
        </div>
        <div class="ml-3 text-sm font-normal"><?= htmlspecialchars($message) ?></div>
    </div>
</div>
<?php endif; ?>
    <div class="container w-full mx-auto pt-20">
        <!-- College Registration Requests -->
      
<div class="container w-full mx-auto pt-20 px-4">
    <div class="mb-8 bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="mdi mdi-school-outline mr-2 text-blue-600"></i>
                College Registration Requests
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">College Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Mode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($row = $collegeResult->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['col_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?= $row['col_mode'] === 'online' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                <?= htmlspecialchars($row['col_mode']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex items-center space-x-4">
                                <a href="?action=approve&type=college&id=<?= $row['col_id'] ?>" 
                                   class="text-green-600 hover:text-green-900 tooltip"
                                   data-tooltip="Approve">
                                    <i class="mdi mdi-check-circle-outline text-xl"></i>
                                </a>
                                <a href="?action=reject&type=college&id=<?= $row['col_id'] ?>" 
                                   class="text-red-600 hover:text-red-900 tooltip"
                                   data-tooltip="Reject">
                                    <i class="mdi mdi-close-circle-outline text-xl"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- YouTube Link Approval -->
    <!-- Enhanced YouTube Approval Section -->
    <div class="mb-8 bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="mdi mdi-youtube mr-2 text-red-600"></i>
                YouTube Link Approvals
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Link ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Preview</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($row = $youtubeResult->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['id']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['description']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="relative group">
                                <?= processLink($row['links']) ?>
                                <div class="absolute inset-0 bg-black bg-opacity-50 hidden group-hover:flex items-center justify-center rounded-md transition-opacity">
                                    <a href="<?= htmlspecialchars($row['links']) ?>" target="_blank" 
                                       class="text-white hover:text-gray-200">
                                        <i class="mdi mdi-open-in-new text-2xl"></i>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-4">
                                <a href="?action=approve&type=yt&id=<?= $row['id'] ?>" 
                                   class="text-green-600 hover:text-green-900 tooltip"
                                   data-tooltip="Approve">
                                    <i class="mdi mdi-check-circle-outline text-xl"></i>
                                </a>
                                <a href="?action=reject&type=yt&id=<?= $row['id'] ?>" 
                                   class="text-red-600 hover:text-red-900 tooltip"
                                   data-tooltip="Reject">
                                    <i class="mdi mdi-close-circle-outline text-xl"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

 <!-- Enhanced Edit College Form -->
<?php if (isset($_GET['edit_id'])): ?>
    <div class="mb-8 bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="mdi mdi-pencil-outline mr-2 text-purple-600"></i>
                Edit College Details
            </h3>
        </div>
        <div class="p-6">
            <form method="POST" action="admin_task.php" class="space-y-6">
                <input type="hidden" name="col_id" value="<?= htmlspecialchars($college['col_id'] ?? '') ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">College Name</label>
                        <input type="text" name="col_name" value="<?= htmlspecialchars($college['col_name'] ?? '') ?>"
                               class="mt-1 block w-full rounded-md border border-black shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">College Email</label>
                        <input type="text" name="col_email" value="<?= htmlspecialchars($college['col_email'] ?? '') ?>"
                               class="mt-1 block w-full rounded-md border border-black shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">College Address</label>
                        <input type="text" name="col_address" value="<?= htmlspecialchars($college['col_address'] ?? '') ?>"
                               class="mt-1 block w-full rounded-md border border-black shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">College Phone</label>
                        <input type="text" name="col_phone" value="<?= htmlspecialchars($college['col_phone'] ?? '') ?>"
                               class="mt-1 block w-full rounded-md border border-black shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mode</label>
                        <select name="col_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="online" <?= ($college['col_mode'] ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
                            <option value="offline" <?= ($college['col_mode'] ?? '') === 'offline' ? 'selected' : '' ?>>Offline</option>
                        </select>
                    </div>

                </div>

                <div class="flex justify-end space-x-3">
                    <a href="admin_task.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700" name="edit_college">
                        <i class="mdi mdi-content-save-outline mr-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
    <!-- Approved Colleges Section -->
    <div class="mb-8 bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <i class="mdi mdi-checkbox-marked-circle-outline mr-2 text-green-600"></i>
                Approved Colleges
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
            <thead>
                        <tr class="bg-gray-100 text-black">
                            <th class="py-3 px-4 text-left">College Name</th>
                            <th class="py-3 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT col_id, col_name, col_mode FROM college WHERE status = 'approved'");
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr class='border-b border-gray-300'>
                            <td class='py-3 px-4'><?= htmlspecialchars($row['col_name']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-4">
                        <a href="?action=delete&type=college&id=<?= $row['col_id'] ?>" 
                           class="text-red-600 hover:text-red-900 tooltip"
                           data-tooltip="Delete College"
                           onclick="return confirm('This will permanently delete the college and all its data. Continue?')">
                            <i class="mdi mdi-trash-can-outline text-xl"></i>
                        </a>
                        <a href="?edit_id=<?= $row['col_id'] ?>" 
                           class="text-blue-600 hover:text-blue-900 tooltip"
                           data-tooltip="Edit College">
                            <i class="mdi mdi-pencil-outline text-xl"></i>
                        </a>
                    </div>
                </td>
                <?php endwhile; ?>
                        </tbody>
            </table>
        </div>
    </div>
</div>
</div>

<!-- tooltip script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tooltip').forEach(el => {
        el.addEventListener('mouseover', function(e) {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 px-2 py-1 text-xs text-white bg-gray-800 rounded-md shadow-lg';
            tooltip.textContent = tooltipText;
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = `${rect.top - 25}px`;
            tooltip.style.left = `${rect.left + rect.width/2}px`;
            tooltip.style.transform = 'translateX(-50%)';
            
            this.appendChild(tooltip);
            
            this.addEventListener('mouseleave', () => tooltip.remove());
        });
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>