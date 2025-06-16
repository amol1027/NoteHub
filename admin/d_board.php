<?php
$hostName = "localhost";
$userName = "root";
$password = "";
$databaseName = "justclick";

$conn = new mysqli($hostName, $userName, $password, $databaseName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generic function to get count from any table
function get_count($conn, $table) {
    $query = "SELECT COUNT(*) AS total FROM `$table`";
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $data = $result->fetch_assoc();
    return $data['total'];
}

// Get counts using generic function
$total_documents = get_count($conn, 'documents');
$total_links = get_count($conn, 'homedata');
$totalStudents = get_count($conn, 'users');
$totalTeachers = get_count($conn, 'teachers');
$totalColleges = get_count($conn, 'college');

// Function to get all users
function getAllUsers($conn) {
    $query = "SELECT firstName, lastName, username, collegeName, branch, cur_year, timeStamp FROM users";
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get all colleges
function getAllColleges($conn) {
    $query = "SELECT col_name, col_email, col_phone, col_address, col_mode, status, registered_date FROM college";
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get all teachers
function getAllTeachers($conn) {
    $query = "SELECT t_name, col_name, t_email, t_branch, registered_at FROM teachers";
    $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

$users = getAllUsers($conn);
$colleges = getAllColleges($conn);
$teachers = getAllTeachers($conn);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>NotesHub</title>
    <meta name="description" content="description here">
    <meta name="keywords" content="keywords,here">
	
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js" integrity="sha256-XF29CBwU1MWLaGEnsELogU6Y6rcc5nCkhhx89nFMIDQ=" crossorigin="anonymous"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableSelector = document.getElementById('tableSelector');
        const tables = {
            users: document.getElementById('usersTable'),
            colleges: document.getElementById('collegesTable'),
            teachers: document.getElementById('teachersTable')
        };

        // Show initial table
        tables.users.style.display = 'block';
        tables.colleges.style.display = 'block';
        tables.teachers.style.display = 'block';

        tableSelector.addEventListener('change', function() {
           
            if(this.value === 'all') {
                Object.values(tables).forEach(table => table.style.display = 'block');
                return;
            }
            Object.values(tables).forEach(table => table.style.display = 'none');
            // Show selected table
            tables[this.value].style.display = 'block';
        });
    });
</script>
    </script>

</head>
<body class="bg-slate-200 font-sans leading-normal tracking-normal">
<nav id="header" class="bg-black fixed w-full z-10 top-0 shadow">
		<div class="w-full container mx-auto flex flex-wrap items-center mt-0 pt-3 pb-3 md:pb-0">
				
			<div class="w-1/2 pl-2 md:pl-0">
				<a class="text-gray-100 text-base xl:text-xl no-underline hover:no-underline font-bold"  href="#"> 
				</a>
            </div>
			<div class="w-1/2 pr-0">
				<div class="flex relative inline-block float-right">
				
				  <div class="relative text-sm text-gray-100">
                    <!-- make it into dropdown -->
					  <button id="userButton" class="flex items-center focus:outline-none mr-3">
						 <span class="md:inline-block text-gray-100">Hi, Admin</span>
					  </button>
					  <div id="userMenu" class="bg-gray-900 rounded shadow-md mt-2 absolute mt-12 top-5 right-0 min-w-full overflow-auto z-30 ">
						  <ul class="list-reset">
							
							<li><a href="../student/log_out.php" class="px-4 py-2 block text-gray-100 hover:bg-gray-800 no-underline hover:no-underline">Logout</a></li>
						  </ul>
					  </div>
				  </div>
				</div>
			</div>
			<div class="w-full flex-grow lg:flex lg:items-center lg:w-auto hidden lg:block mt-2 lg:mt-0 bg-black z-20" id="nav-content">
				<ul class="list-reset lg:flex flex-1 items-center px-4 md:px-0">
					<li class="mr-6 my-2 md:my-0">
                        <a href="#" class="block py-1 md:py-3 pl-1 align-middle text-blue-400 no-underline hover:text-gray-100 border-b-2 border-blue-400 hover:border-blue-400">
                            <i class="fas fa-home fa-fw mr-3 text-blue-400"></i><span class="pb-1 md:pb-0 text-sm">Home</span>
                        </a>
                    </li>
					<li class="mr-6 my-2 md:my-0">
                        <a href="admin_task.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2 border-red-900  hover:border-pink-400">
                            <i class="fas fa-tasks fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Tasks</span>
                        </a>
                    </li>
                    <li class="mr-6 my-2 md:my-0">
                        <a href="user_feedback.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2 border-gray-900  hover:border-purple-400">
                            <i class="fa fa-envelope fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Messages</span>
                        </a>
                    </li>
                    <li class="mr-6 my-2 md:my-0">
                        <a href="analytic.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2 border-gray-900  hover:border-green-400">
                            <i class="fas fa-chart-area fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Analytics</span>
                        </a>
                    </li>            
				</ul>
			</div>			
		</div>
	</nav>
	<!--Container-->
	<div class="container w-full mx-auto pt-20">		
		<div class="w-full px-4 md:px-0 md:mt-8 mb-16 text-gray-800 leading-normal">			
			<!--Console Content-->	       
			<div class="flex flex-wrap">
                <div class="w-full md:w-1/2 xl:w-1/3 p-3">
                    <!--Metric Card-->
                    <div class="bg-gray-900 border border-gray-800 rounded shadow p-2">
                        <div class="flex flex-row items-center">
                            <div class="flex-shrink pr-4">
                                <div class="rounded p-3 bg-green-600"><i class="fas fa-users fa-2x fa-fw fa-inverse"></i></div>
                            </div>
                            <div class="flex-1 text-right md:text-center">
                                <h5 class="font-bold uppercase text-gray-400">Total Teachers</h5>
                                <h3 class="font-bold text-3xl text-gray-600"><?php echo $totalTeachers; ?><span class="text-green-500"><i class="fas fa-caret-up"></i></span></h3>
                            </div>
                        </div>
                    </div>
                    <!--/Metric Card-->
                </div>
                <div class="w-full md:w-1/2 xl:w-1/3 p-3">
                    <!--Metric Card-->
                    <div class="bg-gray-900 border border-gray-800 rounded shadow p-2">
                        <div class="flex flex-row items-center">
                            <div class="flex-shrink pr-4">
                                <div class="rounded p-3 bg-pink-600"><i class="fas fa-users fa-2x fa-fw fa-inverse"></i></div>
                            </div>
                            <div class="flex-1 text-right md:text-center">
                                <h5 class="font-bold uppercase text-gray-400">Total Students</h5>
                                <h3 class="font-bold text-3xl text-gray-600"><?php echo $totalStudents; ?><span class="text-pink-500"><i class="fas fa-exchange-alt"></i></span></h3>
                            </div>
                        </div>
                    </div>
                    <!--/Metric Card-->
                </div>
                <div class="w-full md:w-1/2 xl:w-1/3 p-3">
                        <!--Metric Card-->
                        <div class="bg-gray-900 border border-gray-800 rounded shadow p-2">
                        <div class="flex flex-row items-center">
                            <div class="flex-shrink pr-4">
                                <div class="rounded p-3 bg-blue-600"><i class="fas fa-video fa-2x fa-fw fa-inverse"></i></div>
                            </div>
                            <div class="flex-1 text-right md:text-center">
                                <h5 class="font-bold uppercase text-gray-400">youtube links</h5>
                                <h3 class="font-bold text-3xl text-gray-600"><?php echo $total_links;  ?></h3>
                            </div>
                        </div>
                    </div>
                    <!--/Metric Card-->                   
                </div>
                <div class="w-full md:w-1/2 xl:w-1/3 p-3">
                 <!--Metric Card-->
                 <div class="bg-gray-900 border border-gray-800 rounded shadow p-2">
                        <div class="flex flex-row items-center">
                            <div class="flex-shrink pr-4">
                                <div class="rounded p-3 bg-indigo-600"><i class="fas fa-folder fa-2x fa-fw fa-inverse"></i></div>
                            </div>
                            <div class="flex-1 text-right md:text-center">
                                <h5 class="font-bold uppercase text-gray-400">documents</h5>
                                <h3 class="font-bold text-3xl text-gray-600"><?php echo $total_documents; ?></h3>
                            </div>
                        </div>
                    </div>
                    <!--/Metric Card-->
                </div>
                <div class="w-full md:w-1/2 xl:w-1/3 p-3">
                 <!--Metric Card-->
                 <div class="bg-gray-900 border border-gray-800 rounded shadow p-2">
                        <div class="flex flex-row items-center">
                            <div class="flex-shrink pr-4">
                                <div class="rounded p-3 bg-indigo-600"><i class="fas fa-university fa-2x fa-fw fa-inverse"></i></div>
                            </div>
                            <div class="flex-1 text-right md:text-center">
                                <h5 class="font-bold uppercase text-gray-400">Colleges Regesterd</h5>
                                <h3 class="font-bold text-3xl text-gray-600"><?php echo $totalColleges; ?></h3>
                            </div>
                        </div>
                    </div>
                    <!--/Metric Card-->
                </div>
            </div>
			<!--Divider-->
			<hr class="border-b-2 border-gray-600 bg- my-8 mx-4">
        
            <select id="tableSelector" class="w-1/6 p-2 bg-gray-600 text-center text-gray-100 ml-[40%] border border-gray-800 rounded shadow">
                <option value="all">all</option>
                <option value="users">Users</option>
                <option value="colleges">Colleges</option>
                <option value="teachers">Teachers</option>
            </select>		
            <div class="flex flex-row flex-wrap flex-grow mt-2 bg-white">
            <div class="w-full  p-3">
                <!-- users -->
    <div class="p-5" id="usersTable">
        <h1 class="text-3xl text-black pb-6" >All Users</h1>
        <table class="w-full p-5 text-gray-700" >
            <thead>
                <tr class="bg-gray-100">
                    <th class="text-left py-2 px-4 text-gray-600">First Name</th>
                    <th class="text-left py-2 px-4 text-gray-600">Last Name</th>
                    <th class="text-left py-2 px-4 text-gray-600">Username</th>
                    <th class="text-left py-2 px-4 text-gray-600">College</th>
                    <th class="text-left py-2 px-4 text-gray-600">Branch</th>
                    <th class="text-left py-2 px-4 text-gray-600">Year</th>
                    <th class="text-left py-2 px-4 text-gray-600">Registration Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2 px-4"><?= htmlspecialchars($user['firstName']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['lastName']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['username']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['collegeName']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['branch']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['cur_year']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['timeStamp']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- colleges -->
<div class="w-full p-3" id="collegesTable">
        <div class="p-5">
            <h1 class="text-3xl text-black pb-6">Colleges</h1>
            <table class="w-full p-5 text-gray-700" >
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left py-2 px-4 text-gray-600">College Name</th>
                        <th class="text-left py-2 px-4 text-gray-600">Email</th>
                        <th class="text-left py-2 px-4 text-gray-600">Phone No.</th>
                        <th class="text-left py-2 px-4 text-gray-600">Address</th>
                        <th class="text-left py-2 px-4 text-gray-600">Mode</th>
                        <th class="text-left py-2 px-4 text-gray-600">Status</th>
                        <th class="text-left py-2 px-4 text-gray-600">Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($colleges as $college): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4"><?= htmlspecialchars($college['col_name']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($college['col_email']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($college['col_phone']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($college['col_address']) ?></td>
                        <td class="py-2 px-4">
                            <?= $college['col_mode'] == 1 ? 'Online' : 'Offline' ?>
                        </td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 text-sm rounded-full 
                                <?= $college['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                   ($college['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($college['status']) ?>
                            </span>
                        </td>
                        <td class="py-2 px-4"><?= htmlspecialchars($college['registered_date']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
     <!-- Teachers Table -->
     <div class="w-full p-3" id="teachersTable">
        <div class="p-5">
            <h1 class="text-3xl text-black pb-6">Teachers</h1>
            <table class="w-full p-5 text-gray-700" id="teachersTable">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left py-2 px-4">Name</th>
                        <th class="text-left py-2 px-4">College Name</th>
                        <th class="text-left py-2 px-4">Email</th>
                        <th class="text-left py-2 px-4">Branch</th>
                        <th class="text-left py-2 px-4">Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4"><?= htmlspecialchars($teacher['t_name']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($teacher['col_name']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($teacher['t_email']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($teacher['t_branch']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($teacher['registered_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>								
</div>		
</div> 
</body>
</html>
