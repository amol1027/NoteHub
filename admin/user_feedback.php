

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <meta name="description" content="description here">
    <meta name="keywords" content="keywords,here">
	
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js" integrity="sha256-XF29CBwU1MWLaGEnsELogU6Y6rcc5nCkhhx89nFMIDQ=" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>


</head>
<body class="bg-slate-200 font-sans leading-normal tracking-normal">

<nav id="header" class="bg-black fixed w-full z-10 top-0 shadow">
	

		<div class="w-full bg-black container mx-auto flex flex-wrap items-center mt-0 pt-3 pb-3 md:pb-0">
				
			<div class="w-1/2 pl-2 md:pl-0">
				<a class="text-gray-100 text-base xl:text-xl no-underline hover:no-underline font-bold"  href="#"> 
				</a>
            </div>
			<div class="w-1/2 pr-0">
				<div class="flex relative inline-block float-right bg-black">
				
				  <div class="relative text-sm text-gray-100">
					  <button id="userButton" class="flex items-center focus:outline-none mr-3">
						 <span class="hidden md:inline-block text-gray-100">Hi, Admin</span>
						
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
                        <a href="d_board.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2  border-b-2 border-gray-900  hover:border-green-400">
                            <i class="fas fa-home fa-fw mr-3 "></i><span class="pb-1 md:pb-0 text-sm ">Home</span>
                        </a>
                    </li>
					<li class="mr-6 my-2 md:my-0">
                        <a href="admin_task.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2  border-b-2 border-gray-900  hover:border-green-400">
                            <i class="fas fa-tasks fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Tasks</span>
                        </a>
                    </li>
                    <li class="mr-6 my-2 md:my-0">
                        <a href="#" class="block py-1 md:py-3 pl-1 align-middle text-blue-400 no-underline hover:text-gray-100 border-b-2 border-blue-400 hover:border-blue-400">
                            <i class="fa fa-envelope fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Messages</span>
                        </a>
                    </li>
                    <li class="mr-6 my-2 md:my-0">
                        <a href="analytic.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2  border-b-2 border-gray-900  hover:border-green-400">
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
			
		

			<!--Divider-->

            <div class="flex flex-row flex-wrap flex-grow mt-2">

                <div class="w-full md:w-1/2 p-3">
                    <!--Graph Card-->
                  

                <div class="w-full md:w-1/2 xl:w-1/3 p-3">
                    <!--Template Card-->
                
                    <!--/Template Card-->
                </div>

                <div class="w-full p-3">
                    <!--Table Card-->
<div class="bg-white border border-gray-800 rounded shadow">
    <div class="border-b border-gray-800 p-3">
        <h5 class="font-bold uppercase text-gray-600">Contact Form Submissions</h5>
    </div>
    <div class="p-5">
        <?php
        // Database connection
        $host = 'localhost';
        $dbname = 'justclick';
        $username = 'root';
        $password = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Fetch all records
            $stmt = $pdo->query("SELECT * FROM contact_form ORDER BY created_at DESC");
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "<p class='text-red-500'>Error fetching records: " . $e->getMessage() . "</p>";
            $submissions = [];
        }
        ?>

        <table class="w-full text-gray-700">
            <thead>
                <tr>
                    <th class="text-left p-2">Name</th>
                    <th class="text-left p-2">Email</th>
                    <th class="text-left p-2">Message</th>
                    <th class="text-left p-2">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($submissions) > 0): ?>
                    <?php foreach ($submissions as $submission): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-2"><?= htmlspecialchars($submission['name']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($submission['email']) ?></td>
                            <td class="p-2 max-w-[300px] truncate"><?= htmlspecialchars($submission['message']) ?></td>
                            <td class="p-2"><?= date('M j, Y g:i a', strtotime($submission['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="p-2 text-center text-gray-500">No submissions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <p class="py-2 mt-4 text-sm">
            Showing <?= count($submissions) ?> entries
        </p>
    </div>
</div>
<!--/table Card--> 
                </div>
            </div>									
		</div>
	</div> 
	<!--/container-->		
<script>		
	var userMenuDiv = document.getElementById("userMenu");
	var userMenu = document.getElementById("userButton");
	
	var navMenuDiv = document.getElementById("nav-content");
	var navMenu = document.getElementById("nav-toggle");
	
	document.onclick = check;

	function check(e){
	  var target = (e && e.target) || (event && event.srcElement);

	  //User Menu
	  if (!checkParent(target, userMenuDiv)) {
		// click NOT on the menu
		if (checkParent(target, userMenu)) {
		  // click on the link
		  if (userMenuDiv.classList.contains("invisible")) {
			userMenuDiv.classList.remove("invisible");
		  } else {userMenuDiv.classList.add("invisible");}
		} else {
		  // click both outside link and outside menu, hide menu
		  userMenuDiv.classList.add("invisible");
		}
	  }
	  
	  //Nav Menu
	  if (!checkParent(target, navMenuDiv)) {
		// click NOT on the menu
		if (checkParent(target, navMenu)) {
		  // click on the link
		  if (navMenuDiv.classList.contains("hidden")) {
			navMenuDiv.classList.remove("hidden");
		  } else {navMenuDiv.classList.add("hidden");}
		} else {
		  // click both outside link and outside menu, hide menu
		  navMenuDiv.classList.add("hidden");
		}
	  }
	  
	}

	function checkParent(t, elm) {
	  while(t.parentNode) {
		if( t == elm ) {return true;}
		t = t.parentNode;
	  }
	  return false;
	}


</script>

</body>
</html>
