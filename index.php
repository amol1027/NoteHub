<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" id="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/css.css">
    <title>NotesHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="https://img.icons8.com/?size=100&id=79257&format=png&color=000000" type="image/x-icon">
    <style>
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
<body>
    <!-- Modal for displaying messages -->
    <div id="messageModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h2 id="modalTitle" class="text-lg font-bold mb-4">Message</h2>
            <p id="modalMessage" class="text-gray-700 mb-4"></p>
            <button id="closeModal" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Close</button>
        </div>
    </div>
    <header>
        <nav>
            <div class="logo">NoteHub</div>
            <ul class="nav-links">
                <li class="dropdown">
                    <a href="#">Register</a>
                    <div class="dropdown-content">
                        <a href="./student/stud_register.php">Student Register</a>
                        <a href="./college/collage_reg.php">College Register</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Login</a>
                    <div class="dropdown-content">
                        <a href="./student/login.php">Student Login</a>
                        <a href="teacher/teach_log.php">Teacher Login</a>
                        <a href="college/col_login.php">College Login</a>
                        <a href="admin/admin_login.php">Admin Login</a>
                    </div>
                </li>
                <li><a href="#about">About Us</a></li>
                <li><a href="./site.html">Sitemap</a></li>

            </ul>
        </nav>
    </header>

    <section class="hero">
        <video src="back_vid.mp4" autoplay muted loop></video>
        <div class="hero-content">
            <h1>Transform Your Learning Experience</h1>
            <p>Discover an innovative platform designed to enhance your educational journey with interactive tools and expert guidance.</p>
            <a href="./student/stud_register.php" class="bg-blue-500 text-white px-4 py-2 rounded-md cta-button hover:bg-blue-600">Get Started</a>
        </div>
    </section>

    <section class="bg-gray-100" id="about">
        <div class="container mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-8">
                <div class="max-w-lg">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">About Us</h2>
                    <p class="mt-4 text-gray-600 text-lg">NoteHub is a comprehensive educational resource platform designed to streamline the sharing of academic materials between students, teachers, and educational institutions. Founded with the mission to make quality educational resources accessible to all, our platform enables seamless collaboration across different colleges and branches of study. At NoteHub, we believe in the power of knowledge sharing to enhance learning outcomes. Our secure document management system allows students to access course materials, notes, and resources uploaded by their teachers and peers, while our intuitive interface makes navigation simple and efficient. With features like personalized profiles, document categorization by branch, and advanced search capabilities, NoteHub is committed to creating an organized digital environment where educational content can be easily discovered, shared, and utilized. Join our growing community of learners and educators to experience a new way of academic collaboration.</p>
                    
                </div>
                <div class="mt-12 md:mt-0">
                    <img src="https://images.unsplash.com/photo-1531973576160-7125cd663d86" alt="About Us Image" class="object-cover rounded-lg shadow-md">
                </div>
            </div>
        </div>
    </section>
    <section class="bg-gray-900 text-white ">

        <div class="container flex flex-col justify-center p-4 mx-auto md:p-8 mt-[10%]">
         
            <h2 class="mb-12 text-4xl font-bold leading-none text-center sm:text-5xl">Frequently Asked Questions</h2>
            <div class="grid gap-10 md:gap-8 sm:p-3 md:grid-cols-2 lg:px-12 xl:px-32">
                <div>
                    <h3 class="font-semibold">Lorem ipsum dolor sit amet.</h3>
                    <p class="mt-1 dark:text-gray-600">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Ratione, fugit? Aspernatur, ullam enim, odit eaque quia rerum ipsum voluptatem consequatur ratione, doloremque debitis? Fuga labore omnis minima, quisquam delectus culpa!</p>
                </div>
                <div>
                    <h3 class="font-semibold">Lorem ipsum dolor sit amet.</h3>
                    <p class="mt-1 dark:text-gray-600">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Ratione, fugit? Aspernatur, ullam enim, odit eaque quia rerum ipsum voluptatem consequatur ratione, doloremque debitis? Fuga labore omnis minima, quisquam delectus culpa!</p>
                </div>
                <div>
                    <h3 class="font-semibold">Lorem ipsum dolor sit amet.</h3>
                    <p class="mt-1 dark:text-gray-600">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Ratione, fugit? Aspernatur, ullam enim, odit eaque quia rerum ipsum voluptatem consequatur ratione, doloremque debitis? Fuga labore omnis minima, quisquam delectus culpa!</p>
                </div>
                <div>
                    <h3 class="font-semibold">Lorem ipsum dolor sit amet.</h3>
                    <p class="mt-1 dark:text-gray-600">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Ratione, fugit? Aspernatur, ullam enim, odit eaque quia rerum ipsum voluptatem consequatur ratione, doloremque debitis? Fuga labore omnis minima, quisquam delectus culpa!</p>
                </div>
            </div>
        </div>
    </section>
    <section class="bg-white text-gray-900" id="contact">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-5xl font-extrabold text-gray-900 mb-4">
                    Get in Touch
                </h2>
                
            </div>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <p class="text-lg text-gray-700">
                        We would love to hear from you! Whether you have a question about our services, need support, or just want to say hello, feel free to reach out.
                    </p>
                    <ul class="space-y-6">
                       
                        <li class="flex items-start">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-900 text-white mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8">
                                    <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"></path>
                                    <path d="M15 7a2 2 0 0 1 2 2"></path>
                                    <path d="M15 3a6 6 0 0 1 6 6"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Contact</h3>
                                <p class="text-gray-600">Mobile: +1 (123) 456-7890</p>
                                <p class="text-gray-600">Mail: tailnext@gmail.com</p>
                            </div>
                        </li>
                       
                    </ul>
                </div>
                <div class="bg-gray-900 p-8 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold text-white mb-4">Ready to Get Started?</h2>
                    <form id="contactForm" method="post" action="contact.php" class="space-y-4">
                        <div class="mb-4">
                            <label for="name" class="block text-xs uppercase tracking-wider text-gray-400 mb-1">Name</label>
                            <input type="text" name="name" autocomplete="given-name" placeholder="Your name" class="w-full bg-gray-800 border border-gray-700 rounded-md py-2 px-4 text-white shadow-md focus:outline-none focus:ring-2 focus:ring-gray-600">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-xs uppercase tracking-wider text-gray-400 mb-1">Email</label>
                            <input type="email" name="email" autocomplete="email" placeholder="Your email address" class="w-full bg-gray-800 border border-gray-700 rounded-md py-2 px-4 text-white shadow-md focus:outline-none focus:ring-2 focus:ring-gray-600">
                        </div>
                        <div class="mb-4">
                            <label for="textarea" class="block text-xs uppercase tracking-wider text-gray-400 mb-1">Message</label>
                            <textarea name="message" cols="30" rows="5" placeholder="Write your message..." class="w-full bg-gray-800 border border-gray-700 rounded-md py-2 px-4 text-white shadow-md focus:outline-none focus:ring-2 focus:ring-gray-600"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-white text-gray-900 px-6 py-3 font-semibold rounded-md hover:bg-gray-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>


    <footer>
        @Notes Hub
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.0/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.0/ScrollTrigger.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const contactForm = document.getElementById('contactForm');
            const messageModal = document.getElementById('messageModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const closeModal = document.getElementById('closeModal');

            const showModal = (title, message) => {
                modalTitle.textContent = title;
                modalMessage.textContent = message;
                messageModal.classList.remove('hidden');
            };

            closeModal.addEventListener('click', () => {
                messageModal.classList.add('hidden');
            });

            if (contactForm) {
                contactForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const form = e.target;
                    const formData = new FormData(form);
                    
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (!response.ok) {
                            throw result;
                        }
                        
                        showModal('Success', result.message);
                        form.reset();
                    } catch (error) {
                        showModal('Error', error.message || 'An error occurred');
                    }
                });
            }
        });

        // GSAP animations
        gsap.from(".hero-content h1", { duration: 1.5, y: -50, opacity: 0, ease: "power3.out" });
        gsap.from(".hero-content p", { duration: 1.5, y: 50, opacity: 0, ease: "power3.out", delay: 0.5 });
        gsap.from(".cta-button", { duration: 1.5, scale: 0.9, opacity: 0, ease: "bounce.out", delay: 1 });

        gsap.from(".features .feature-item", {
            duration: 1.2,
            opacity: 0,
            y: 50,
            stagger: 0.3,
            ease: "power3.out",
            scrollTrigger: {
                trigger: ".features",
                start: "top 80%",
                end: "bottom 20%",
                scrub: true
            }
        });

        gsap.from(".about p", { duration: 1.2, opacity: 0, y: 50, ease: "power3.out", scrollTrigger: { trigger: ".about", start: "top 80%" } });
        gsap.from(".contact p", { duration: 1.2, opacity: 0, y: 50, ease: "power3.out", scrollTrigger: { trigger: ".contact", start: "top 80%" } });
    </script>
</body>
</html>