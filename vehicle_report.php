<?php 
require_once 'db.php'; 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$vId = $_GET['vehicleId'] ?? null;
if (!$vId) { header("Location: dashboard.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligence Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f1f5f9; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 24px; transition: all 0.3s ease; }
        .glass-card:hover { border-color: #3b82f6; }
        .stat-value { font-weight: 900; font-style: italic; text-transform: uppercase; }
    </style>
</head>
<body class="p-6">

    <main class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="dashboard.php" class="inline-flex items-center text-xs font-bold uppercase tracking-widest text-slate-500 hover:text-blue-500 transition-all group">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Command Link
            </a>
        </div>

        <header class="mb-10">
            <h1 class="text-5xl font-black italic uppercase tracking-tighter text-white">Data <span class="text-blue-600">Report</span></h1>
            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.4em] mt-2 ml-1">Unit ID: <?php echo htmlspecialchars($vId); ?></p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="glass-card p-8">
                <p class="text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Maintenance Only</p>
                <h3 id="cost-maint" class="text-2xl font-bold">LKR 0.00</h3>
            </div>
            <div class="glass-card p-8">
                <p class="text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Fuel Expenses</p>
                <h3 id="cost-fuel" class="text-2xl font-bold">LKR 0.00</h3>
            </div>
            <div class="glass-card p-8 bg-blue-600/10 border-blue-500/40">
                <p class="text-[10px] font-bold text-blue-500 uppercase mb-2 tracking-widest">Total Lifecycle Cost</p>
                <h3 id="cost-total" class="text-3xl stat-value text-white">LKR 0.00</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="glass-card p-10 flex flex-col items-center justify-center">
                <div class="relative flex items-center justify-center">
                    <svg class="w-32 h-32 transform -rotate-90">
                        <circle cx="64" cy="64" r="58" stroke="#0f172a" stroke-width="10" fill="transparent" />
                        <circle id="score-circle" cx="64" cy="64" r="58" stroke="#3b82f6" stroke-width="10" fill="transparent" stroke-dasharray="364.4" stroke-dashoffset="364.4" stroke-linecap="round" style="transition: all 1.5s ease-in-out;"/>
                    </svg>
                    <span id="score-text" class="absolute text-3xl font-black italic">--%</span>
                </div>
                <p id="status-label" class="mt-6 text-xs font-black uppercase text-blue-500 tracking-widest">Syncing AI Status</p>
            </div>

            <div class="bg-blue-600 rounded-[40px] p-10 text-white shadow-2xl shadow-blue-900/40">
                <h3 class="text-[10px] font-black uppercase tracking-widest mb-6 opacity-70">Predictive Alerts</h3>
                <div id="alerts-list" class="space-y-4"></div>
            </div>
        </div>
    </main>

    <script>
        async function runReport() {
            try {
                const res = await fetch(`get_report_data.php?vehicleId=<?php echo $vId; ?>`);
                const data = await res.json();
                
                if (data.success) {
                    const r = data.report;
                    
                    // Populate Costs
                    document.getElementById('cost-maint').innerText = `LKR ${r.financials.maint}`;
                    document.getElementById('cost-fuel').innerText = `LKR ${r.financials.fuel}`;
                    document.getElementById('cost-total').innerText = `LKR ${r.financials.total}`;
                    
                    // Populate Score
                    document.getElementById('score-text').innerText = r.reliability_score;
                    document.getElementById('status-label').innerText = r.health_status;
                    
                    // Animate Progress
                    const score = parseInt(r.reliability_score);
                    document.getElementById('score-circle').style.strokeDashoffset = 364.4 - (364.4 * score / 100);

                    // Populate Alerts
                    const alerts = document.getElementById('alerts-list');
                    alerts.innerHTML = r.alerts.length ? r.alerts.map(a => `
                        <div class="bg-white/10 p-4 rounded-2xl text-sm font-bold border border-white/5 italic flex gap-3">
                            <span>➔</span> ${a}
                        </div>
                    `).join('') : '<p class="opacity-60 italic text-sm">System parameters stable. No alerts.</p>';
                }
            } catch (err) { console.error("Report link failed."); }
        }
        window.onload = runReport;
    </script>
</body>
</html>