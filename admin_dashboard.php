<?php

require_once 'db.php';
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: dashboard.php");
    exit();
}

$allUsers     = db('GET', 'users') ?: [];
$allVehicles  = db('GET', 'vehicles') ?: [];
$allTickets   = db('GET', 'tickets') ?: [];

$userCount    = count($allUsers);
$vehicleCount = count($allVehicles);

$pendingTickets = 0;
if (is_array($allTickets)) {
    foreach ($allTickets as $t) {
        if (($t['status'] ?? 'pending') === 'pending') {
            $pendingTickets++;
        }
    }
}

$totalTickets = count($allTickets);
$resolvedTickets = $totalTickets - $pendingTickets;
$resolutionRate = ($totalTickets > 0) ? round(($resolvedTickets / $totalTickets) * 100) : 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Insights - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #f1f5f9; }
        .stat-card { background: #0f172a; border: 1px solid #1e293b; border-radius: 1.5rem; transition: all 0.3s ease; }
        .stat-card:hover { border-color: #3b82f6; transform: translateY(-2px); }
        .insight-gradient { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-6 md:p-10 min-h-screen transition-all duration-300">
        <div class="max-w-6xl mx-auto">
            
            <header class="mb-12">
                <p class="text-blue-500 text-[10px] font-black uppercase tracking-[0.3em] mb-1">Administrative Intelligence</p>
                <h1 class="text-4xl font-black italic uppercase tracking-tighter text-white">System <span class="text-white/20">Insights</span></h1>
            </header>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                
                <div class="stat-card p-8 flex flex-col justify-between h-48">
                    <div class="flex justify-between items-start">
                        <div class="bg-blue-600/10 p-3 rounded-2xl">
                            <span class="material-symbols-outlined text-blue-500 text-3xl">group</span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Users</span>
                    </div>
                    <div>
                        <h3 class="text-5xl font-black italic text-white"><?php echo number_format($userCount); ?></h3>
                        <p class="text-xs text-slate-500 font-bold uppercase mt-1">Active Registrations</p>
                    </div>
                </div>

                <div class="stat-card p-8 flex flex-col justify-between h-48">
                    <div class="flex justify-between items-start">
                        <div class="bg-indigo-600/10 p-3 rounded-2xl">
                            <span class="material-symbols-outlined text-indigo-500 text-3xl">directions_car</span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Global Fleet</span>
                    </div>
                    <div>
                        <h3 class="text-5xl font-black italic text-white"><?php echo number_format($vehicleCount); ?></h3>
                        <p class="text-xs text-slate-500 font-bold uppercase mt-1">Managed Assets</p>
                    </div>
                </div>

                <div class="stat-card p-8 flex flex-col justify-between h-48 border-amber-500/20">
                    <div class="flex justify-between items-start">
                        <div class="bg-amber-600/10 p-3 rounded-2xl">
                            <span class="material-symbols-outlined text-amber-500 text-3xl">mail</span>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Active Tickets</span>
                    </div>
                    <div>
                        <h3 class="text-5xl font-black italic text-amber-500"><?php echo $pendingTickets; ?></h3>
                        <p class="text-xs text-slate-500 font-bold uppercase mt-1">Requiring Response</p>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="stat-card p-8 insight-gradient">
                    <h4 class="text-sm font-black uppercase text-slate-500 mb-6 italic tracking-widest">Platform Efficiency</h4>
                    <div class="flex items-end justify-between">
                        <div class="space-y-2">
                            <p class="text-xs text-slate-400 font-bold uppercase">Ticket Resolution Rate</p>
                            <p class="text-4xl font-black italic text-blue-400"><?php echo $resolutionRate; ?>%</p>
                        </div>
                        <div class="w-32 bg-slate-800 h-2 rounded-full overflow-hidden mb-2">
                            <div class="bg-blue-500 h-full transition-all duration-1000" style="width: <?php echo $resolutionRate; ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="stat-card p-8 bg-blue-600/5">
                    <h4 class="text-sm font-black uppercase text-blue-500 mb-6 italic tracking-widest">Quick Command</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="admin_support.php" target="_self" class="bg-slate-900 border border-white/5 p-4 rounded-xl text-center hover:border-blue-500 transition-all">
                            <p class="text-[10px] font-black text-slate-500 uppercase mb-1">Response Center</p>
                            <span class="text-xs font-bold text-white">Go to Inbox</span>
                        </a>
                        <a href="admin_vehicles.php" target="_self" class="bg-slate-900 border border-white/5 p-4 rounded-xl text-center hover:border-blue-500 transition-all">
                            <p class="text-[10px] font-black text-slate-500 uppercase mb-1">Fleet Logistics</p>
                            <span class="text-xs font-bold text-white">View Master List</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </main>
</body>
</html>