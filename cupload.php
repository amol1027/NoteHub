<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in (i.e., session variables are set)
if (!isset($_SESSION['college_id']) || !isset($_SESSION['t_id'])) {
    // If not logged in, redirect to login page
    header("Location: ./test/teach_log.html");
    exit();
}

// Check if there's a message to display
$message = isset($_SESSION['message']) ? $_SESSION['message'] : null;

// Clear the message after displaying it
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document</title>
    <link rel="stylesheet" href="./css/css.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f2f2;
        }
        .upload-form {
            width: 50%;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .upload-form label {
            font-weight: bold;
        }
        .upload-form input[type="file"] {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #f2f2f2;
        }
        .upload-form button[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #000000;
            color: #fff;
            cursor: pointer;
        }
        .upload-form button[type="submit"]:hover {
            background-color: #494848;
        }
        .show-files-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
        }
        .show-files-btn:hover {
            background-color: #3e8e41;
        }
    </style>
</head>

<body>
    </header>
    <body>
    <header>
        <nav>
            <div class="logo">NoteHub</div>
            <ul class="nav-links ">
                <li><a href="./teacher/teac_port.php">Back to My Profile</a></li>
                <li><a href="./tech_doc.php">My Uploads</a></li>

                <li><a href="./student/log_out.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <div class="upload-form p-4 shadow-md rounded-md bg-white mt-[10%]">
        <h2 class="text-center mb-4 text-3xl">Upload Document</h2>

        <!-- Display the message if available -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'danger'; ?>" role="alert">
                <?php echo $message['content']; ?>
            </div>
        <?php endif; ?>

        <form action="c_proc.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Document Title:</label>
                <input type="text" name="title" id="title" required class="form-control">
            </div>
            <div class="mb-3">
                <label for="branch" class="form-label">Branch:</label>
                <select name="branch" id="branch" required class="form-control">
                    <option value="">Select Branch</option>
                    <option value="BCA">BCA</option>
                    <option value="BBA">BBA</option>
                    <option value="MBA">MBA</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">Choose a document:</label>
                <input type="file" name="file" id="file" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</body>

</body>
</html>
