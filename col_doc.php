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

// Fetch documents from the database using prepared statements
$sql = "SELECT * FROM documents WHERE col_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $collegeName);
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
    </style>
</head>
<body class="bg-gray-100 p-6">
<header>
    <nav>
        <div class="logo"><a href="../index.php">NotesHub</a></div>
        <ul class="nav-links">
            <li><a href="./home.php">Home</a></li>
            <li><a href="./teac_port.php">My Profile</a></li>
            <li><a href="./user_login/log_out.php">Logout</a></li>
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
        <!-- PHP to generate cards -->
        <?php
        $isImage = false; // Default to non-image
        
foreach ($documents as $doc) {
    $isImage = strpos($doc['type'], 'image/') === 0;
    $fileType = getFileType($doc['type']);
    $thumbnail = 'icons/' . htmlspecialchars($fileType['icon'], ENT_QUOTES, 'UTF-8'); // Path to icon
    ?>
    <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center text-center transition-transform transform hover:scale-105 hover:shadow-lg hover:bg-gray-100">
        <?php if ($isImage): ?>
            <img src="<?php echo htmlspecialchars($doc['content'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-32 object-cover mb-4"/>
        <?php else: ?>
            <!-- Use thumbnail image instead of Font Awesome -->
            <img src="<?php echo $thumbnail; ?>" alt="<?php echo htmlspecialchars($fileType['name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-16 h-16 mb-4"/>
        <?php endif; ?>
        <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($doc['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <div class="flex flex-col items-center mt-auto border-t border-gray-200 pt-2">
            <a href="<?php echo htmlspecialchars($doc['content'], ENT_QUOTES, 'UTF-8'); ?>" class="text-blue-600 hover:text-blue-700" download>Download</a>
        </div>
    </div>
    <?php
}
?>
    </div>

    <!-- Pagination Controls -->
    <div class="flex justify-center gap-2 mt-6">
        <button id="prev-button" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>&laquo; Previous</button>
        <button id="next-button" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Next &raquo;</button>
    </div>
</div>

<script>
    let documents = <?php echo json_encode($documents); ?>;
    let currentPage = 1;
    const cardsPerPage = 6;

    // Function to create card HTML
    const createCard = (id, title, type, filePath) => `
        <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center text-center transition-transform transform hover:scale-105 hover:shadow-lg hover:bg-gray-100">
            ${type.startsWith('image') ? 
                `<img src="${filePath}" alt="${title}" class="w-full h-32 object-cover mb-4"/>`
                : 
                `<i class="fas ${type === 'application/pdf' ? 'fa-file-pdf' : type === 'application/msword' || type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ? 'fa-file-word' : type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ? 'fa-file-excel' : 'fa-file-alt'} text-5xl ${type === 'application/pdf' ? 'text-red-600' : type === 'application/msword' || type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ? 'text-blue-600' : type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ? 'text-green-600' : 'text-gray-600'} mb-4"></i>`
            }
            <h2 class="text-xl font-semibold mb-2">${title}</h2>
            <div class="flex flex-col items-center mt-auto border-t border-gray-200 pt-2">
                <a href="${filePath}" class="text-blue-600 hover:text-blue-700" download>Download</a>
            </div>
        </div>
    `;

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
    document.getElementById('search').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const cards = document.querySelectorAll('#cards-container > div');
        
        cards.forEach(card => {
            const text = card.querySelector('h2').textContent.toLowerCase();
            if (text.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
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
