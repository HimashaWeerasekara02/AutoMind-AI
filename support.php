<?php 

require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];


$allTickets = db('GET', 'tickets') ?? [];
$userTickets = [];

foreach ($allTickets as $id => $ticket) {
    if (isset($ticket['userId']) && $ticket['userId'] === $userId) {
        $ticket['id'] = $id;
        $userTickets[] = $ticket;
    }
}


usort($userTickets, function($a, $b) {
    return ($b['createdAt'] ?? 0) - ($a['createdAt'] ?? 0);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; }
        input, textarea, select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: white !important; }
        input:focus, textarea:focus, select:focus { border-color: #6366f1 !important; outline: none; }
        
        #toast { visibility: hidden; opacity: 0; transform: translateY(-20px); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        #toast.show { visibility: visible; opacity: 1; transform: translateY(0); }

        .status-pill { padding: 4px 10px; border-radius: 99px; font-size: 10px; font-weight: 800; text-transform: uppercase; border-width: 1px; }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.3); }
        .status-resolved { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.3); }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen">
        <div id="toast" class="fixed top-8 right-8 bg-indigo-600 text-white px-6 py-4 rounded-xl shadow-2xl z-[110] flex items-center gap-3 border border-white/10">
            <span class="material-symbols-outlined">check_circle</span>
            <span id="toast-message" class="font-bold text-xs uppercase tracking-widest text-white">Ticket Transmitted</span>
        </div>

        <div class="max-w-5xl mx-auto">
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold text-white tracking-tight italic uppercase">Support <span class="text-indigo-500">Hub</span></h1>
                <p class="text-gray-400 mt-2">Submit technical inquiries to the administrative console.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
           
                <div class="lg:col-span-1 space-y-6">
                    <div class="glass-card p-6 border-l-4 border-indigo-500">
                        <h3 class="text-white font-bold mb-4 flex items-center gap-2 italic uppercase text-sm tracking-wider">
                            <span class="material-symbols-outlined text-indigo-400">help</span> Process Flow
                        </h3>
                        <p class="text-gray-500 text-xs italic leading-relaxed">
                            Resolutions provided by admins will appear directly in your history table below.
                        </p>
                    </div>
                </div>

             
                <div class="lg:col-span-2">
                    <div class="glass-card p-8 bg-slate-900/50">
                        <form id="support-form" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-2">Subject</label>
                                    <input type="text" id="subject" placeholder="e.g. Service Sync Issue" class="w-full rounded-xl px-4 py-3" required>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-500 uppercase mb-2">Category</label>
                                    <select id="category" class="w-full rounded-xl px-4 py-3 cursor-pointer">
                                        <option value="Technical">Technical Issue</option>
                                        <option value="Garage">Garage / Odometer</option>
                                        <option value="Feature">Feature Request</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase mb-2">Inquiry Detail</label>
                                <textarea id="message" rows="5" placeholder="Provide technical details..." class="w-full rounded-xl px-4 py-3 resize-none" required></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" id="send-btn" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-12 rounded-xl transition shadow-xl flex items-center justify-center gap-3 uppercase text-xs tracking-widest">
                                    <span class="material-symbols-outlined text-sm">send</span> Transmit Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

           
            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-slate-700 bg-slate-800/30 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-white uppercase italic tracking-tighter text-sm">Transmission History</h2>
                    <span class="material-symbols-outlined text-indigo-400">history</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-900/50 text-[10px] uppercase font-black text-gray-500 tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Inquiry / Resolution</th>
                                <th class="px-6 py-4">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <?php if (empty($userTickets)): ?>
                                <tr><td colspan="3" class="px-6 py-12 text-center text-gray-600 italic uppercase font-bold text-xs">No active tickets</td></tr>
                            <?php else: ?>
                                <?php foreach ($userTickets as $t): 
                                    $isResolved = ($t['status'] ?? 'pending') === 'resolved';
                                ?>
                                    <tr class="hover:bg-slate-800/30 transition-all">
                                        <td class="px-6 py-6 align-top">
                                            <span class="status-pill status-<?php echo $t['status'] ?? 'pending'; ?>">
                                                <?php echo $t['status'] ?? 'pending'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-6">
                                            <p class="text-sm text-gray-300 font-bold uppercase italic tracking-tight mb-2"><?php echo htmlspecialchars($t['subject']); ?></p>
                                            <p class="text-xs text-slate-500 mb-4"><?php echo htmlspecialchars($t['message']); ?></p>
                                            
                                            <?php if ($isResolved && !empty($t['adminResponse'])): ?>
                                                <div class="bg-indigo-600/10 border border-indigo-500/20 p-4 rounded-xl">
                                                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Admin Resolution</p>
                                                    <p class="text-xs text-white italic">"<?php echo htmlspecialchars($t['adminResponse']); ?>"</p>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-6 text-[10px] text-slate-500 font-mono italic align-top">
                                            <?php echo isset($t['createdAt']) ? date('M d, H:i', $t['createdAt'] / 1000) : '--'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('support-form').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('send-btn');
            btn.disabled = true;
            btn.innerHTML = 'Transmitting...';

            const payload = {
                subject: document.getElementById('subject').value,
                category: document.getElementById('category').value,
                message: document.getElementById('message').value
            };

            try {
                const res = await fetch('create_ticket.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if(result.success) {
                    const toast = document.getElementById('toast');
                    toast.classList.add('show');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    alert("Error: " + result.error);
                    btn.disabled = false;
                }
            } catch (e) {
                alert("Technical Link failure.");
                btn.disabled = false;
            }
        };
    </script>
</body>
</html>