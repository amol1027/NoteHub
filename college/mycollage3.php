<?php

session_start();  // Start the session to access session variables

if (isset($_SESSION['college_id'])) {
  $college_id = $_SESSION['college_id'];
  $college_name = $_SESSION['college_name'];
  $college_mode = $_SESSION['college_mode'];
} else {
  echo "You are not logged in.";
  exit;
}

$user = $_SESSION['college_name'];
$hostName = "localhost";
$userName = "root";
$password = "";
$databaseName = "justclick";

// Establish database connection
$conn = new mysqli($hostName, $userName, $password, $databaseName);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX document deletion request
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $docId = intval($_POST['id']);
    $college_name = $_SESSION['college_name'];
    
    // Get document info before deletion to remove the file
    $getDocSql = "SELECT content FROM documents WHERE id = ? AND col_name = ?";
    $getStmt = $conn->prepare($getDocSql);
    $getStmt->bind_param("is", $docId, $college_name);
    $getStmt->execute();
    $result = $getStmt->get_result();
    
    if ($result->num_rows > 0) {
        $docData = $result->fetch_assoc();
        $filePath = $docData['content'];
        
        // Delete from database
        $deleteSql = "DELETE FROM documents WHERE id = ? AND col_name = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("is", $docId, $college_name);
        
        if ($stmt->execute()) {
            // Try to delete the physical file if it exists and is within the project
            if (file_exists("../$filePath") && strpos($filePath, '../') === false) {
                @unlink("../$filePath");
            }
            
            // Return success JSON response
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Document not found']);
        exit;
    }
}

// Handle Approval/Rejection of student
if (isset($_POST['approve'])) {
  $id = $_POST['approve_id'];
  $sql = "UPDATE users SET is_approved = 1 WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $message = "User approved successfully!";
}

if (isset($_POST['reject'])) {
  $id = $_POST['reject_id'];
  $sql = "UPDATE users SET is_approved = -1 WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $message = "User rejected successfully!";
}

//get total Students
function getTotalStudents($conn)
{
  $college_name = $_SESSION['college_name'];
  $query = "SELECT COUNT(*) AS total FROM users WHERE collegeName = '$college_name'"; 
  $result = $conn->query($query);

  if (!$result) {
    die("Query failed: " . $conn->error);
  }

  $row = $result->fetch_assoc();
  return $row['total'];
}

$totalStudents = getTotalStudents($conn);

//get total teachers
function getTotalTeachers($conn)
{
  $college_name = $_SESSION['college_name'];
  $query = "SELECT COUNT(*) AS total FROM $college_name"; 
  $result = $conn->query($query);

  if (!$result) {
    die("Query failed: " . $conn->error);
  }

  $row = $result->fetch_assoc();
  return $row['total'];
}
$totalTeachers = getTotalTeachers($conn);

// delete teacher 
$message = "";
if (isset($_POST['confirmDelete'])) {
    $t_id = $_POST['t_id']; 
    // SQL query to delete the row
    $deleteSql = "DELETE FROM $college_name WHERE t_id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $t_id);

    $deleteSql2 = "DELETE FROM teachers WHERE t_id = ?";
    $stmt1 = $conn->prepare($deleteSql2);
    $stmt1->bind_param("i", $t_id);


    if ($stmt->execute() && $stmt1->execute()) {
        $message = "Record deleted successfully.";
    } else {
        $message = "Error deleting record: " . $conn->error;
    }

    $stmt->close();
}

//update Teacher

if (isset($_POST['updateTeacher'])) {
  $t_id = $_POST['t_id']; // Get the teacher ID to update
  $t_fname = $_POST['t_fname'];
  $t_lname = $_POST['t_lname'];
  $t_branch = $_POST['t_branch'];

  // SQL query to update the teacher record
  $updateSql = "UPDATE $college_name SET t_fname = ?, t_lname = ?, t_branch = ? WHERE t_id = ?";
  $stmt = $conn->prepare($updateSql);
  $stmt->bind_param("sssi", $t_fname, $t_lname, $t_branch, $t_id);

  if ($stmt->execute()) {
      $message = "Record updated successfully.";
  } else {
      $message = "Error updating record: " . $conn->error;
  }

  $stmt->close();
}

// Handle Student Update
if (isset($_POST['updateStudent'])) {
    $student_id = $_POST['student_id'];
    $firstName = $_POST['firstName'];
    $email = $_POST['email'];
    $branch = $_POST['branch'];

    $sql = "UPDATE users SET firstName = ?, email = ?, branch = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $firstName, $email, $branch,$student_id);

    if ($stmt->execute()) {
        $message = "Student updated successfully!";
    } else {
        $message = "Error updating student: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Student Delete
if (isset($_POST['confirmStudentDelete'])) {
    $student_id = $_POST['student_id'];

    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // First delete related records in video_likes table
        $sql_likes = "DELETE FROM video_likes WHERE user_id = ?";
        $stmt_likes = $conn->prepare($sql_likes);
        $stmt_likes->bind_param("i", $student_id);
        $stmt_likes->execute();
        $stmt_likes->close();
        
        // Then delete the student record
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();
        
        // Commit the transaction
        $conn->commit();
        $message = "Student deleted successfully!";
    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $conn->rollback();
        $message = "Error deleting student: " . $e->getMessage();
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>College Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .hidden {
      display: none;
    }
    .pagination a {
            margin: 0 2px;
            text-decoration: none;
            color: #007BFF;
        }
        .pagination a.active {
            font-weight: bold;
            color: #0056b3;
        }
  </style>
  <script>
    // Function to show only selected section
    function showSection(sectionId) {
      const sections = document.querySelectorAll('main > section');
      sections.forEach(section => {
        section.classList.add('hidden');
      });
      document.getElementById(sectionId).classList.remove('hidden');
    }

    document.addEventListener("DOMContentLoaded", function() {
    // Student Delete Modal
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('deleteStudentButton')) {
            const studentId = event.target.getAttribute('data-student-id');
            document.getElementById('modal-student-id').value = studentId;
            document.getElementById('studentDeleteModal').classList.remove('hidden');
        }
    });

    document.getElementById('cancelStudentDelete').addEventListener('click', function() {
        document.getElementById('studentDeleteModal').classList.add('hidden');
    });

    // Student Update Modal
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('updateStudentButton')) {
            const studentId = event.target.getAttribute('data-student-id');
            const firstName = event.target.getAttribute('data-firstname');
            const email = event.target.getAttribute('data-email');
            const branch = event.target.getAttribute('data-branch');

            document.getElementById('update-student-id').value = studentId;
            document.getElementById('firstName').value = firstName;
            document.getElementById('email').value = email;
            document.getElementById('branch').value = branch;

            document.getElementById('studentUpdateModal').classList.remove('hidden');
        }
    });

    document.getElementById('cancelStudentUpdate').addEventListener('click', function() {
        document.getElementById('studentUpdateModal').classList.add('hidden');
    });
});

</script>
</head>

<body class="bg-gray-100">

  <!-- Navbar -->
  <nav class="bg-gray-900 text-white px-6 py-4 flex justify-between items-center">
    <div class="text-xl font-bold"><a href="mycollage3.php">College Admin Panel</a></div>
    <div class="space-x-6">
      <a href="mycollage3.php" class="text-white hover:text-blue-400">Home</a>
      <a href="#" class="text-white hover:text-blue-400" onclick="showSection('teachers')">Teachers</a>
      <a href="#" class="text-white hover:text-blue-400" onclick="showSection('data')">Uploaded Data</a>
      <a href="../student/log_out.php" class="text-white hover:text-blue-400">Log out</a>
      </div>
  </nav>
  <!-- Main Content -->
  <main class="p-6">
    <section id="dashboard" class="mb-6">
      <h2 class="text-2xl font-bold mb-4"><?php echo $college_name; ?></h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white shadow-md p-4 rounded">
          <h3 class="text-xl font-semibold">Total Students</h3>
          <p class="text-4xl font-bold text-blue-600"><?php echo $totalStudents; ?></p>
        </div>
        <div class="bg-white shadow-md p-4 rounded">
          <h3 class="text-xl font-semibold">Total Teachers</h3>
          <p class="text-4xl font-bold text-blue-600"><?php echo $totalTeachers; ?></p>
        </div>
        <div class="bg-white shadow-md p-4 rounded">
          <h3 class="text-xl font-semibold">College Id</h3>
          <p id="status-text" class="text-4xl font-bold text-green-600"><?php echo $college_id ?></p>
        </div>
      </div>
    </section>    
<!-- student section starts from here -->
    <section id="students" class="mb-6">
  <h2 class="text-2xl font-bold mb-4">Students</h2>
  <!-- Add Approval Tabs -->
  <?php
  // 1. First calculate pending count BEFORE HTML output
  $pendingCountResult = $conn->query(
    "SELECT COUNT(*) AS total 
     FROM users 
     WHERE collegeName = '$college_name' 
     AND is_approved = 0"
  );
  $pendingCount = $pendingCountResult ? $pendingCountResult->fetch_assoc()['total'] : 0;
  
  // 2. Get active tab
  $activeTab = $_GET['tab'] ?? 'all';
  ?>

  <!-- 3. Now render tabs using calculated $pendingCount -->
  <div class="mb-4 flex gap-2">
    <a href="?tab=all" 
       class="<?= ($activeTab === 'all') ? 'bg-blue-500 text-white' : 'bg-gray-300' ?> px-4 py-2 rounded">
      All Students
    </a>
    <a href="?tab=pending" 
       class="<?= ($activeTab === 'pending') ? 'bg-blue-500 text-white' : 'bg-gray-300' ?> px-4 py-2 rounded">
      Pending Approvals (<?= $pendingCount ?>)
    </a>
  </div>

  <!-- Search Bar -->
  <div class="mb-4">
    <form method="GET" class="space-y-2">
      <input type="hidden" name="tab" value="<?= $activeTab ?>">
      
      <div class="flex flex-wrap items-center ">
        <input type="text" name="search" placeholder="Search students..." 
               class="border border-gray-300 rounded-l px-4 py-2 w-1/2" 
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600 ml-2">
          Search
        </button>
        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
          <a href="?tab=<?= $activeTab ?>" class="text-black hover:text-gray-800 ml-2 bg-red-500 px-4 py-2 rounded-r">
            Clear
          </a>
        <?php endif; ?>
      </div>
      
      <div class="flex flex-wrap gap-4 text-sm">
        <div class="flex items-center">
          <input type="radio" id="search_all" name="search_field" value="all" 
                 <?= (!isset($_GET['search_field']) || $_GET['search_field'] === 'all') ? 'checked' : '' ?>>
          <label for="search_all" class="ml-1">All Fields</label>
        </div>
        <div class="flex items-center">
          <input type="radio" id="search_name" name="search_field" value="name" 
                 <?= (isset($_GET['search_field']) && $_GET['search_field'] === 'name') ? 'checked' : '' ?>>
          <label for="search_name" class="ml-1">Name</label>
        </div>
        <div class="flex items-center">
          <input type="radio" id="search_email" name="search_field" value="email" 
                 <?= (isset($_GET['search_field']) && $_GET['search_field'] === 'email') ? 'checked' : '' ?>>
          <label for="search_email" class="ml-1">Email</label>
        </div>
        <div class="flex items-center">
          <input type="radio" id="search_branch" name="search_field" value="branch" 
                 <?= (isset($_GET['search_field']) && $_GET['search_field'] === 'branch') ? 'checked' : '' ?>>
          <label for="search_branch" class="ml-1">Branch</label>
        </div>
      </div>
    </form>
  </div>

  <?php if (!empty($message)): ?>
    <div id="messageBox" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
      <?php echo $message; ?>
    </div>
    <script>
      setTimeout(() => document.getElementById('messageBox').remove(), 5000);
    </script>
  <?php endif; ?>

  <div class="container">
    <table class="w-full bg-white shadow-md rounded">
      <thead class="bg-gray-900 text-white">
        <tr>
          <th class="py-2 px-4">Roll No.</th>
          <th class="py-2 px-4">Full Name</th>
          <th class="py-2 px-4">Gender</th>
          <th class="py-2 px-4">Email</th>
          <th class="py-2 px-4">Branch</th>
          <th class="py-2 px-4">Status</th>
          <th class="py-2 px-4">Actions</th>
        </tr>
      </thead>
      <tbody id="students-table-body">
        <?php
        $activeTab = $_GET['tab'] ?? 'all';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $studentsPerPage = 5;
        $startIndex = ($page - 1) * $studentsPerPage;

        // Base query
        $sql = "SELECT id, firstName, gender, email, branch, cur_year, is_approved 
                FROM users 
                WHERE collegeName = '$college_name'";

        // Add approval filter
        if ($activeTab === 'pending') {
          $sql .= " AND is_approved = 0";
        }
        
        // Add search filter if search term is provided
        if (isset($_GET['search']) && !empty($_GET['search'])) {
          $search = $conn->real_escape_string($_GET['search']);
          $searchField = $_GET['search_field'] ?? 'all';
          
          switch ($searchField) {
            case 'name':
              $sql .= " AND firstName LIKE '%$search%'";
              break;
            case 'email':
              $sql .= " AND email LIKE '%$search%'";
              break;
            case 'branch':
              $sql .= " AND branch LIKE '%$search%'";
              break;
            default:
              $sql .= " AND (firstName LIKE '%$search%' OR email LIKE '%$search%' OR branch LIKE '%$search%')";
              break;
          }
        }

        // Pagination
        $sql .= " ORDER BY id ASC LIMIT $startIndex, $studentsPerPage";
        $result = $conn->query($sql);

        // Total counts - must include search parameter for correct pagination
        $countSql = "SELECT COUNT(*) AS total FROM users 
                    WHERE collegeName = '$college_name'" . 
                    ($activeTab === 'pending' ? " AND is_approved = 0" : "");
                    
        // Add search to count query as well
        if (isset($_GET['search']) && !empty($_GET['search'])) {
          $search = $conn->real_escape_string($_GET['search']);
          $searchField = $_GET['search_field'] ?? 'all';
          
          switch ($searchField) {
            case 'name':
              $countSql .= " AND firstName LIKE '%$search%'";
              break;
            case 'email':
              $countSql .= " AND email LIKE '%$search%'";
              break;
            case 'branch':
              $countSql .= " AND branch LIKE '%$search%'";
              break;
            default:
              $countSql .= " AND (firstName LIKE '%$search%' OR email LIKE '%$search%' OR branch LIKE '%$search%')";
              break;
          }
        }
        
        $totalResult = $conn->query($countSql);
        $total = $totalResult->fetch_assoc()['total'];
        $totalPages = ceil($total / $studentsPerPage);

        // Pending count for badge
        $pendingCount = $conn->query("SELECT COUNT(*) AS total FROM users 
                                     WHERE collegeName = '$college_name' AND is_approved = 1")
                           ->fetch_assoc()['total'];

        // Display search results message after $total is defined
        if(isset($_GET['search']) && !empty($_GET['search'])): ?>
          <div class="mb-4 text-sm text-gray-600">
            <span class="font-medium">Search results for: </span>
            "<?= htmlspecialchars($_GET['search']) ?>"
            <?php if(!isset($total) || $total == 0): ?>
              <span class="text-red-500"> - No results found</span>
            <?php else: ?>
              <span class="text-green-500"> - <?= isset($total) ? $total : 0 ?> result<?= isset($total) && $total > 1 ? 's' : '' ?> found</span>
            <?php endif; ?>
          </div>
        <?php endif;

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td class='py-2 px-4'>" . $row['id'] . "</td>";
            echo "<td class='py-2 px-4'>" . $row['firstName'] . "</td>";
            echo "<td class='py-2 px-4'>" . $row['gender'] . "</td>";
            echo "<td class='py-2 px-4'>" . $row['email'] . "</td>";
            echo "<td class='py-2 px-4'>" . $row['branch'] . "</td>";
            echo "<td class='py-2 px-4 font-semibold ";
            if ($row['is_approved'] == 1) {
                echo "text-green-600'>Active";
            } elseif ($row['is_approved'] == 0) {
                echo "text-yellow-600'>Pending";
            } elseif ($row['is_approved'] == -1) {
                echo "text-red-600'>Rejected";
            }
            echo "</td>";

            echo "<td class='py-2 px-4'>";

            if ($activeTab === 'pending') {
              // Approve/Reject buttons
              echo "<form method='POST' class='inline'>
                      <input type='hidden' name='approve_id' value='{$row['id']}'>
                      <button type='submit' name='approve' class='text-green-500 hover:text-green-600 mr-2'>Approve</button>
                    </form>
                    <form method='POST' class='inline'>
                      <input type='hidden' name='reject_id' value='{$row['id']}'>
                      <button type='submit' name='reject' class='text-red-500 hover:text-red-600'>Reject</button>
                    </form>";
            } else 
            {
              // Original Update/Delete buttons
              echo "<button class='text-yellow-500 hover:text-yellow-600 mr-2 updateStudentButton' 
                      data-student-id='{$row['id']}' 
                      data-firstname='{$row['firstName']}' 
                      data-email='{$row['email']}' 
                      data-branch='{$row['branch']}' 
                      ,.>Update</button>
                    <button class='text-red-500 hover:text-red-600 deleteStudentButton' 
                      data-student-id='{$row['id']}'>Delete</button>";
          }
            echo "</td></tr>";
          }
        } else {
          echo "<tr><td colspan='7' class='py-2 px-4 text-center'>No data available</td></tr>";
        }
        ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <div class="flex justify-center mt-4">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php 
          // Build pagination URL preserving search parameters
          $paginationUrl = "?page=" . $i . "&tab=" . $activeTab;
          if(isset($_GET['search']) && !empty($_GET['search'])) {
            $paginationUrl .= "&search=" . urlencode($_GET['search']);
            if(isset($_GET['search_field'])) {
              $paginationUrl .= "&search_field=" . urlencode($_GET['search_field']);
            }
          }
        ?>
        <a href="<?php echo $paginationUrl; ?>"
          class="py-1 px-3 mx-1 <?php echo $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-300'; ?> rounded">
          <?php echo $i; ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
  
<!-- Delete Modal for Students -->
<div id="studentDeleteModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
        <p>Are you sure you want to delete this student?</p>
        <form method="POST" class="mt-4">
            <input type="hidden" name="student_id" id="modal-student-id">
            <div class="flex justify-end">
                <button type="button" id="cancelStudentDelete" class="mr-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" name="confirmStudentDelete" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Modal for Students -->
<div id="studentUpdateModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-bold mb-4">Update Student</h2>
        <form method="POST">
            <input type="hidden" name="student_id" id="update-student-id">
            <div class="mb-4">
                <label for="firstName" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="firstName" name="firstName" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="branch" class="block text-sm font-medium text-gray-700">Branch</label>
                <input type="text" id="branch" name="branch" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="flex justify-end">
                <button type="button" id="cancelStudentUpdate" class="mr-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" name="updateStudent" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
</section>

<!-- teacher section starts from here-->
    <section id="teachers" class="mb-6">
      <h2 class="text-2xl font-bold mb-4">Teachers</h2>
      <div class="mb-4">
        <button class="text-blue-700 hover:text-white border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-blue-500 dark:text-blue-500 dark:hover:text-white dark:hover:bg-blue-500 dark:focus:ring-blue-800"><a href="creat_teach.php">Add New Teacher</a></button>
      </div>
  <div class="container">

<!-- Display Message -->
<?php if (!empty($message)): ?>
    <div id="messageBox" class="fixed top-0 left-0 w-full bg-green-500 text-white py-2 px-4 text-center shadow-lg z-50">
        <?php echo $message; ?>
    </div>
    <script>
        setTimeout(() => {
            const messageBox = document.getElementById('messageBox');
            if (messageBox) messageBox.remove();
        }, 5000);
    </script>
<?php endif; ?>

<!-- Confirmation Modal for Delete -->
<div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-bold mb-4">Confirm Delete</h2>
        <p>Are you sure you want to delete this record?</p>
        <form method="POST" class="mt-4">
            <input type="hidden" name="t_id" id="modal-t-id">
            <div class="flex justify-end">
                <button type="button" id="cancelButton" class="mr-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" name="confirmDelete" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-bold mb-4">Update Teacher</h2>
        <form method="POST">
            <input type="hidden" name="t_id" id="update-t-id">
            <div class="mb-4">
                <label for="t_fname" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" id="t_fname" name="t_fname" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="t_lname" class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" id="t_lname" name="t_lname" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="t_branch" class="block text-sm font-medium text-gray-700">Branch</label>
                <input type="text" id="t_branch" name="t_branch" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="flex justify-end">
                <button type="button" id="cancelUpdateButton" class="mr-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" name="updateTeacher" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Table Display -->
<table class="w-[100%] bg-white shadow-md rounded mb-4 overflow-hidden table-fixed text-sm text-white border border-gray-200 dark:border-gray-700 dark:bg-gray-800">
    <thead class="bg-gray-900 text-white">
        <tr class="text-left">
            <th class="py-2 px-4">ID</th>
            <th class="py-2 px-4">Name</th>
            <th class="py-2 px-4">Branch</th>
            <th class="py-2 px-4">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // SQL query to fetch teacher data
        $sql = "SELECT t_id, t_fname, t_lname, t_branch FROM $college_name";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Output data for each row
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td class='py-2 px-4'>" . $row['t_id'] . "</td>";
                echo "<td class='py-2 px-4'>" . $row['t_fname'] . " " . $row['t_lname'] . "</td>";
                echo "<td class='py-2 px-4'>" . $row['t_branch'] . "</td>";
                echo "<td class='py-2 px-4'>
                        <button type='button' class='text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-green-500 dark:text-green-500 dark:hover:text-white dark:hover:bg-green-600 dark:focus:ring-green-800 updateButton' 
                                data-t-id='" . $row['t_id'] . "' 
                                data-t-fname='" . $row['t_fname'] . "'
                                data-t-lname='" . $row['t_lname'] . "'
                                data-t-branch='" . $row['t_branch'] . "'>
                            Update
                        </button>
                        <button type='button' class='text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center me-2 mb-2 dark:border-red-500 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600 dark:focus:ring-red-900 deleteButton' 
                                data-t-id='" . $row['t_id'] . "'>
                            Delete
                        </button>
                      </td>";
                echo "</tr>";
            }
        } else {
            // No data available
            echo "<tr><td colspan='4' class='py-2 px-4 text-center'>No data available</td></tr>";
        }
        ?>
    </tbody>
</table>

<script>
    // Open Delete Modal
    document.querySelectorAll('.deleteButton').forEach(button => {
        button.addEventListener('click', function () {
            const tId = this.getAttribute('data-t-id');
            document.getElementById('modal-t-id').value = tId;
            document.getElementById('deleteModal').classList.remove('hidden');
        });
    });

    // Close Delete Modal
    document.getElementById('cancelButton').addEventListener('click', function () {
        document.getElementById('deleteModal').classList.add('hidden');
    });

    // Open Update Modal
    document.querySelectorAll('.updateButton').forEach(button => {
        button.addEventListener('click', function () {
            const tId = this.getAttribute('data-t-id');
            const tFname = this.getAttribute('data-t-fname');
            const tLname = this.getAttribute('data-t-lname');
            const tBranch = this.getAttribute('data-t-branch');

            document.getElementById('update-t-id').value = tId;
            document.getElementById('t_fname').value = tFname;
            document.getElementById('t_lname').value = tLname;
            document.getElementById('t_branch').value = tBranch;

            document.getElementById('updateModal').classList.remove('hidden');
        });
    });

    // Close Update Modal
    document.getElementById('cancelUpdateButton').addEventListener('click', function () {
        document.getElementById('updateModal').classList.add('hidden');
    });
</script>
  </div>
</section>
    </section>
    <!-- documents section -->
    <?php

    
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']); // Ensure the ID is an integer
    $college_name = isset($_SESSION['college_name']) ? $_SESSION['college_name'] : 'hncc';
    $college_name = $conn->real_escape_string($college_name);

    // SQL query to delete the document
    $sql = "DELETE FROM `documents` WHERE `id` = ? AND `col_name` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id, $college_name);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit(); // Stop further execution
}

// Ensure college_name is set and safe
$college_name = isset($_SESSION['college_name']) ? $_SESSION['college_name'] : 'hncc'; // Default to 'hncc' if not set
$college_name = $conn->real_escape_string($college_name);

// SQL query to fetch document details
$sql = "SELECT `id`, `name`, `t_id`, `col_name`, `title`, `type`, `size`, `content`, `upload_time` FROM `documents` WHERE col_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $college_name);
$stmt->execute();
$result = $stmt->get_result();

// SQL query to fetch teacher name
$sql2 = "SELECT `t_fname`, `t_lname` FROM `$college_name` WHERE t_id = ?";
$stmt2 = $conn->prepare($sql2);

// Check if there are any results
$documents = [];
if ($result->num_rows > 0) {
    // Loop through each row in the result set
    while ($row = $result->fetch_assoc()) {
        // Bind the t_id for the second query
        $stmt2->bind_param("i", $row['t_id']);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $teacher = $result2->fetch_assoc();
        $teacherName = $teacher ? htmlspecialchars($teacher['t_fname']) . " " . htmlspecialchars($teacher['t_lname']) : 'Unknown';

        // Escape output to prevent XSS attacks
        $id = htmlspecialchars($row["id"]);
        $title = htmlspecialchars($row["title"]);
        $uploadedBy = htmlspecialchars($row["name"]);
        $uploadTime = htmlspecialchars($row["upload_time"]);
        $size = htmlspecialchars($row["size"]);
        $contentPath = htmlspecialchars($row["content"]);
        $newContent = "../" . $contentPath;

        // Store document data in an array
        $documents[] = [
            'id' => $id,
            'title' => $title,
            'uploadedBy' => $uploadedBy,
            'uploadTime' => $uploadTime,
            'size' => $size,
            'teacherName' => $teacherName,
            'newContent' => $newContent
        ];
    }
}

// Close the database connection
$stmt->close();
$stmt2->close();
$conn->close();
?>

    <section id="data" class="mb-6">
        <h2 class="text-2xl font-bold mb-4">Uploaded Data</h2>
        <div id="documents-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"></div>
        <div class="pagination" id="pagination-controls"></div>
    </section>

    <script>
        const documents = <?php echo json_encode($documents); ?>;
        const resultsPerPage = 9;
        let currentPage = 1;

        function displayDocuments() {
            const documentsContainer = document.getElementById('documents-container');
            const paginationControls = document.getElementById('pagination-controls');
            documentsContainer.innerHTML = '';
            paginationControls.innerHTML = '';

            const start = (currentPage - 1) * resultsPerPage;
            const end = start + resultsPerPage;
            const paginatedDocuments = documents.slice(start, end);

            paginatedDocuments.forEach(doc => {
                const docElement = `
                    <div class="bg-white shadow-md p-4 rounded">
                        <h3 class="text-lg font-bold">${doc.title}</h3>
                        <p class="text-sm text-gray-500">Uploaded on: ${doc.uploadTime}</p>
                        <p class="text-sm text-gray-500">Size: ${doc.size} bytes</p>
                        <p class="text-sm text-gray-500">Uploaded by: ${doc.teacherName}</p>
                        <div class="mt-4 flex space-x-2">
                            <button class="bg-blue-500 text-white px-4 py-2 rounded" onclick="viewDocument('${doc.newContent}')">View</button>
                            <button class="bg-red-500 text-white px-4 py-2 rounded" onclick="deleteDocument(${doc.id})">Delete</button>
                        </div>
                    </div>
                `;
                documentsContainer.innerHTML += docElement;
            });

            const totalPages = Math.ceil(documents.length / resultsPerPage);
            if (totalPages > 1) {
                if (currentPage > 1) {
                    paginationControls.innerHTML += `<a href="#" onclick="goToPage(${currentPage - 1})">Previous</a>`;
                }
                for (let i = 1; i <= totalPages; i++) {
                    paginationControls.innerHTML += `<a href="#" class="${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</a>`;
                }
                if (currentPage < totalPages) {
                    paginationControls.innerHTML += `<a href="#" onclick="goToPage(${currentPage + 1})">Next</a>`;
                }
            }
        }

        function goToPage(page) {
            currentPage = page;
            displayDocuments();
        }

        function viewDocument(newContent) {
            window.open(newContent, '_blank');
        }

        function deleteDocument(id) {
            if (confirm('Are you sure you want to delete this document?')) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', window.location.href, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    // Remove the document from the array
                                    const index = documents.findIndex(doc => doc.id === id);
                                    if (index !== -1) {
                                        documents.splice(index, 1);
                                    }
                                    // Refresh the display
                                    displayDocuments();
                                    alert('Document deleted successfully!');
                                } else {
                                    alert('Error deleting document: ' + (response.error || 'Unknown error'));
                                }
                            } catch (e) {
                                alert('Error parsing response: ' + e.message);
                            }
                        } else {
                            alert('Error: Unable to delete document.');
                        }
                    }
                };
                xhr.send('action=delete&id=' + id);
            }
        }

        // Initial display
        displayDocuments();
    </script>
  </main>
</body>

</html>