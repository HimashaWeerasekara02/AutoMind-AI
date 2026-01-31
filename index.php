<?php 
require_once 'db.php'; 
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoMind AI | Your Intelligent Vehicle Co-pilot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        
        .hero-gradient {
            background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent), 
                        radial-gradient(circle at bottom left, rgba(30, 41, 59, 1), rgba(17, 24, 39, 1));
        }

        #toast {
            visibility: hidden;
            opacity: 0;
            transform: translate(-50%, -100%);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        #toast.show {
            visibility: visible;
            opacity: 1;
            transform: translate(-50%, 0);
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-300 antialiased">

    <div id="toast" class="fixed top-8 left-1/2 z-50 w-full max-w-sm px-4">
        <div id="toast-content" class="flex items-center space-x-3 py-4 px-6 rounded-xl shadow-2xl border backdrop-blur-md">
            <span id="toast-message"></span>
        </div>
    </div>

    <nav class="fixed top-0 left-0 right-0 p-6 z-40 bg-gray-900/80 backdrop-blur-md border-b border-white/5 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <img src="Images/logo.png" alt="AutoMind AI Logo" class="h-10 w-auto">
            <span class="text-2xl font-bold text-white tracking-tight">AutoMind AI</span>
        </div>
        <div>
            <a href="#login-forms-section" onclick="switchTab('signin')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-blue-900/20">Sign In</a>
        </div>
    </nav>

    <div class="min-h-screen flex items-center justify-center p-8 hero-gradient relative overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-blue-600/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl"></div>

        <div class="relative z-10 text-center">
            <div class="inline-flex items-center space-x-2 bg-blue-500/10 border border-blue-500/20 px-4 py-1.5 rounded-full mb-8">
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                <span class="text-blue-400 text-sm font-medium">Next-Gen Vehicle Intelligence</span>
            </div>
            <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 leading-tight tracking-tighter">
                Your Intelligent <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-500">Vehicle Co-pilot.</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                Stop guessing. Start knowing. AutoMind AI analyzes mechanic bills, tracks maintenance, and predicts issues before they happen.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="#login-forms-section" onclick="switchTab('create')" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-10 rounded-2xl text-lg transition duration-300 transform hover:-translate-y-1 shadow-xl shadow-blue-600/20">
                    Get Started Free
                </a>
                <a href="#features" class="w-full sm:w-auto bg-gray-800 hover:bg-gray-700 text-white font-bold py-4 px-10 rounded-2xl text-lg transition">
                    Explore Features
                </a>
            </div>
        </div>
    </div>

    <section id="features" class="py-24 bg-gray-900">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-black text-white italic mb-4">ENGINEERED FOR PRECISION</h2>
                <p class="text-gray-500 max-w-xl mx-auto">Advanced tools to keep your vehicle in peak condition without the headache of manual tracking.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gray-800/50 p-8 rounded-3xl border border-white/5 hover:border-blue-500/50 transition-all">
                    <div class="bg-blue-600/10 w-12 h-12 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">AI Diagnostics</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Predictive analysis that identifies mechanical wear before it becomes a costly repair bill.</p>
                </div>

                <div class="bg-gray-800/50 p-8 rounded-3xl border border-white/5 hover:border-indigo-500/50 transition-all">
                    <div class="bg-indigo-600/10 w-12 h-12 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Fuel Intelligence</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">Track efficiency trends and optimize your driving habits to save money at the pump.</p>
                </div>

                <div class="bg-gray-800/50 p-8 rounded-3xl border border-white/5 hover:border-purple-500/50 transition-all">
                    <div class="bg-purple-600/10 w-12 h-12 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Maintenance Vault</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">A digital timeline of every service, oil change, and part replacement for your entire fleet.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="login-forms-section" class="py-24">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex flex-col md:flex-row bg-gray-800 rounded-[2.5rem] shadow-3xl overflow-hidden border border-white/5">
                <div class="md:w-5/12 p-12 bg-gradient-to-br from-blue-600 to-indigo-700 flex flex-col justify-center text-white">
                    <h2 class="text-3xl font-bold mb-6">Join the future of car ownership.</h2>
                    <ul class="space-y-4">
                        <li class="flex items-center space-x-3 text-blue-100">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            <span>Save up to 30% on repairs</span>
                        </li>
                        <li class="flex items-center space-x-3 text-blue-100">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            <span>AI-driven Bill Analysis</span>
                        </li>
                        <li class="flex items-center space-x-3 text-blue-100">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            <span>Smart Maintenance Reminders</span>
                        </li>
                    </ul>
                </div>

                <div class="md:w-7/12 p-12 bg-gray-900">
                    <div class="flex space-x-8 mb-8 border-b border-gray-800">
                        <button id="tab-signin" onclick="switchTab('signin')" class="pb-4 text-lg font-bold text-white border-b-2 border-blue-500">Sign In</button>
                        <button id="tab-create" onclick="switchTab('create')" class="pb-4 text-lg font-bold text-gray-500 hover:text-gray-300 transition">Register</button>
                    </div>

                    <form id="form-signin" class="space-y-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2 ml-1">Email</label>
                            <input type="email" id="email-signin" required class="w-full bg-gray-800 border border-white/5 rounded-xl p-4 text-white focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="name@example.com">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2 ml-1">Password</label>
                            <input type="password" id="password-signin" required class="w-full bg-gray-800 border border-white/5 rounded-xl p-4 text-white focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="••••••••">
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-900/40 transition-all transform active:scale-95">Access Dashboard</button>
                    </form>

                    <form id="form-create" class="space-y-5 hidden">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2 ml-1">Full Name</label>
                            <input type="text" id="name-create" required class="w-full bg-gray-800 border border-white/5 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="Name">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2 ml-1">Email Address</label>
                            <input type="email" id="email-create" required class="w-full bg-gray-800 border border-white/5 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="name@example.com">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2 ml-1">Phone Number (For Reminders)</label>
                            <input type="tel" id="phone-create" required class="w-full bg-gray-800 border border-white/5 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="+94 234 567 890">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2 ml-1">Password</label>
                                <input type="password" id="password-create" required class="w-full bg-gray-800 border border-white/5 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="••••••••">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2 ml-1">Confirm</label>
                                <input type="password" id="password-confirm" required class="w-full bg-gray-800 border border-white/5 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="••••••••">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-900/40 transition-all transform active:scale-95">Create Free Account</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-12 border-t border-white/5 text-center">
        <p class="text-gray-500 text-sm">© 2026 AutoMind AI. Built with precision intelligence.</p>
    </footer>

    <script>
        const tabSignin = document.getElementById("tab-signin");
        const tabCreate = document.getElementById("tab-create");
        const formSignin = document.getElementById("form-signin");
        const formCreate = document.getElementById("form-create");
        const toast = document.getElementById("toast");
        const toastMsg = document.getElementById("toast-message");

        function switchTab(type) {
            if (type === 'signin') {
                formSignin.classList.remove("hidden");
                formCreate.classList.add("hidden");
                tabSignin.className = "pb-4 text-lg font-bold text-white border-b-2 border-blue-500";
                tabCreate.className = "pb-4 text-lg font-bold text-gray-500 hover:text-gray-300 transition";
            } else {
                formSignin.classList.add("hidden");
                formCreate.classList.remove("hidden");
                tabCreate.className = "pb-4 text-lg font-bold text-white border-b-2 border-indigo-500";
                tabSignin.className = "pb-4 text-lg font-bold text-gray-500 hover:text-gray-300 transition";
            }
        }

        function showToast(msg, success = true) {
            toastMsg.textContent = msg;
            const content = toast.querySelector("#toast-content");
            content.className = `flex items-center space-x-3 py-4 px-6 rounded-xl shadow-2xl border ${success ? 'bg-green-600/20 border-green-500/50 text-green-400' : 'bg-red-600/20 border-red-500/50 text-red-400'}`;
            toast.classList.add("show");
            setTimeout(() => toast.classList.remove("show"), 4000);
        }

        formSignin.onsubmit = async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = `<span class="inline-block animate-spin mr-2">↻</span> Authenticating...`;
            btn.disabled = true;

            try {
                const res = await fetch("login.php", { 
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        email: document.getElementById("email-signin").value,
                        password: document.getElementById("password-signin").value
                    })
                });

                const data = await res.json();
                if (data.success) {
                    showToast("Welcome back! Redirecting...");
                    setTimeout(() => location.href = "dashboard.php", 1000);
                } else {
                    showToast(data.message || "Invalid credentials", false);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                showToast("Connection error. Check your server.", false);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };

        formCreate.onsubmit = async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const name = document.getElementById("name-create").value;
            const email = document.getElementById("email-create").value;
            const phone = document.getElementById("phone-create").value; 
            const pass = document.getElementById("password-create").value;
            const conf = document.getElementById("password-confirm").value;

            if (pass !== conf) {
                showToast("Passwords do not match", false);
                return;
            }

            btn.disabled = true;
            btn.innerHTML = `<span class="inline-block animate-spin mr-2">↻</span> Creating Account...`;

            try {
                const res = await fetch("register.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        displayName: name, 
                        email: email,       
                        phone: phone, 
                        password: pass
                    })
                });

                const data = await res.json();
                if (data.success) {
                    showToast("Account created! Redirecting...");
                    // Using register.php success, but we need to log in or redirect to login
                    setTimeout(() => location.href = "login.php", 1000);
                } else {
                    showToast(data.message || "Registration failed", false);
                    btn.disabled = false;
                    btn.innerHTML = "Create Free Account";
                }
            } catch (err) {
                showToast("Connection error", false);
                btn.disabled = false;
                btn.innerHTML = "Create Free Account";
            }
        };
    </script>
</body>
</html>