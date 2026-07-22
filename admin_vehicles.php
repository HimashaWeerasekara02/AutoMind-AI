<?php

require_once 'db.php';
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: dashboard.php");
    exit();
}

$allVehicles = db('GET', 'vehicles') ?: [];
$allUsers = db('GET', 'users') ?: [];

$fleet = [];
if (is_array($allVehicles)) {
    foreach ($allVehicles as $vId => $v) {
        $ownerId = $v['userId'] ?? 'Unknown';
        
        $v['id'] = $vId;
        $v['ownerName'] = $allUsers[$ownerId]['displayName'] ?? 'Unknown User';
        $v['ownerEmail'] = $allUsers[$ownerId]['email'] ?? 'N/A';
        
        $rawOdo = $v['odometer'] ?? $v['currentOdometer'] ?? $v['mileage'] ?? $v['km'] ?? 0;
       
        $cleanOdo = preg_replace('/[^0-9.]/', '', (string)$rawOdo);
        $v['displayOdometer'] = (float)$cleanOdo;
        
        $v['displayPlate'] = $v['plate'] ?? $v['licensePlate'] ?? $v['plateNumber'] ?? 'N/A';
        
        $fleet[] = $v;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Fleet - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #f1f5f9; }
        .glass-card { background: #0f172a; border: 1px solid #1e293b; border-radius: 12px; overflow: hidden; }
        tr:hover { background: rgba(59, 130, 246, 0.03); }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #020617; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-6 md:p-10 min-h-screen transition-all duration-300">
        <div class="max-w-6xl mx-auto">
            <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                <div>
                    <p class="text-blue-500 text-[10px] font-black uppercase tracking-[0.3em] mb-1">Fleet Management</p>
                    <h1 class="text-4xl font-black italic uppercase tracking-tighter">Master <span class="text-white/20">Garage</span></h1>
                </div>
                <div class="flex gap-3">
                    <a href="admin_support.php" target="_self" class="bg-blue-600 hover:bg-blue-700 px-6 py-2 rounded-lg text-xs font-bold uppercase tracking-widest transition shadow-lg flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">mail</span> Support Inbox
                    </a>
                </div>
            </header>

            <div class="glass-card shadow-2xl">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-white/5 text-[10px] font-black uppercase text-slate-500 tracking-widest border-b border-white/5">
                            <tr>
                                <th class="px-6 py-4">Owner</th>
                                <th class="px-6 py-4">Vehicle Identity</th>
                                <th class="px-6 py-4">Plate</th>
                                <th class="px-6 py-4">Odometer</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if(empty($fleet)): ?>
                                <tr>
                                    <td colspan="5" class="p-20 text-center text-slate-600 italic">
                                        <span class="material-symbols-outlined text-4xl mb-2 block">no_transport</span>
                                        No vehicles registered on platform.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($fleet as $v): ?>
                            <tr class="transition">
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <p class="text-sm font-bold text-white"><?php echo htmlspecialchars($v['ownerName']); ?></p>
                                        <p class="text-[10px] text-slate-500 uppercase font-mono"><?php echo htmlspecialchars($v['ownerEmail']); ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col">
                                        <p class="text-sm font-bold text-slate-300 italic"><?php echo htmlspecialchars($v['nickname'] ?: 'Unit'); ?></p>
                                        <p class="text-[10px] text-blue-500 uppercase font-bold"><?php echo htmlspecialchars("{$v['year']} {$v['make']} {$v['model']}"); ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="font-mono text-xs text-slate-400 bg-black/30 px-2 py-1 rounded border border-white/10">
                                        <?php echo htmlspecialchars($v['displayPlate']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-bold text-white">
                                            <?php echo number_format($v['displayOdometer']); ?>
                                        </span>
                                        <span class="text-[9px] text-slate-500 font-black uppercase">KM</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-[9px] font-black px-2 py-1 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 uppercase tracking-tighter">
                                        Active
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <footer class="mt-8 text-center">
                <p class="text-[10px] text-slate-600 font-bold uppercase tracking-[0.2em]">Total Fleet Capacity: <?php echo count($fleet); ?> Units</p>
            </footer>
        </div>
    </main>
</body>
</html>