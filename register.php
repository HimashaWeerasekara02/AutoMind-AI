<?php
require_once 'db.php';
session_start();

/**
 * 1. REDIRECT IF ALREADY LOGGED IN
 */
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

/**
 * 2. HANDLE REGISTRATION LOGIC
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    $email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
    $password = isset($data['password']) ? $data['password'] : '';
    $displayName = isset($data['displayName']) ? trim($data['displayName']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $notificationsEnabled = isset($data['notificationsEnabled']) ? (bool)$data['notificationsEnabled'] : false;

    // Clean phone number
    $phone = preg_replace('/[^\d+]/', '', $phone);

    if (empty($email) || empty($password) || empty($displayName) || empty($phone)) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    if (!preg_match('/^\+\d{10,15}$/', $phone)) {
        echo json_encode(["success" => false, "message" => "Invalid phone format. Use +[CountryCode][Number]"]);
        exit;
    }

    try {
        // Check for existing user
        $users = db('GET', 'users') ?? [];
        foreach ($users as $u) {
            if (isset($u['email']) && strtolower($u['email']) === $email) {
                echo json_encode(["success" => false, "message" => "This email is already registered"]);
                exit;
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // --- SECURE USER OBJECT ---
        $newUser = [
            "email"                => $email,
            "password"             => $hashedPassword,
            "displayName"          => $displayName,
            "phone"                => $phone, 
            "role"                 => "user", // DEFAULT ROLE (Hidden from frontend)
            "photoURL"             => "https://ui-avatars.com/api/?name=" . urlencode($displayName) . "&background=random",
            "createdAt"            => date("Y-m-d H:i:s"),
            "notificationsEnabled" => $notificationsEnabled 
        ];

        $result = db('POST', 'users', $newUser);

        if ($result && isset($result['name'])) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #020617; }
        .glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .hero-gradient { background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.1), transparent), radial-gradient(circle at bottom left, #020617, #0f172a); }
    </style>
</head>
<body class="hero-gradient min-h-screen flex items-center justify-center p-6 text-gray-300">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-white italic tracking-tighter uppercase">AutoMind AI</h1>
            <p class="text-gray-500 mt-2">Create your secure account</p>
        </div>

        <div class="glass p-8 rounded-[2rem] shadow-2xl">
            <form id="form-register" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2 ml-1">Full Name</label>
                    <input type="text" id="displayName" required class="w-full bg-slate-900 border border-slate-800 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="John Doe">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2 ml-1">Email Address</label>
                    <input type="email" id="email" required class="w-full bg-slate-900 border border-slate-800 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="name@example.com">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2 ml-1">Phone Number</label>
                    <input type="tel" id="phone" required class="w-full bg-slate-900 border border-slate-800 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="+94771234567">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2 ml-1">Password</label>
                        <input type="password" id="password" required class="w-full bg-slate-900 border border-slate-800 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2 ml-1">Confirm</label>
                        <input type="password" id="confirm_password" required class="w-full bg-slate-900 border border-slate-800 rounded-xl p-4 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center py-3 px-4 bg-slate-900/50 rounded-xl border border-slate-800">
                    <input type="checkbox" id="notificationsEnabled" checked class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-indigo-600">
                    <label for="notificationsEnabled" class="ml-3 text-[11px] text-gray-400">Enable <span class="text-indigo-400 font-bold">WhatsApp Reminders</span></label>
                </div>

                <button type="submit" id="btn-submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg transition-all active:scale-95">
                    Create Account
                </button>
            </form>

            <p class="text-center mt-6 text-xs text-gray-500">
                Already have an account? <a href="login.php" class="text-indigo-400 font-bold hover:underline">Sign In</a>
            </p>
        </div>
    </div>

    <div id="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 translate-y-20 opacity-0 transition-all duration-300 z-50">
        <div id="toast-content" class="px-6 py-3 rounded-full border shadow-2xl text-sm font-medium backdrop-blur-md"></div>
    </div>

    <script>
        const form = document.getElementById('form-register');
        const btn = document.getElementById('btn-submit');
        const toast = document.getElementById('toast');

        function showToast(msg, isError = true) {
            const content = document.getElementById('toast-content');
            content.textContent = msg;
            content.className = isError 
                ? "px-6 py-3 rounded-full border bg-red-500/10 border-red-500/50 text-red-400 shadow-xl"
                : "px-6 py-3 rounded-full border bg-green-500/10 border-green-500/50 text-green-400 shadow-xl";
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => toast.classList.add('translate-y-20', 'opacity-0'), 3000);
        }

        form.onsubmit = async (e) => {
            e.preventDefault();
            if (document.getElementById("password").value !== document.getElementById("confirm_password").value) {
                showToast("Passwords do not match!");
                return;
            }

            btn.disabled = true;
            btn.innerHTML = "Creating...";

            try {
                const res = await fetch("register.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ 
                        displayName: document.getElementById("displayName").value, 
                        email: document.getElementById("email").value, 
                        phone: document.getElementById("phone").value, 
                        password: document.getElementById("password").value,
                        notificationsEnabled: document.getElementById("notificationsEnabled").checked 
                    })
                });

                const data = await res.json();
                if (data.success) {
                    showToast("Success! Redirecting...", false);
                    setTimeout(() => window.location.href = "login.php", 2000);
                } else {
                    showToast(data.message);
                    btn.disabled = false;
                    btn.innerHTML = "Create Account";
                }
            } catch (err) {
                showToast("Network error.");
                btn.disabled = false;
            }
        };
    </script>
</body>
</html>