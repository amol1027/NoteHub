<?php
session_start();

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'justclick';

$conn = new mysqli($host, $username, $password, $database);

$errors = [];
$successMessage = '';
$oldInput = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $oldInput = [
        'collegeName' => trim($_POST['collegeName'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'college_mode' => "Public" ?? '',
    ];

    // Validate inputs
    if (empty($oldInput['collegeName'])) {
        $errors['collegeName'] = 'College name is required';
    }

    if (empty($oldInput['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($oldInput['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($_POST['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^[0-9]{10}$/', $_POST['phone'])) {
        $errors['phone'] = 'Phone number must be 10 digits';
    }

    if (empty($_POST['address'])) {
        $errors['address'] = 'Address is required';
    }

    if (empty($_POST['college_password'])) {
        $errors['password'] = 'Password is required';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $_POST['college_password'])) {
        $errors['password'] = 'Password must be at least 6 characters long and include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 symbol';
    }

    if ($_POST['college_password'] !== $_POST['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($oldInput['college_mode']) || !in_array($oldInput['college_mode'], ['Public', 'Private'])) {
        $errors['college_mode'] = 'College mode is required';
    }

    if (empty($errors)) {
        // Check if college exists
        $checkStmt = $conn->prepare("SELECT col_id FROM college WHERE col_name = ?");
        $checkStmt->bind_param("s", $oldInput['collegeName']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $errors['collegeName'] = 'College already exists';
        } else {
            // Insert college
            $insertStmt = $conn->prepare("INSERT INTO college (col_name, col_email, col_phone, col_address, col_password, col_mode, status) 
                                         VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $collegeModeValue = ($oldInput['college_mode'] === 'Public') ? 1 : 0;
            $insertStmt->bind_param("sssssi", 
                $oldInput['collegeName'],
                $oldInput['email'],
                $_POST['phone'],
                $_POST['address'],
                $_POST['college_password'],
                $collegeModeValue
            );

            if ($insertStmt->execute()) {
                $_SESSION['success'] = 'College registered successfully. Awaiting admin approval.';
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $errors['database'] = 'Registration failed: ' . $conn->error;
            }
        }
    }
}

// Display success message from session
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>College Registration Form</title>
<link rel="stylesheet" href="../css/css.css">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../css/style.css">
  <style>
    .success-banner {
      animation: slideDown 0.5s ease-out;
    }
    @keyframes slideDown {
      from { transform: translateY(-100%); }
      to { transform: translateY(0); }
    }
    
  </style>
  <script>
    function validatePassword() {
      const password = document.getElementById('college_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const passwordError = document.getElementById('password_error');
      const confirmPasswordError = document.getElementById('confirm_password_error');

      const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;

      if (!passwordRegex.test(password)) {
        passwordError.textContent = 'Password must be at least 6 characters long and include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 symbol';
        return false;
      } else {
        passwordError.textContent = '';
      }

      if (password !== confirmPassword) {
        confirmPasswordError.textContent = 'Passwords do not match';
        return false;
      } else {
        confirmPasswordError.textContent = '';
      }

      return true;
    }

    function validateForm(event) {
      if (!validatePassword()) {
        event.preventDefault();
      }
    }

    function updatePasswordCriteria() {
  const password = document.getElementById("college_password").value;
  const length = document.getElementById("length");
  const uppercase = document.getElementById("uppercase");
  const lowercase = document.getElementById("lowercase");
  const number = document.getElementById("number");
  const symbol = document.getElementById("symbol");

  // Check password criteria
  length.style.color = password.length >= 6 ? "green" : "gray";
  uppercase.style.color = /[A-Z]/.test(password) ? "green" : "gray";
  lowercase.style.color = /[a-z]/.test(password) ? "green" : "gray";
  number.style.color = /\d/.test(password) ? "green" : "gray";
  symbol.style.color = /[\W_]/.test(password) ? "green" : "gray";
}

function validatePassword() {
  const password = document.getElementById("college_password").value;
  const passwordError = document.getElementById("password_error");
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/;

  if (!passwordRegex.test(password)) {
    passwordError.textContent = "Password does not meet the requirements.";
    return false;
  } else {
    passwordError.textContent = "";
    return true;
  }
}

  </script>

<style>
  .password-instructions {
    background-color: #f8fafc;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-top: 0.5rem;
  }
  .requirement {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #ef4444;
    transition: color 0.2s;
  }
  .requirement.valid {
    color: #22c55e;
  }
  .requirement-icon {
    width: 1rem;
    height: 1rem;
  }

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
<body class="bg-gray-100">
<header>
        <nav>
            <div class="logo">NoteHub</div>
            <ul class="nav-links">
                <li class="dropdown">
                    <a href="#">Register</a>
                    <div class="dropdown-content">
                        <a href="../student/stud_register.php">Student Register</a>
                       
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
                <li><a href="#about">About Us</a></li>
            </ul>
        </nav>
    </header>
  <div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white rounded-xl shadow-lg p-8 space-y-6">
      <?php if ($successMessage): ?>
        <div class="success-banner bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 mt-[10%]">
          <?= $successMessage ?>
        </div>
      <?php endif; ?>

      <?php if (isset($errors['database'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 mt-[10%]">
          <?= $errors['database'] ?>
        </div>
      <?php endif; ?>

      <h1 class="text-3xl font-bold text-center text-gray-800 mt-10">College Registration</h1>
      
      <form method="POST" class="space-y-4" onsubmit="validateForm(event)">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">College Name</label>
          <input type="text" name="collegeName" value="<?= htmlspecialchars($oldInput['collegeName'] ?? '') ?>" 
                 class="w-full px-4 py-2 border <?= isset($errors['collegeName']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
          <?php if (isset($errors['collegeName'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= $errors['collegeName'] ?></p>
          <?php endif; ?>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>" 
                 class="w-full px-4 py-2 border <?= isset($errors['email']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
          <?php if (isset($errors['email'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= $errors['email'] ?></p>
          <?php endif; ?>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
          <input type="tel" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" 
                 class="w-full px-4 py-2 border <?= isset($errors['phone']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                 >
          <?php if (isset($errors['phone'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= $errors['phone'] ?></p>
          <?php endif; ?>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
          <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" 
                 class="w-full px-4 py-2 border <?= isset($errors['address']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
          <?php if (isset($errors['address'])): ?>
            <p class="text-red-500 text-sm mt-1"><?= $errors['address'] ?></p>
          <?php endif; ?>
        </div>

        <div hidden value="Public">
          <label class="block text-sm font-medium text-gray-700 mb-2">College Mode</label>
          <div class="flex gap-4">
            <label class="flex items-center space-x-2">
              <input type="radio" name="college_mode" value="Private" 
                     <?= ($oldInput['college_mode'] ?? '') === 'Private' ? 'checked' : '' ?> 
                     class="h-4 w-4 text-blue-600 focus:ring-blue-500">
              <span class="text-gray-700">Private</span>
            </label>
            <label class="flex items-center space-x-2">
              <input type="radio" name="college_mode" value="Public" 
                     <?= ($oldInput['college_mode'] ?? '') === 'Public' ? 'checked' : '' ?> 
                     class="h-4 w-4 text-blue-600 focus:ring-blue-500">
              <span class="text-gray-700">Public</span>
            </label>
          </div>
          <?php if (isset($errors['college_mode'])): ?>
            <p class="text-red-500 text-sm mt-1 " ><?= $errors['college_mode'] ?></p>
          <?php endif; ?>
        </div>

        <div>
  <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
  <input type="password" name="college_password" id="college_password" 
         class="w-full px-4 py-2 border <?= isset($errors['password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
         oninput="validatePasswordRealTime()">
  <div class="password-instructions">
    <div class="text-sm mb-2">Password must contain:</div>
    <div class="space-y-1">
      <div id="length" class="requirement">
        <svg class="requirement-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>At least 6 characters</span>
      </div>
      <div id="uppercase" class="requirement">
        <svg class="requirement-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>1 uppercase letter</span>
      </div>
      <div id="lowercase" class="requirement">
        <svg class="requirement-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>1 lowercase letter</span>
      </div>
      <div id="number" class="requirement">
        <svg class="requirement-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>1 number</span>
      </div>
      <div id="symbol" class="requirement">
        <svg class="requirement-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>1 special character</span>
      </div>
    </div>
  </div>
  <p id="password_error" class="text-red-500 text-sm mt-1"><?= $errors['password'] ?? '' ?></p>
</div>


        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
          <input type="password" name="confirm_password" id="confirm_password" 
                 class="w-full px-4 py-2 border <?= isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
          <p id="confirm_password_error" class="text-red-500 text-sm mt-1"><?= $errors['confirm_password'] ?? '' ?></p>
        </div>

        <button type="submit" class="w-full bg-black hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors">
          Register College
        </button>
      </form>

      <div class="text-center text-sm text-gray-600">
        Already registered? 
        <a href="col_login.php" class="text-blue-600 hover:text-blue-800 font-medium">Login here</a>
      </div>
    </div>
  </div>
  <!-- Add this JavaScript -->
<script>
function validatePasswordRealTime() {
  const password = document.getElementById('college_password').value;
  const requirements = {
    length: password.length >= 6,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /\d/.test(password),
    symbol: /[\W_]/.test(password)
  };

  // Update requirement indicators
  Object.keys(requirements).forEach(key => {
    const element = document.getElementById(key);
    element.classList.toggle('valid', requirements[key]);
  });

  // Clear existing error message while typing
  document.getElementById('password_error').textContent = '';
}

// Add input event listener to confirm password field
document.getElementById('confirm_password').addEventListener('input', function() {
  const password = document.getElementById('college_password').value;
  const confirmPassword = this.value;
  const errorElement = document.getElementById('confirm_password_error');
  
  if (password !== confirmPassword) {
    errorElement.textContent = 'Passwords do not match';
  } else {
    errorElement.textContent = '';
  }
});
</script>
</body>
</html>