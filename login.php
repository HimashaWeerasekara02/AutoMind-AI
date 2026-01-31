<?php
require_once 'db.php';
session_start();

/**
 * 1. AUTH GUARD
 * If already logged in, send to dashboard.
 */
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

/**
 * 2. API REQUEST HANDLER
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents("php://input"), true);
    $email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    if (empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Please enter both email and password"]);
        exit;
    }

    try {
        // --- 1. HARDCODED ADMIN CHECK ---
        $adminEmail = "admin@gmail.com";
        $adminPass = "admin123"; 

        if ($email === $adminEmail && $password === $adminPass) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = "SUPER_ADMIN_01";
            $_SESSION['email'] = $adminEmail;
            $_SESSION['displayName'] = "System Admin";
            $_SESSION['is_admin'] = true; // Set this to true for dashboard logic

            echo json_encode([
                "success" => true, 
                "redirect" => "dashboard.php" 
            ]);
            exit;
        }

        // --- 2. DATABASE USER CHECK ---
        $users = db('GET', 'users') ?? [];
        $foundUser = null;
        $userId = null;

        foreach ($users as $id => $user) {
            if (isset($user['email']) && strtolower($user['email']) === $email) {
                $foundUser = $user;
                $userId = $id; 
                break;
            }
        }

        if ($foundUser && password_verify($password, $foundUser['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $foundUser['email'];
            $_SESSION['displayName'] = $foundUser['displayName'] ?? 'Valued Member';
            $_SESSION['is_admin'] = false; // Regular users

            echo json_encode([
                "success" => true, 
                "redirect" => "dashboard.php"
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Incorrect email or password"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "System error: " . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .hero-gradient { background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent), #0f172a; }
    </style>
</head>
<body class="hero-gradient min-h-screen flex items-center justify-center p-6 text-gray-300">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-white italic tracking-tighter uppercase">AutoMind AI</h1>
            <p class="text-gray-400 mt-2">Sign in to your dashboard</p>
        </div>

        <div class="glass p-8 rounded-[2rem] shadow-2xl">
            <form id="form-signin" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Email Address</label>
                    <input type="email" id="email" required 
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl p-4 text-white focus:ring-2 focus:ring-blue-500 outline-none transition" 
                        placeholder="admin@gmail.com">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Password</label>
                    <input type="password" id="password" required 
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl p-4 text-white focus:ring-2 focus:ring-blue-500 outline-none transition" 
                        placeholder="••••••••">
                </div>

                <button type="submit" id="btn-submit" 
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg transition-all transform active:scale-95 flex items-center justify-center gap-2">
                    Sign In
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-slate-800 text-center">
                <p class="text-sm text-gray-500">
                    Need an account? 
                    <a href="register.php" class="text-blue-400 font-bold hover:underline ml-1">Register here</a>
                </p>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 translate-y-20 opacity-0 transition-all duration-300 z-50">
        <div id="toast-content" class="px-6 py-3 rounded-full border shadow-2xl text-sm font-medium backdrop-blur-md"></div>
    </div>

    <script>
        const form = document.getElementById('form-signin');
        const btn = document.getElementById('btn-submit');
        const toast = document.getElementById('toast');

        function showToast(msg, isError = true) {
            const content = document.getElementById('toast-content');
            content.textContent = msg;
            content.className = isError 
                ? "px-6 py-3 rounded-full border bg-red-500/10 border-red-500/50 text-red-400" 
                : "px-6 py-3 rounded-full border bg-green-500/10 border-green-500/50 text-green-400";
            
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => toast.classList.add('translate-y-20', 'opacity-0'), 3000);
        }

        form.onsubmit = async (e) => {
            e.preventDefault();
            btn.disabled = true;
            btn.innerHTML = "Authenticating...";

            try {
                const res = await fetch("login.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        email: document.getElementById("email").value,
                        password: document.getElementById("password").value
                    })
                });

                const data = await res.json();
                if (data.success) {
                    showToast("Verified. Welcome!", false);
                    setTimeout(() => location.href = data.redirect, 1000);
                } else {
                    showToast(data.message);
                    btn.disabled = false;
                    btn.innerHTML = "Sign In";
                }
            } catch (err) {
                showToast("Connection failure");
                btn.disabled = false;
                btn.innerHTML = "Sign In";
            }
        };
    </script>
</body>
</html>
