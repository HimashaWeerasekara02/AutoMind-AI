<?php
require_once 'db.php';
session_start();

// 1. ADMIN SECURITY GUARD
// Only the hardcoded admin from login.php can see this page
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

/**
 * 2. FETCH DATA FROM FIREBASE
 */
try {
    $users = db('GET', 'users') ?? [];
    $totalUsers = count($users);
    
    // Counting total vehicles across all users
    $totalVehicles = 0;
    foreach ($users as $user) {
        if (isset($user['vehicles'])) {
            $totalVehicles += count($user['vehicles']);
        }
    }
} catch (Exception $e) {
    $error = "System Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Console | AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #e2e8f0; }
        .glass-card { background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="min-h-screen">

    <nav class="border-b border-white/5 bg-slate-950/50 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-blue-500 material-symbols-outlined">shield_person</span>
                <span class="font-black uppercase tracking-tighter italic text-xl">AutoMind <span class="text-blue-500">Admin</span></span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-xs font-medium text-slate-400">Welcome, <?php echo $_SESSION['displayName']; ?></span>
                <a href="logout.php" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 px-4 py-2 rounded-lg text-xs font-bold transition">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="glass-card p-6 rounded-3xl">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Total Users</p>
                <h2 class="text-4xl font-black text-white"><?php echo $totalUsers; ?></h2>
            </div>
            <div class="glass-card p-6 rounded-3xl">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Monitored Vehicles</p>
                <h2 class="text-4xl font-black text-blue-500"><?php echo $totalVehicles; ?></h2>
            </div>
            <div class="glass-card p-6 rounded-3xl">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">System Status</p>
                <h2 class="text-lg font-bold text-green-400 flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    Firebase Active
                </h2>
            </div>
        </div>

        <div class="glass-card rounded-[2rem] overflow-hidden">
            <div class="p-8 border-b border-white/5 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Registered Users</h3>
                <button onclick="location.reload()" class="text-slate-400 hover:text-white transition">
                    <span class="material-symbols-outlined">refresh</span>
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/5 text-[10px] uppercase font-bold text-slate-500 tracking-wider">
                        <tr>
                            <th class="px-8 py-4">User Details</th>
                            <th class="px-8 py-4">Phone</th>
                            <th class="px-8 py-4">Role</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if (empty($users)): ?>
                            <tr><td colspan="4" class="p-10 text-center text-slate-500">No users found in database.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $id => $user): ?>
                            <tr class="hover:bg-white/[0.02] transition">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <img src="<?php echo $user['photoURL'] ?? ''; ?>" class="w-10 h-10 rounded-full border border-white/10">
                                        <div>
                                            <p class="text-sm font-bold text-white"><?php echo $user['displayName'] ?? 'N/A'; ?></p>
                                            <p class="text-xs text-slate-500"><?php echo $user['email'] ?? ''; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-sm font-mono text-slate-400">
                                    <?php echo $user['phone'] ?? '---'; ?>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo ($user['role'] ?? '') === 'admin' ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-800 text-slate-400'; ?>">
                                        <?php echo $user['role'] ?? 'User'; ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <button class="text-xs font-bold text-blue-500 hover:text-blue-400">View Data</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>