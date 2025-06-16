<?php
session_start();
if (!isset($_SESSION['user'])) {
    
    header('Location: ./student/login.php');


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

$user = $_SESSION['user'];
$collegeName = $user['collegeName'];
$branch = $user['branch'];

// Check if the user wants to view all branches
$viewAllBranches = isset($_GET['all_branches']) && $_GET['all_branches'] == 1;

$sql = "SELECT d.*, CONCAT(t.t_fname, ' ', t.t_lname) as teacher_name 
        FROM documents d 
        LEFT JOIN `$collegeName` t ON d.t_id = t.t_id 
        WHERE d.col_name = '$collegeName'";
        
if (!$viewAllBranches) {
    $sql .= " AND d.branch = '$branch'";
}
$result = $conn->query($sql);

// Fetch data into an array
$documents = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
}
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
</head>
<body class="bg-gray-100 p-6">
<header>
    <nav>
        <div class="logo"><a href="../index.php">NoteHub</a></div>
        <ul class="nav-links">
            <li><a href="./home.php">Home</a></li>
            <li><a href="./student/stud_prof.php">My Profile</a></li>
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
            <button class="notice-button px-4 py-2 rounded-lg bg-black text-white shadow-md hover:bg-red-600 hover:text-black focus:outline-none focus:ring-2 focus:ring-gray-600">
                Search
            </button>
        </div>
    </div>

    <!-- Branch Toggle -->
    <div class="mb-6 flex items-center justify-center">
        <div class="bg-white shadow-md rounded-lg p-3 flex items-center justify-between max-w-4xl w-full">
            <span class="text-gray-700 font-medium">Currently viewing: <?php echo $viewAllBranches ? 'All Branches' : 'Your Branch (' . htmlspecialchars($branch) . ')'; ?></span>
            <a href="?<?php echo $viewAllBranches ? '' : 'all_branches=1'; ?>" 
               class="px-4 py-2 rounded-lg <?php echo $viewAllBranches ? 'bg-gray-200 text-gray-700' : 'bg-blue-500 text-white'; ?> shadow-md hover:opacity-90 transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <?php echo $viewAllBranches ? 'Show Only My Branch' : 'Show All Branches'; ?>
            </a>
        </div>
    </div>

    <!-- Grid Container -->
    <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-8 px-4">
        <!-- PHP to generate cards -->
        <?php
        $isImage = false; // Default to non-image
        foreach ($documents as $doc) {
            $isImage = strpos($doc['type'], 'image/') === 0;
            $fileType = getFileType($doc['type']);
            $thumbnail = 'icons/' . htmlspecialchars($fileType['icon'], ENT_QUOTES, 'UTF-8'); // Path to icon
            $uploadTime = date('M d, Y H:i', strtotime($doc['upload_time']));
            $teacherName = !empty($doc['teacher_name']) ? htmlspecialchars($doc['teacher_name'], ENT_QUOTES, 'UTF-8') : 'Unknown Teacher';
            ?>
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center text-center transition-all duration-300 transform hover:scale-102 hover:shadow-xl hover:bg-gray-50 relative group">
                <!-- File Type Badge -->
                <div class="absolute top-4 right-4 bg-gray-100 px-3 py-1 rounded-full text-xs font-medium text-gray-600">
                    <?php echo strtoupper(pathinfo($doc['name'], PATHINFO_EXTENSION)); ?>
                </div>

                <!-- Image/Icon Container with Background -->
                <div class="w-full h-40 mb-6 flex items-center justify-center bg-gray-50 rounded-lg overflow-hidden">
                    <?php if ($isImage): ?>
                        <img src="<?php echo htmlspecialchars($doc['content'], ENT_QUOTES, 'UTF-8'); ?>" 
                             alt="<?php echo htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8'); ?>" 
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"/>
                    <?php else: ?>
                        <i class="fas <?php echo $doc['type'] === 'application/pdf' ? 'fa-file-pdf' : 
                            ($doc['type'] === 'application/msword' || $doc['type'] === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ? 'fa-file-word' : 
                            ($doc['type'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ? 'fa-file-excel' : 'fa-file-alt')); ?> 
                            text-6xl <?php echo $doc['type'] === 'application/pdf' ? 'text-red-500' : 
                            ($doc['type'] === 'application/msword' || $doc['type'] === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ? 'text-blue-500' : 
                            ($doc['type'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ? 'text-green-500' : 'text-gray-500')); ?> 
                            transition-transform duration-300 transform group-hover:scale-110"></i>
                    <?php endif; ?>
                </div>

                <!-- Document Title -->
                <h2 class="text-xl font-bold mb-3 text-gray-800"><?php echo htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8'); ?></h2>

                <!-- Meta Information -->
                <div class="w-full space-y-2 mb-6">
                    <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                        <i class="fas fa-user text-gray-400"></i>
                        <span><?php echo $teacherName; ?></span>
                    </div>
                    <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                        <i class="fas fa-clock text-gray-400"></i>
                        <span><?php echo $uploadTime; ?></span>
                    </div>
                    <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                        <i class="fas fa-code-branch text-gray-400"></i>
                        <span><?php echo htmlspecialchars($doc['branch'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>

                <!-- Download Button -->
                <div class="w-full pt-4 border-t border-gray-100">
                    <a href="<?php echo htmlspecialchars($doc['content'], ENT_QUOTES, 'UTF-8'); ?>" 
                       class="inline-flex items-center justify-center w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-300 space-x-2 group" 
                       download>
                        <i class="fas fa-download group-hover:animate-bounce"></i>
                        <span>Download</span>
                    </a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <!-- Pagination Controls -->
    <div class="flex justify-center gap-4 mt-8">
        <button id="prev-button" class="px-6 py-2.5 rounded-lg bg-white text-blue-500 border border-blue-500 hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" disabled>&laquo; Previous</button>
        <button id="next-button" class="px-6 py-2.5 rounded-lg bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Next &raquo;</button>
    </div>
</div>

<script>
    let documents = <?php echo json_encode($documents); ?>;
    let currentPage = 1;
    const cardsPerPage = 6;

    // Function to create card HTML
    const createCard = (id, title, type, filePath) => {
        const doc = documents.find(d => d.id === id);
        const uploadTime = new Date(doc.upload_time).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: 'numeric'
        });
        const teacherName = doc.teacher_name || 'Unknown Teacher';
        const fileExtension = doc.name.split('.').pop().toUpperCase();
        
        return `
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center text-center transition-all duration-300 transform hover:scale-102 hover:shadow-xl hover:bg-gray-50 relative group">
            <!-- File Type Badge -->
            <div class="absolute top-4 right-4 bg-gray-100 px-3 py-1 rounded-full text-xs font-medium text-gray-600">
                ${fileExtension}
            </div>

            <!-- Image/Icon Container with Background -->
            <div class="w-full h-40 mb-6 flex items-center justify-center bg-gray-50 rounded-lg overflow-hidden">
                ${type.startsWith('image') ? 
                    `<img src="${filePath}" alt="${title}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"/>` 
                    : 
                    `<i class="fas ${type === 'application/pdf' ? 'fa-file-pdf' : 
                        type === 'application/msword' || type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ? 'fa-file-word' : 
                        type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ? 'fa-file-excel' : 'fa-file-alt'} 
                        text-6xl ${type === 'application/pdf' ? 'text-red-500' : 
                        type === 'application/msword' || type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ? 'text-blue-500' : 
                        type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ? 'text-green-500' : 'text-gray-500'} 
                        transition-transform duration-300 transform group-hover:scale-110"></i>`
                }
            </div>

            <!-- Document Title -->
            <h2 class="text-xl font-bold mb-3 text-gray-800">${title}</h2>

            <!-- Meta Information -->
            <div class="w-full space-y-2 mb-6">
                <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                    <i class="fas fa-user text-gray-400"></i>
                    <span>${teacherName}</span>
                </div>
                <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                    <i class="fas fa-clock text-gray-400"></i>
                    <span>${uploadTime}</span>
                </div>
                <div class="flex items-center justify-center space-x-2 text-sm text-gray-600">
                    <i class="fas fa-code-branch text-gray-400"></i>
                    <span>${doc.branch}</span>
                </div>
            </div>

            <!-- Download Button -->
            <div class="w-full pt-4 border-t border-gray-100">
                <a href="${filePath}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-300 space-x-2 group" download>
                    <i class="fas fa-download group-hover:animate-bounce"></i>
                    <span>Download</span>
                </a>
            </div>
        </div>
    `;
    };

    // Function to generate and display cards
    const generateCards = () => {
        const cardsContainer = document.getElementById('cards-container');
        cardsContainer.innerHTML = '';

        const start = (currentPage - 1) * cardsPerPage;
        const end = start + cardsPerPage;

        for (let i = start; i < end && i < documents.length; i++) {
            const doc = documents[i];
            cardsContainer.innerHTML += createCard(
                doc.id,
                doc.title,
                doc.type,
                doc.content
            );
        }
    };

    // Update pagination button states
    const updatePaginationButtons = () => {
        document.getElementById('prev-button').disabled = currentPage === 1;
        document.getElementById('next-button').disabled = currentPage * cardsPerPage >= documents.length;
    };

    // Event listeners for pagination buttons
    document.getElementById('prev-button').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            generateCards();
            updatePaginationButtons();
        }
    });

    document.getElementById('next-button').addEventListener('click', () => {
        if (currentPage * cardsPerPage < documents.length) {
            currentPage++;
            generateCards();
            updatePaginationButtons();
        }
    });

    // Initial load of documents
    document.addEventListener('DOMContentLoaded', () => {
        generateCards();
        updatePaginationButtons();
    });

    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        const cardsContainer = document.getElementById('cards-container');
        
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase();
            
            // Loop through all cards and hide/show based on search term
            Array.from(cardsContainer.children).forEach(card => {
                const title = card.querySelector('h2').textContent.toLowerCase();
                const teacherName = card.querySelector('.fas.fa-user').parentElement.querySelector('span').textContent.toLowerCase();
                const fileType = card.querySelector('.absolute.top-4.right-4').textContent.toLowerCase().trim();
                
                if (title.includes(searchTerm) || 
                    teacherName.includes(searchTerm) ||
                    fileType.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    document.getElementById('cards-container').innerHTML = '<h1 class=" text-2xl font-bold text-gray-500 text-center ">No results found</h1>';
                    card.style.display = 'none';
                    
                }
            });
        }
        
        // Add keyup event listener for real-time search
        searchInput.addEventListener('keyup', performSearch);
        
        // Make the search button use the same functionality
        document.querySelector('.notice-button').addEventListener('click', performSearch);
    });
</script>
</body>
</html>

<?php
// Function to determine the file type and return an array with the icon and name
function getFileType($mimeType) {
    $fileTypes = array(
        'application/pdf' => array('icon' => 'pdf.png', 'name' => 'PDF'),
        'application/msword' => array('icon' => 'doc.png', 'name' => 'Word Document'),
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => array('icon' => 'docx.png', 'name' => 'Word Document'),
        'application/vnd.ms-excel' => array('icon' => 'xls.png', 'name' => 'Excel Spreadsheet'),
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => array('icon' => 'xlsx.png', 'name' => 'Excel Spreadsheet'),
        'application/vnd.ms-powerpoint' => array('icon' => 'ppt.png', 'name' => 'PowerPoint Presentation'),
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => array('icon' => 'pptx.png', 'name' => 'PowerPoint Presentation'),
        'image/jpeg' => array('icon' => 'jpg.png', 'name' => 'JPEG Image'),
        'image/png' => array('icon' => 'png.png', 'name' => 'PNG Image'),
        'text/plain' => array('icon' => 'txt.png', 'name' => 'Text File'),
        'application/zip' => array('icon' => 'zip.png', 'name' => 'Zip Archive'),
        'application/rar' => array('icon' => 'rar.png', 'name' => 'RAR Archive'),
        'default' => array('icon' => 'unknown.png', 'name' => 'Unknown File Type')
    );

    return array_key_exists($mimeType, $fileTypes) ? $fileTypes[$mimeType] : $fileTypes['default'];
}
?>
