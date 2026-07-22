<?php

require_once 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $target = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) 
              ? "admin_dashboard.php" 
              : "dashboard.php";
    header("Location: $target");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents("php://input"), true);
    $input_email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    if (empty($input_email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Please enter credentials."]);
        exit;
    }

    try {
        $adminEmail = "admin@gmail.com";
        $adminUsername = "admin"; 
        $adminPass = "admin123"; 

        if (($input_email === $adminEmail || $input_email === $adminUsername) && $password === $adminPass) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = "SUPER_ADMIN_01";
            $_SESSION['email'] = $adminEmail;
            $_SESSION['displayName'] = "System Admin";
            $_SESSION['is_admin'] = true; 

            echo json_encode([
                "success" => true, 
                "redirect" => "admin_dashboard.php" 
            ]);
            exit;
        }

        $users = db('GET', 'users') ?? [];
        $foundUser = null;
        $userId = null;

        foreach ($users as $id => $user) {
            if (isset($user['email']) && strtolower($user['email']) === $input_email) {
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
            $_SESSION['is_admin'] = false; 

            echo json_encode([
                "success" => true, 
                "redirect" => "dashboard.php" 
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Incorrect email or password."]);
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .hero-gradient { background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent), #0f172a; }
    </style>
</head>
<body class="hero-gradient min-h-screen flex items-center justify-center p-6 text-gray-300">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black text-white italic tracking-tighter uppercase">AutoMind <span class="text-blue-500">AI</span></h1>
            <p class="text-gray-400 mt-2 font-medium">Cognitive Fleet Management Hub</p>
        </div>

        <div class="glass p-10 rounded-[2.5rem] shadow-2xl">
            <form id="form-signin" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1 tracking-widest">Identifier (Email/User)</label>
                    <input type="text" id="email" required 
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl p-4 text-white focus:ring-2 focus:ring-blue-500 outline-none transition" 
                        placeholder="admin@gmail.com">
                </div>
                
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1 tracking-widest">Security Token (Password)</label>
                    <input type="password" id="password" required 
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl p-4 text-white focus:ring-2 focus:ring-blue-500 outline-none transition" 
                        placeholder="••••••••">
                </div>

                <button type="submit" id="btn-submit" 
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-4 rounded-xl shadow-xl transition-all transform active:scale-95 uppercase text-xs tracking-widest">
                    Synchronize Session
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-slate-800 text-center">
                <p class="text-xs text-gray-500 font-bold uppercase tracking-widest">
                    New Fleet? <a href="register.php" class="text-blue-400 hover:underline ml-1">Register Hub</a>
                </p>
            </div>
        </div>
    </div>

    <div id="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 translate-y-20 opacity-0 transition-all duration-300 z-50">
        <div id="toast-content" class="px-6 py-3 rounded-full border shadow-2xl text-xs font-black uppercase tracking-widest backdrop-blur-md"></div>
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
            btn.innerHTML = "Authenticating Telemetry...";

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
                    showToast("Session Established", false);
                    setTimeout(() => location.href = data.redirect, 800);
                } else {
                    showToast(data.message);
                    btn.disabled = false;
                    btn.innerHTML = "Synchronize Session";
                }
            } catch (err) {
                showToast("Connection Failure");
                btn.disabled = false;
                btn.innerHTML = "Synchronize Session";
            }
        };
    </script>
</body>
</html>