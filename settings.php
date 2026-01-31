<?php 
require_once 'db.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

/**
 * PHOTO LOADING LOGIC
 */
$users = db('GET', 'users') ?? [];
$userData = $users[$userId] ?? [];

$displayName = $userData['displayName'] ?? $_SESSION['displayName'] ?? 'User';
$userEmail = $userData['email'] ?? $_SESSION['email'] ?? '';
$userPhone = $userData['phone'] ?? ''; 
$notificationsEnabled = isset($userData['notificationsEnabled']) ? (bool)$userData['notificationsEnabled'] : false;

if (!empty($userData['photoURL'])) {
    $photoURL = $userData['photoURL'];
} elseif (!empty($_SESSION['photoURL'])) {
    $photoURL = $_SESSION['photoURL'];
} else {
    $photoURL = "https://ui-avatars.com/api/?name=" . urlencode($displayName) . "&background=random&color=fff";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border-radius: 1rem; border: 1px solid #334155; }
        
        input[type="text"], input[type="tel"], input[type="password"], input[type="email"] { 
            background-color: #0f172a !important; 
            border: 1px solid #334155 !important; 
            color: white !important;
            font-weight: 600;
        }
        input:focus { border-color: #3b82f6 !important; outline: none; }
        
        #toast { visibility: hidden; opacity: 0; transform: translateY(-20px); transition: all 0.3s ease; z-index: 1000; }
        #toast.show { visibility: visible; opacity: 1; transform: translateY(0); }
        
        /* Custom Toggle Switch Style matching My Garage theme */
        .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { 
            position: absolute; cursor: pointer; inset: 0; background-color: #334155; 
            transition: .4s; border-radius: 34px; 
        }
        .slider:before { 
            position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; 
            background-color: white; transition: .4s; border-radius: 50%; 
        }
        input:checked + .slider { background-color: #2563eb; }
        input:checked + .slider:before { transform: translateX(20px); }
    </style>
</head>
<body class="antialiased">
    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen transition-all duration-300">
        <div id="toast" class="fixed top-5 right-5 bg-blue-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 border border-blue-400/20">
            <span class="material-symbols-outlined" id="toast-icon">verified</span>
            <span id="toast-message" class="font-bold uppercase tracking-widest text-xs">Settings saved!</span>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tighter italic uppercase">Account Protocol</h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base font-medium uppercase tracking-wider">Identity Management for <span class="text-blue-500 font-bold"><?php echo htmlspecialchars($displayName); ?></span></p>
            </div>
            
            <div class="glass-card overflow-hidden shadow-2xl">
                <form id="settings-form">
                    <div class="p-6 md:p-10 space-y-10">
                        <div class="flex flex-col md:flex-row items-center gap-8 bg-gray-900/40 p-6 rounded-2xl border border-gray-800">
                            <div class="relative group">
                                <img id="avatar-preview" src="<?php echo $photoURL; ?>" 
                                     class="w-32 h-32 rounded-full object-cover border-4 border-gray-700 shadow-xl transition group-hover:opacity-75 duration-300">
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition pointer-events-none">
                                    <span class="material-symbols-outlined text-white text-3xl">photo_camera</span>
                                </div>
                            </div>
                            <div class="text-center md:text-left">
                                <label for="file-input" class="cursor-pointer bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-lg text-xs font-black transition uppercase tracking-widest inline-block shadow-lg">
                                    Change Identity Image
                                </label>
                                <p class="text-[10px] text-gray-500 mt-4 font-bold uppercase tracking-widest leading-relaxed">System Requirement: Square JPG/PNG <br> Allocation: 1MB Max</p>
                            </div>
                            <input type="file" id="file-input" class="hidden" accept="image/*">
                        </div>

                        <div class="space-y-6">
                            <h3 class="text-sm font-black text-blue-500 italic uppercase tracking-widest flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">person</span> Profile Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">Full Name</label>
                                    <input type="text" id="display-name" value="<?php echo htmlspecialchars($displayName); ?>" class="w-full rounded-xl px-5 py-3.5 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">WhatsApp Interface</label>
                                    <input type="tel" id="phone" value="<?php echo htmlspecialchars($userPhone); ?>" 
                                           pattern="^\+\d{10,15}$"
                                           class="w-full rounded-xl px-5 py-3.5 text-sm italic" 
                                           placeholder="+94771234567" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1 text-gray-600">Primary Email (System Locked)</label>
                                    <input type="email" value="<?php echo htmlspecialchars($userEmail); ?>" class="w-full rounded-xl px-5 py-3.5 text-sm opacity-40 cursor-not-allowed bg-gray-900 border-dashed" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <div class="flex items-center justify-between bg-blue-600/5 p-5 rounded-2xl border border-blue-500/20">
                                <div class="flex items-center gap-4">
                                    <div class="bg-blue-600/20 p-2 rounded-lg">
                                        <span class="material-symbols-outlined text-blue-500">notifications_active</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-black text-xs uppercase tracking-widest italic">WhatsApp Reminders</p>
                                        <p class="text-[10px] text-gray-500 font-bold uppercase mt-0.5">Automated Neural Alerts for Fleet Maintenance</p>
                                    </div>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" id="notifications-enabled" <?php echo $notificationsEnabled ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-900/30 p-6 md:p-10 border-t border-gray-700/50 space-y-8">
                        <h3 class="text-sm font-black text-indigo-400 italic uppercase tracking-widest flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">security</span> Security Protocol
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">Current Password</label>
                                <input type="password" id="current-password" placeholder="••••••••" class="w-full rounded-xl px-5 py-3.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 ml-1">New Access Key</label>
                                <input type="password" id="new-password" placeholder="MIN 6 CHARACTERS" class="w-full rounded-xl px-5 py-3.5 text-sm">
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4">
                            <button type="submit" id="save-btn" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-black py-4 px-12 rounded-xl transition shadow-xl flex items-center justify-center gap-3 uppercase text-[11px] tracking-[0.2em]">
                                <span class="material-symbols-outlined text-lg">sync_alt</span>
                                Commit Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const fileInput = document.getElementById('file-input');
        const avatarPreview = document.getElementById('avatar-preview');
        const settingsForm = document.getElementById('settings-form');
        const saveBtn = document.getElementById('save-btn');
        const toast = document.getElementById('toast');

        function showToast(message, isError = false) {
            const toastMsg = document.getElementById('toast-message');
            const toastIcon = document.getElementById('toast-icon');
            toastMsg.textContent = message;
            toast.style.backgroundColor = isError ? '#dc2626' : '#2563eb';
            toastIcon.textContent = isError ? 'report' : 'verified';
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        fileInput.onchange = (e) => {
            const [file] = fileInput.files;
            if (file) {
                if (file.size > 1024 * 1024) {
                    showToast("Buffer Overload: Max 1MB", true);
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => { avatarPreview.src = e.target.result; };
                reader.readAsDataURL(file);
            }
        };

        settingsForm.onsubmit = async (e) => {
            e.preventDefault();
            
            const newPass = document.getElementById('new-password').value;
            const currentPass = document.getElementById('current-password').value;

            if (newPass && !currentPass) {
                showToast("Auth Required: Enter current password", true);
                return;
            }

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="animate-spin text-sm">↻</span> Executing...';

            const payload = {
                displayName: document.getElementById('display-name').value,
                phone: document.getElementById('phone').value,
                photoURL: avatarPreview.src,
                notificationsEnabled: document.getElementById('notifications-enabled').checked,
                currentPassword: currentPass,
                newPassword: newPass
            };

            try {
                const res = await fetch('update_profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const result = await res.json();
                
                if (result.success) {
                    showToast("System Protocol Updated");
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message || "Sequence Failed", true);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<span class="material-symbols-outlined text-lg">sync_alt</span> Commit Changes';
                }
            } catch (err) {
                showToast("Link Failure: Server Error", true);
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<span class="material-symbols-outlined text-lg">sync_alt</span> Commit Changes';
            }
        };
    </script>
</body>
</html>