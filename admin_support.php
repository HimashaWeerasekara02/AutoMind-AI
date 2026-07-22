<?php

require_once 'db.php';
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: dashboard.php");
    exit();
}

$allTickets = db('GET', 'tickets') ?: [];
$allUsers = db('GET', 'users') ?: [];

$tickets = [];
if (is_array($allTickets)) {
    foreach ($allTickets as $id => $t) {
        $t['id'] = $id;
        $t['userName'] = $allUsers[$t['userId']]['displayName'] ?? 'Unknown User';
        $t['userEmail'] = $allUsers[$t['userId']]['email'] ?? 'N/A';
        $tickets[] = $t;
    }
}

usort($tickets, function($a, $b) {
    if (($a['status'] ?? '') === 'pending' && ($b['status'] ?? '') !== 'pending') return -1;
    if (($a['status'] ?? '') !== 'pending' && ($b['status'] ?? '') === 'pending') return 1;
    return ($b['createdAt'] ?? 0) - ($a['createdAt'] ?? 0);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Command - Support</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #020617; color: #f1f5f9; }
        .admin-card { background: #0f172a; border: 1px solid #1e293b; border-radius: 12px; }
        .status-pending { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
        .status-resolved { color: #10b981; background: rgba(16, 185, 129, 0.1); }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-6 md:p-10 min-h-screen transition-all duration-300">
        <div class="max-w-6xl mx-auto">
            <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                <div>
                    <p class="text-blue-500 text-[10px] font-black uppercase tracking-[0.3em] mb-1">Administrative Hub</p>
                    <h1 class="text-4xl font-black italic uppercase tracking-tighter">Support <span class="text-white/20">Inbox</span></h1>
                </div>
                <a href="admin_vehicles.php" target="_self" class="bg-white/5 hover:bg-white/10 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-widest transition border border-white/5">View Master Fleet</a>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              
                <div class="lg:col-span-1 space-y-4 h-[70vh] overflow-y-auto pr-2 custom-scrollbar">
                    <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Inbound Transmissions</h3>
                    <?php if (empty($tickets)): ?>
                        <p class="text-slate-600 italic text-sm">No tickets found.</p>
                    <?php endif; ?>
                    
                    <?php foreach ($tickets as $t): ?>
                        <div onclick="viewTicket(<?php echo htmlspecialchars(json_encode($t)); ?>)" 
                             class="admin-card p-4 cursor-pointer hover:border-blue-500/50 transition group relative overflow-hidden">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[9px] font-black px-2 py-0.5 rounded uppercase <?php echo ($t['status'] ?? 'pending') === 'pending' ? 'status-pending' : 'status-resolved'; ?>">
                                    <?php echo $t['status'] ?? 'pending'; ?>
                                </span>
                                <span class="text-[9px] text-slate-600 font-mono"><?php echo isset($t['createdAt']) ? date('M d', $t['createdAt']/1000) : ''; ?></span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-200 truncate"><?php echo htmlspecialchars($t['subject']); ?></h4>
                            <p class="text-[10px] text-slate-500 uppercase font-bold mt-1"><?php echo $t['userName']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

               
                <div class="lg:col-span-2">
                    <div id="welcome-view" class="admin-card h-full flex flex-col items-center justify-center p-20 text-center opacity-50 border-dashed">
                        <span class="material-symbols-outlined text-5xl mb-4 text-blue-500">forum</span>
                        <h2 class="text-xl font-bold uppercase italic">Resolution Console</h2>
                        <p class="text-xs text-slate-500 mt-2">Select a transmission from the left to begin the technical resolution process.</p>
                    </div>

                    <div id="active-view" class="hidden admin-card p-8 sticky top-10">
                        <div class="border-b border-white/5 pb-6 mb-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 id="view-subject" class="text-2xl font-black italic uppercase text-white">Subject</h2>
                                    <p id="view-meta" class="text-blue-500 text-[10px] font-black uppercase mt-1"></p>
                                </div>
                                <button onclick="closeTicket()" class="text-slate-500 hover:text-white transition">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white/5 p-5 rounded-xl border border-white/5">
                                <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-2">User Inquiry</p>
                                <p id="view-message" class="text-sm text-slate-300 leading-relaxed italic"></p>
                            </div>

                            <div id="response-container">
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-2 tracking-widest">Transmit Resolution</label>
                                <textarea id="admin-reply" rows="6" class="w-full bg-black border border-slate-800 rounded-xl p-4 text-sm text-white focus:border-blue-500 outline-none resize-none" placeholder="Provide technical guidance or status update..."></textarea>
                                <div class="flex justify-end mt-4">
                                    <button id="submit-reply" onclick="submitResolution()" class="bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-8 rounded-xl text-xs uppercase tracking-widest transition flex items-center gap-2">
                                        <span class="material-symbols-outlined text-sm">bolt</span> Transmit Resolution
                                    </button>
                                </div>
                            </div>

                            <div id="history-container" class="hidden bg-green-500/5 p-5 rounded-xl border border-green-500/20">
                                <p class="text-[9px] font-black text-green-400 uppercase tracking-widest mb-2">Resolution Provided</p>
                                <p id="view-resolution" class="text-sm text-slate-300"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let selectedTicketId = null;

        function viewTicket(t) {
            selectedTicketId = t.id;
            document.getElementById('welcome-view').classList.add('hidden');
            document.getElementById('active-view').classList.remove('hidden');

            document.getElementById('view-subject').innerText = t.subject;
            document.getElementById('view-meta').innerText = `${t.userName} • ${t.category} • ${t.userEmail}`;
            document.getElementById('view-message').innerText = t.message;

            const isResolved = t.status === 'resolved';
            document.getElementById('response-container').classList.toggle('hidden', isResolved);
            document.getElementById('history-container').classList.toggle('hidden', !isResolved);
            
            if (isResolved) {
                document.getElementById('view-resolution').innerText = t.adminResponse || "No response recorded.";
            } else {
                document.getElementById('admin-reply').value = "";
            }
        }

        async function submitResolution() {
            const reply = document.getElementById('admin-reply').value.trim();
            if (!reply) return alert("Please type a response.");

            const btn = document.getElementById('submit-reply');
            btn.disabled = true;
            btn.innerText = "Transmitting...";

            try {
                const res = await fetch('update_ticket.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: selectedTicketId,
                        response: reply,
                        status: 'resolved'
                    })
                });
                
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert("Sync Error: " + data.message);
                    btn.disabled = false;
                    btn.innerText = "Transmit Resolution";
                }
            } catch (e) {
                alert("Network Link Error.");
                btn.disabled = false;
            }
        }

        function closeTicket() {
            document.getElementById('welcome-view').classList.remove('hidden');
            document.getElementById('active-view').classList.add('hidden');
        }
    </script>
</body>
</html>