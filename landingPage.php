<?php 
require_once 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>AutoMind AI</title>
        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Google Font: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <style>
            /* Base styles */
            body {
                font-family: 'Inter', sans-serif;
            }

            html {
                scroll-behavior: smooth;
            }

            /* Page switching logic */
            .page {
                display: none;
            }
            .page.active {
                display: block;
            }
        
            /* Sidebar active link style */
            .sidebar-link.active {
                background-color: #111827; /* bg-gray-900 */
                color: white;
            }
        
            /* Simple bar chart mock */
            .chart-bar {
                background-color: #3b82f6; /* bg-blue-500 */
                border-radius: 4px 4px 0 0;
                transition: height 0.3s ease;
            }
        
                /* Hide scrollbar but allow scrolling */
            #main-content {
                -ms-overflow-style: none;  /* IE and Edge */
                scrollbar-width: none;  /* Firefox */
            }
            #main-content::-webkit-scrollbar {
                display: none; /* Chrome, Safari, Opera */
            }

            /* Toast Notification Style */
            #toast {
                visibility: hidden;
                opacity: 0;
                transform: translateY(-100%);
                transition: all 0.5s ease-in-out;
            }
            #toast.show {
                visibility: visible;
                opacity: 1;
                transform: translateY(0);
            }

            /* Interactive row hover */
            .interactive-row:hover {
                background-color: #374151; /* bg-gray-700 */
            }
        
        </style>
</head>

<body class="bg-gray-900 text-gray-300">

    <!-- 
    =====================================================================
    Toast Notification
    =====================================================================
    -->
    <div id="toast" class="fixed top-8 left-1/2 -translate-x-1/2 z-50">
        <div id="toast-content" class="flex items-center space-x-3 bg-green-600 text-white font-semibold py-3 px-6 rounded-lg shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span id="toast-message">Success!</span>
        </div>
    </div>


    <!-- 
    =====================================================================
    Page 1: Landing Page (NEW)
    Contains Hero, Features, and Login Forms
    =====================================================================
    -->
    <div id="landing-page" class="page active">
        
        <!-- Landing Page Navbar -->
        <nav class="absolute top-0 left-0 right-0 p-6 z-10 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <svg class="w-8 h-8 text-blue-500" fill="currentColor"><use href="#icon-logo"></use></svg>
                <span class="text-2xl font-bold text-white">AutoMind AI</span>
            </div>
            <div>
                <!-- Removed "Features" link per request -->
                <a href="#login-forms-section" id="nav-login-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">Sign In</a>
            </div>
        </nav>
    
        <!-- Hero Section -->
        <div class="min-h-screen flex items-center justify-center p-8 pt-24 text-center relative overflow-hidden">
            <div class="relative z-10">
                <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-6 leading-tight">Your Intelligent Vehicle Co-pilot.</h1>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto mb-10">
                    Stop guessing. Start knowing. AutoMind AI gives you data-driven insights, tracks maintenance, and analyzes mechanic bills to save you money.
                </p>
                <a href="#login-forms-section" id="hero-get-started-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg text-lg transition duration-300 transform hover:scale-105">
                    Get Started Now
                </a>
            </div>
        </div>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-gray-800">
            <div class="container mx-auto px-6 max-w-6xl">
                <h2 class="text-3xl font-bold text-white text-center mb-4">All Your Vehicle Data. One Smart App.</h2>
                <p class="text-gray-400 text-center max-w-2xl mx-auto mb-12">From AI-powered diagnostics to your digital glovebox, we've got you covered.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature Card 1 -->
                    <div class="bg-gray-900 p-8 rounded-lg shadow-lg">
                        <svg class="w-10 h-10 text-blue-500 mb-4"><use href="#icon-garage"></use></svg>
                        <h3 class="text-xl font-semibold text-white mb-2">Vehicle Management</h3>
                        <p class="text-gray-400">Manage all your cars, track service history, and store important documents like insurance and registration in your Digital Glovebox.</p>
                    </div>
                    <!-- Feature Card 2 -->
                    <div class="bg-gray-900 p-8 rounded-lg shadow-lg">
                        <svg class="w-10 h-10 text-blue-500 mb-4"><use href="#icon-diagnostics"></use></svg>
                        <h3 class="text-xl font-semibold text-white mb-2">AI Diagnostics & Analysis</h3>
                        <p class="text-gray-400">Upload mechanic bills for an AI-powered price analysis to spot overcharges. Look up known issues for your model.</p>
                    </div>
                    <!-- Feature Card 3 -->
                    <div class="bg-gray-900 p-8 rounded-lg shadow-lg">
                        <svg class="w-10 h-10 text-blue-500 mb-4"><use href="#icon-dashboard"></use></svg>
                        <h3 class="text-xl font-semibold text-white mb-2">Day-to-Day Copilot</h3>
                        <p class="text-gray-400">Get automatic reminders for expiring documents, log fuel to monitor efficiency, and keep personalized notes for each vehicle.</p>
                    </div>
                </div>
            </div>
        </section>

         <!-- Login/Signup Section (Original Page Content) -->
        <section id="login-forms-section" class="py-20 bg-gray-900">
             <div class="grid grid-cols-1 md:grid-cols-2 max-w-4xl w-full bg-gray-800 rounded-2xl shadow-2xl overflow-hidden mx-auto">
                <!-- Left Side: Branding -->
                <div class="p-12 flex flex-col justify-center">
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="w-10 h-10 text-blue-500" fill="currentColor"><use href="#icon-logo"></use></svg>
                        <span class="text-3xl font-bold text-white">AutoMind AI</span>
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">Your Intelligent Vehicle Co-pilot.</h1>
                    <p class="text-gray-400">
                        Sign in or create an account to get personalized maintenance schedules, diagnostic alerts, and data-driven insights for your vehicle.
                    </p>
                </div>

                <!-- Right Side: Form -->
                <div class="p-12 bg-gray-900">
                    <div class="mb-6">
                        <div class="flex border-b border-gray-700">
                            <button id="tab-signin" class="flex-1 py-2 font-semibold text-white border-b-2 border-blue-500">Sign In</button>
                            <button id="tab-create" class="flex-1 py-2 font-semibold text-gray-500">Create Account</button>
                        </div>
                    </div>

                    <!-- Sign In Form -->
                    <form id="form-signin" class="space-y-6">
                        <div>
                            <label for="email-signin" class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
                            <input type="email" id="email-signin" value="jane.doe@email.com" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your email">
                        </div>
                        <div>
                            <label for="password-signin" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                            <input type="password" id="password-signin" value="••••••••" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your password">
                        </div>
                        <div id="forgot-password-container" class="text-right">
                             <a href="#" id="forgot-password-link" class="text-sm text-blue-500 hover:underline">Forgot Password?</a>
                        </div>
                        <button type="submit" data-page="vehicle-dashboard" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Sign In
                        </button>
                    </form>

                    <!-- Create Account Form (Hidden) -->
                    <form id="form-create" class="space-y-6" style="display: none;">
                        <div>
                            <label for="email-create" class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
                            <input type="email" id="email-create" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your email">
                        </div>
                        <div>
                            <label for="password-create" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                            <input type="password" id="password-create" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500" placeholder="Create a password">
                        </div>
                        <div id="confirm-password-container">
                            <label for="password-confirm" class="block text-sm font-medium text-gray-300 mb-1">Confirm Password</label>
                            <input type="password" id="password-confirm" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500" placeholder="Confirm your password">
                        </div>
                        <button type="submit" data-page="vehicle-dashboard" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                            Create Account
                        </button>
                    </form>

                    <div class="flex items-center justify-center my-6">
                        <span class="w-full h-px bg-gray-700"></span>
                        <span class="mx-4 text-gray-500">or</span>
                        <span class="w-full h-px bg-gray-700"></span>
                    </div>
                    
                    <button id="google-signin-btn" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg flex items-center justify-center space-x-2 transition duration-200">
                        <svg class="w-5 h-5"><use href="#icon-google"></use></svg>
                        <span>Continue with Google</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-800 py-12">
            <div class="container mx-auto px-6 max-w-6xl text-center">
                <p class="text-gray-500">&copy; 2025 AutoMind AI. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script>
/* ===============================
   ELEMENTS
================================ */
const tabSignin = document.getElementById("tab-signin");
const tabCreate = document.getElementById("tab-create");
const formSignin = document.getElementById("form-signin");
const formCreate = document.getElementById("form-create");
const toast = document.getElementById("toast");
const toastMsg = document.getElementById("toast-message");

/* ===============================
   TOAST
================================ */
function showToast(msg, success = true) {
    toastMsg.textContent = msg;
    toast.querySelector("#toast-content").className =
        success
            ? "flex items-center space-x-3 bg-green-600 text-white font-semibold py-3 px-6 rounded-lg shadow-lg"
            : "flex items-center space-x-3 bg-red-600 text-white font-semibold py-3 px-6 rounded-lg shadow-lg";

    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 3000);
}

/* ===============================
   TAB SWITCHING
================================ */
tabSignin.onclick = () => {
    formSignin.style.display = "block";
    formCreate.style.display = "none";
};

tabCreate.onclick = () => {
    formSignin.style.display = "none";
    formCreate.style.display = "block";
};

/* ===============================
   SIGN IN (PHP → Firebase DB)
================================ */
formSignin.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email-signin").value;
    const password = document.getElementById("password-signin").value;

    const res = await fetch("auth/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    });

    const data = await res.json();

    if (data.success) {
        showToast("Login successful!");
        setTimeout(() => location.href = "dashboard.php", 1200);
    } else {
        showToast(data.message, false);
    }
});

/* ===============================
   CREATE ACCOUNT
================================ */
formCreate.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email-create").value;
    const password = document.getElementById("password-create").value;
    const confirm = document.getElementById("password-confirm").value;

    if (password !== confirm) {
        showToast("Passwords do not match", false);
        return;
    }

    const res = await fetch("auth/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
    });

    const data = await res.json();

    if (data.success) {
        showToast("Account created!");
        setTimeout(() => location.href = "dashboard.php", 1200);
    } else {
        showToast(data.message, false);
    }
});
</script>


</body>



</html>
