<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Graph</title>
    <meta name="description" content="description here">
    <meta name="keywords" content="keywords,here">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js" integrity="sha256-XF29CBwU1MWLaGEnsELogU6Y6rcc5nCkhhx89nFMIDQ=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .bg-black-alt  {
            background:#191919;
        }
        .text-black-alt  {
            color:#191919;
        }
        .border-black-alt {
            border-color: #191919;
        }
        .fixed-dropdown {
            position: fixed;
            top: 15%; /* Adjust based on your header height */
            left: 50%;
            z-index: 1000;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body class="bg-slate-200 font-sans leading-normal tracking-normal">

<nav id="header" class="bg-black fixed w-full z-10 top-0 shadow">
    <div class="w-full container mx-auto flex flex-wrap items-center mt-0 pt-3 pb-3 md:pb-0">
        <div class="w-1/2 pl-2 md:pl-0">
            <a class="text-white text-base xl:text-xl no-underline hover:no-underline font-bold" href="#"></a>
        </div>
        <div class="w-1/2 pr-0">
            <div class="flex relative inline-block float-right">
                <div class="relative text-sm text-white">
                    <button id="userButton" class="flex items-center focus:outline-none mr-3">
                       Hi, Admin</span>
                        
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
                    <a href="d_board.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2 border-b-2 border-gray-900 hover:border-green-400">
                        <i class="fas fa-home fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Home</span>
                    </a>
                </li>
                <li class="mr-6 my-2 md:my-0">
                    <a href="./admin_task.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2 border-gray-900 hover:border-pink-400">
                        <i class="fas fa-tasks fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Tasks</span>
                    </a>
                </li>
                <li class="mr-6 my-2 md:my-0">
                    <a href="user_feedback.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-500 no-underline hover:text-gray-100 border-b-2 border-gray-900 hover:border-purple-400">
                        <i class="fa fa-envelope fa-fw mr-3"></i><span class="pb-1 md:pb-0 text-sm">Messages</span>
                    </a>
                </li>
                <li class="mr-6 my-2 md:my-0">
                    <a href="#" class="block py-1 md:py-3 pl-1 align-middle text-blue-400 no-underline hover:text-gray-100 border-b-2 border-gray-900 border-blue-400 hover:border-green-400">
                        <i class="fas fa-chart-area fa-fw mr-3 text-blue-400"></i><span class="pb-1 md:pb-0 text-sm">Analytics</span>
                    </a>
                </li>
            </ul>
            <
        </div>
    </div>
</nav>

<div class="fixed-dropdown">
    <select id="graphSelector">
        <option value="user">Users</option>
        <option value="college">Colleges</option>
        <option value="home">Home Data</option>
    </select>
</div>

<!-- Container -->
<div class="container w-full mx-auto pt-20">
    <div class="w-full px-4 md:px-0 md:mt-8 mb-16 text-gray-800 leading-normal">
        <div class="flex flex-row flex-wrap flex-grow mt-2">
            <!-- Graph Card for User Registrations -->
            <div id="userGraphContainer" class="w-full md:w-1/2 p-3">
                <div class="bg-white border border-gray-800 rounded shadow">
                    <div class="border-b border-gray-800 p-3 flex justify-between items-center">
                        <h5 class="font-bold uppercase text-gray-600">User Registrations</h5>
                        <div class="flex space-x-2">
                            <select id="userMonthFilter" class="border border-gray-400 rounded px-2 py-1">
                                <option value="all">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                            <select id="userYearFilter" class="border border-gray-400 rounded px-2 py-1">
                                <option value="all">All Years</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>
                    <div class="p-5">
                        <canvas id="userGraph" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Graph Card for Home Data Entries -->
            <div id="homeGraphContainer" class="w-full md:w-1/2 p-3 hidden">
                <div class="bg-white border border-gray-800 rounded shadow">
                    <div class="border-b border-gray-800 p-3 flex justify-between items-center">
                        <h5 class="font-bold uppercase text-gray-600">Home Data Entries</h5>
                        <div class="flex space-x-2">
                            <select id="homeMonthFilter" class="border border-gray-400 rounded px-2 py-1">
                                <option value="all">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                            <select id="homeYearFilter" class="border border-gray-400 rounded px-2 py-1">
                                <option value="all">All Years</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>
                    <div class="p-5">
                        <canvas id="homeDataGraph" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Graph Card for College Approvals -->
            <div id="collegeGraphContainer" class="w-full md:w-1/2 p-3 hidden">
                <div class="bg-white border border-gray-800 rounded shadow">
                    <div class="border-b border-gray-800 p-3 flex justify-between items-center">
                        <h5 class="font-bold uppercase text-gray-600">College Approvals</h5>
                        <div class="flex space-x-2">
                            <select id="collegeMonthFilter" class="border border-gray-400 rounded px-2 py-1">
                                <option value="all">All Months</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                            <select id="collegeYearFilter" class="border border-gray-400 rounded px-2 py-1">
                                <option value="all">All Years</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>
                    <div class="p-5">
                        <canvas id="collegeGraph" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chart instances
let userChartInstance = null;
let homeChartInstance = null;
let collegeChartInstance = null;

// Get current month and year
function getCurrentMonthAndYear() {
    const now = new Date();
    return { month: now.getMonth() + 1, year: now.getFullYear() };
}

// Fetch user registration data
async function fetchUserData(month, year) {
    const queryParams = new URLSearchParams();
    if (month !== 'all') queryParams.append('month', month);
    if (year !== 'all') queryParams.append('year', year);

    try {
        const response = await fetch(`fetch_ussers.php?${queryParams}`); // Fixed typo
        if (!response.ok) throw new Error('Network response was not ok');
        return await response.json();
    } catch (error) {
        console.error('Error fetching user data:', error);
        return [];
    }
}

// Fetch home data
async function fetchHomeData(month, year) {
    const queryParams = new URLSearchParams();
    if (month !== 'all') queryParams.append('month', month);
    if (year !== 'all') queryParams.append('year', year);

    try {
        const response = await fetch(`fetch_homedata.php?${queryParams}`);
        if (!response.ok) throw new Error('Network response was not ok');
        return await response.json();
    } catch (error) {
        console.error('Error fetching home data:', error);
        return [];
    }
}

// Fetch college data
async function fetchCollegeData(month, year) {
    const queryParams = new URLSearchParams();
    if (month !== 'all') queryParams.append('month', month);
    if (year !== 'all') queryParams.append('year', year);

    try {
        const response = await fetch(`fetch_college.php?${queryParams}`);
        if (!response.ok) throw new Error('Network response was not ok');
        return await response.json();
    } catch (error) {
        console.error('Error fetching college data:', error);
        return [];
    }
}

// Helper function to generate all days in a month
function getAllDaysInMonth(month, year) {
    const date = new Date(year, month - 1, 1); // month is 1-based
    const days = [];
    while (date.getMonth() === month - 1) {
        days.push(date.getDate());
        date.setDate(date.getDate() + 1);
    }
    return days;
}

// Render user graph (modified)
async function renderUserGraph() {
    const month = document.getElementById('userMonthFilter')?.value;
    const year = document.getElementById('userYearFilter')?.value;
    if (!month || !year) return;

    const data = await fetchUserData(month, year);
    if (userChartInstance) userChartInstance.destroy();

    // Create array of all days in the selected month
    const allDays = getAllDaysInMonth(month === 'all' ? new Date().getMonth() + 1 : parseInt(month), 
                   year === 'all' ? new Date().getFullYear() : parseInt(year));
    
    // Create data map for existing data
    const dataMap = new Map(data.map(item => [parseInt(item.day_of_month), parseInt(item.total_users)]));

    // Fill in data for all days
    const filledData = allDays.map(day => dataMap.has(day) ? dataMap.get(day) : 0);

    const ctx = document.getElementById('userGraph').getContext('2d');
    userChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: allDays,
            datasets: [{
                label: 'Total Users Registered',
                data: filledData,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 2
            }]
        },
        options: {
            scales: {
                x: { 
                    title: { display: true, text: 'Day of Month' },
                    ticks: {
                        stepSize: 1,
                        callback: function(value) { return Number.isInteger(value) ? value : ''; }
                    }
                },
                y: {
                    title: { display: true, text: 'Total Users' },
                    beginAtZero: true,
                    ticks: { 
                        stepSize: 1,
                        callback: value => Number.isInteger(value) ? value : null 
                    }
                }
            }
        }
    });
}

// Render home graph
async function renderHomeGraph() {
    const month = document.getElementById('homeMonthFilter')?.value;
    const year = document.getElementById('homeYearFilter')?.value;
    if (!month || !year) return;

    const data = await fetchHomeData(month, year);
    if (homeChartInstance) homeChartInstance.destroy();

    const ctx = document.getElementById('homeDataGraph').getContext('2d');
    homeChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.day_of_month),
            datasets: [{
                label: 'Total Links',
                data: data.map(item => item.total_links),
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderWidth: 2
            }]
        },
        options: {
            scales: {
                x: { title: { display: true, text: 'Day of Month' } },
                y: {
                    title: { display: true, text: 'Total Links' },
                    beginAtZero: true,
                    ticks: { stepSize: 1, callback: value => Number.isInteger(value) ? value : null }
                }
            }
        }
    });
}


// Render college graph (modified)
async function renderCollegeGraph() {
    const month = document.getElementById('collegeMonthFilter')?.value;
    const year = document.getElementById('collegeYearFilter')?.value;
    if (!month || !year) return;

    const data = await fetchCollegeData(month, year);
    if (collegeChartInstance) collegeChartInstance.destroy();

    // Create array of all days in the selected month
    const allDays = getAllDaysInMonth(month === 'all' ? new Date().getMonth() + 1 : parseInt(month), 
                   year === 'all' ? new Date().getFullYear() : parseInt(year));
    
    // Create data map for existing data
    const dataMap = new Map(data.map(item => [parseInt(item.day_of_month), parseInt(item.total_colleges)]));

    // Fill in data for all days
    const filledData = allDays.map(day => dataMap.has(day) ? dataMap.get(day) : 0);

    const ctx = document.getElementById('collegeGraph').getContext('2d');
    collegeChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: allDays,
            datasets: [{
                label: 'Approved Colleges',
                data: filledData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2
            }]
        },
        options: {
            scales: {
                x: { 
                    title: { display: true, text: 'Day of Month' },
                    ticks: {
                        stepSize: 1,
                        callback: function(value) { return Number.isInteger(value) ? value : ''; }
                    }
                },
                y: {
                    title: { display: true, text: 'Number of Colleges' },
                    beginAtZero: true,
                    ticks: { 
                        stepSize: 1,
                        callback: value => Number.isInteger(value) ? value : null 
                    }
                }
            }
        }
    });
}
// Combined initialization
document.addEventListener('DOMContentLoaded', () => {
    const { month, year } = getCurrentMonthAndYear();

    // Set default values only if elements exist
    if (document.getElementById('userMonthFilter')) document.getElementById('userMonthFilter').value = month;
    if (document.getElementById('userYearFilter')) document.getElementById('userYearFilter').value = year;
    if (document.getElementById('homeMonthFilter')) document.getElementById('homeMonthFilter').value = month;
    if (document.getElementById('homeYearFilter')) document.getElementById('homeYearFilter').value = year;
    if (document.getElementById('collegeMonthFilter')) document.getElementById('collegeMonthFilter').value = month;
    if (document.getElementById('collegeYearFilter')) document.getElementById('collegeYearFilter').value = year;

    // Initial render
    renderUserGraph();
    renderHomeGraph();
    renderCollegeGraph();
});

// Event listeners for filters
document.getElementById('userMonthFilter')?.addEventListener('change', renderUserGraph);
document.getElementById('userYearFilter')?.addEventListener('change', renderUserGraph);
document.getElementById('homeMonthFilter')?.addEventListener('change', renderHomeGraph);
document.getElementById('homeYearFilter')?.addEventListener('change', renderHomeGraph);
document.getElementById('collegeMonthFilter')?.addEventListener('change', renderCollegeGraph);
document.getElementById('collegeYearFilter')?.addEventListener('change', renderCollegeGraph);

// Event listener for graph selector
document.getElementById('graphSelector')?.addEventListener('change', (event) => {
    const selectedGraph = event.target.value;
    document.getElementById('userGraphContainer').classList.add('hidden');
    document.getElementById('homeGraphContainer').classList.add('hidden');
    document.getElementById('collegeGraphContainer').classList.add('hidden');

    if (selectedGraph === 'user') {
        document.getElementById('userGraphContainer').classList.remove('hidden');
    } else if (selectedGraph === 'home') {
        document.getElementById('homeGraphContainer').classList.remove('hidden');
    } else if (selectedGraph === 'college') {
        document.getElementById('collegeGraphContainer').classList.remove('hidden');
    }
});
</script>

</body>
</html>