<?php
session_start();
if (!isset($_SESSION['t_id'])) {
    header('Location: ./teacher/tech_log.html');
    exit();
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "justclick";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$collegeName = $_SESSION['college_name'];
$teacherId = $_SESSION['t_id'];

// Fetch documents from the database using prepared statements
$sql = "SELECT * FROM documents WHERE col_name = ? and t_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $collegeName, $teacherId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data into an array
$documents = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Display</title>
    <link rel="stylesheet" href="./css/css.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .notice-button {
            background-color: #000;
            color: #fff;
            margin-left: 1rem;
        }
        .notice-button:hover {
            background-color: #333;
        }

        #toast {
            transition: opacity 0.3s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
<header>
    <nav>
        <div class="logo"><a href="../index.php">NoteHub</a></div>
        <ul class="nav-links">
            <li><a href="./teacher/teac_port.php">Back to My Profile</a></li>
            <li><a href="./cupload.php">Upload Content</a></li>

            <li><a href="./student/log_out.php">Logout</a></li>
        </ul>
    </nav>
</header>
<div class="container mx-auto mt-[7%]">
    <!-- Search Bar and Notice Button -->
    <div class="mb-6 flex items-center justify-center">
        <div class="bg-white shadow-md rounded-lg p-3 flex items-center max-w-4xl w-full">
            <i class="fas fa-search text-gray-500 text-xl mr-3"></i>
            <input type="text" id="search" placeholder="Search documents..." class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button class="notice-button px-4 py-2 rounded-lg shadow-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-600">
                Notice
            </button>
        </div>
    </div>

    <!-- Grid Container -->
    <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6">
        <?php
        // Pagination Logic
        $itemsPerPage = 6;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $totalItems = count($documents);
        $totalPages = ceil($totalItems / $itemsPerPage);
        $offset = ($currentPage - 1) * $itemsPerPage;
        $paginatedDocuments = array_slice($documents, $offset, $itemsPerPage);

        if (empty($documents)) {
            echo '<div class="text-center text-gray-500 mt-6">No uploads yet.</div>';
        } else {
            foreach ($paginatedDocuments as $doc) :
                $isImage = strpos($doc['type'], 'image/') === 0;
                $fileType = getFileType($doc['type']);
                $thumbnail = 'icons/' . htmlspecialchars($fileType['icon'], ENT_QUOTES, 'UTF-8');
                $uploadTime = strtotime($doc['upload_time']);
                $formattedTime = date('M d, Y H:i', $uploadTime);
        ?>
                <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-start text-left transition-transform transform hover:scale-105 hover:shadow-lg hover:bg-gray-50">
                    <!-- Image or Icon -->
                    <div class="mb-4 flex justify-center w-full">
                        <?php if ($isImage): ?>
                            <img src="<?php echo htmlspecialchars($doc['content'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-32 object-cover rounded-t-lg"/>
                        <?php else: ?>
                            <div class="w-24 h-24 flex items-center justify-center bg-gray-100 rounded-lg shadow-sm">
                                <i class="<?php echo $fileType['icon']; ?> text-4xl text-gray-700"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Title -->
                    <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8'); ?></h2>

                    <!-- Branch -->
                    <div class="text-sm text-gray-600 mb-2 flex items-center">
                        <i class="fas fa-graduation-cap mr-1"></i>
                        <span><?php echo htmlspecialchars($doc['branch'] ?? 'All Branches', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <!-- Actions and Metadata -->
                    <div class="flex flex-col items-start mt-auto border-t border-gray-200 pt-2 w-full">
                        <!-- Download Action -->
                        <a href="<?php echo htmlspecialchars($doc['content'], ENT_QUOTES, 'UTF-8'); ?>" class="text-blue-600 hover:text-blue-700 flex items-center mb-2" download>
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M4 18v2a2 2 0 002 2h12a2 2 0 002-2v-2"></path>
                            </svg>
                            Download
                        </a>
                        <!-- Delete Action -->
                        <button class="text-red-600 hover:text-red-700 flex items-center delete-button mt-2" data-doc-id="<?php echo $doc['id']; ?>">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                        <!-- Upload Time -->
                        <p class="text-sm text-gray-500 mt-2">Date: <?php echo $formattedTime; ?></p>
                    </div>
                </div>
        <?php
            endforeach;
        }
        ?>
    </div>

    <!-- Pagination Controls -->
    <div class="flex justify-center gap-2 mt-6">
        <?php if ($currentPage > 1) : ?>
            <a href="?page=<?= $currentPage - 1 ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700">Previous</a>
        <?php endif; ?>

        <?php if ($currentPage < $totalPages) : ?>
            <a href="?page=<?= $currentPage + 1 ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700">Next</a>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-sm w-full">
        <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
        <p class="mb-4">Are you sure you want to delete this document? This action cannot be undone.</p>
        <div class="flex justify-end">
            <button id="cancelDelete" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 mr-2">Cancel</button>
            <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed top-4 right-4 p-4 rounded-lg text-white"></div>

<script>
    // Search functionality
    document.getElementById('search').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const cards = document.querySelectorAll('#cards-container > div');
        let hasResults = false;

        cards.forEach(card => {
            const text = card.querySelector('h2')?.textContent.toLowerCase();
            if (text && text.includes(query)) {
                card.style.display = 'flex';
                hasResults = true;
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Toast notification handling
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    function showToast(message, isSuccess) {
        const toast = document.getElementById('toast');
        toast.classList.remove('hidden', 'bg-red-500', 'bg-green-500');
        toast.classList.add(isSuccess ? 'bg-green-500' : 'bg-red-500');
        toast.textContent = message;

        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    if (status === 'deleted') {
        showToast('Document deleted successfully!', true);
        history.replaceState({}, document.title, window.location.pathname);
    } else if (status === 'error') {
        showToast('Error deleting document!', false);
        history.replaceState({}, document.title, window.location.pathname);
    }

    // Delete Confirmation Modal
    let docIdToDelete = null;

    function openDeleteModal(docId) {
        docIdToDelete = docId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        docIdToDelete = null;
        document.getElementById('deleteModal').classList.add('hidden');
    }

    document.getElementById('cancelDelete').addEventListener('click', closeDeleteModal);

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (docIdToDelete) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete_document.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'doc_id';
            input.value = docIdToDelete;

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        closeDeleteModal();
    });

    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function() {
            const docId = this.getAttribute('data-doc-id');
            openDeleteModal(docId);
        });
    });
</script>

</body>
</html>

<?php
// Function to determine the file type and return an array with the icon and name
function getFileType($mimeType) {
    $fileTypes = array(
        'application/pdf' => array('icon' => 'fas fa-file-pdf', 'name' => 'PDF'),
        'application/msword' => array('icon' => 'fas fa-file-word', 'name' => 'Word Document'),
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => array('icon' => 'fas fa-file-word', 'name' => 'Word Document'),
        'application/vnd.ms-excel' => array('icon' => 'fas fa-file-excel', 'name' => 'Excel Spreadsheet'),
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => array('icon' => 'fas fa-file-excel', 'name' => 'Excel Spreadsheet'),
        'application/vnd.ms-powerpoint' => array('icon' => 'fas fa-file-powerpoint', 'name' => 'PowerPoint Presentation'),
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => array('icon' => 'fas fa-file-powerpoint', 'name' => 'PowerPoint Presentation'),
        'image/jpeg' => array('icon' => 'fas fa-file-image', 'name' => 'JPEG Image'),
        'image/png' => array('icon' => 'fas fa-file-image', 'name' => 'PNG Image'),
        'text/plain' => array('icon' => 'fas fa-file-alt', 'name' => 'Text File'),
        'application/zip' => array('icon' => 'fas fa-file-archive', 'name' => 'Zip Archive'),
        'application/rar' => array('icon' => 'fas fa-file-archive', 'name' => 'RAR Archive'),
        'default' => array('icon' => 'fas fa-file', 'name' => 'Unknown File Type')
    );

    return array_key_exists($mimeType, $fileTypes) ? $fileTypes[$mimeType] : $fileTypes['default'];
}
?>
