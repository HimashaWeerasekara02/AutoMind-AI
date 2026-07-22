<?php 

require_once 'db.php'; 
session_start();

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$vId = $_GET['vehicleId'] ?? '';

if (!$vId) { 
    header("Location: dashboard.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligence Report - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #f1f5f9; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 24px; transition: all 0.3s ease; }
        #score-circle { transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1); }
        .animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 min-h-screen p-6 md:p-10">
        
        <div class="max-w-5xl mx-auto">
            
            <div class="mb-8">
                <a href="dashboard.php" target="_self" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-300 transition-all group">
                    <span class="material-symbols-outlined text-sm group-hover:-translate-x-1 transition-transform">arrow_back</span> 
                    Dashboard
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="glass-card p-8">
                    <p class="text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Maintenance</p>
                    <h3 id="cost-maint" class="text-2xl font-black italic">LKR 0</h3>
                </div>
                <div class="glass-card p-8">
                    <p class="text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Fuel Consumption</p>
                    <h3 id="cost-fuel" class="text-2xl font-black italic">LKR 0</h3>
                </div>
                <div class="glass-card p-8 bg-blue-600/10 border-blue-500/40">
                    <p class="text-[10px] font-bold text-blue-500 uppercase mb-2 tracking-widest">Lifecycle Cost</p>
                    <h3 id="cost-total" class="text-3xl font-black italic text-blue-400">LKR 0</h3>
                </div>
            </div>


            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">

                <div class="glass-card p-10 flex flex-col items-center justify-center text-center shadow-xl">
                    <div class="relative flex items-center justify-center">
                        <svg class="w-48 h-48 transform -rotate-90">
                            <circle cx="96" cy="96" r="85" stroke="#0f172a" stroke-width="14" fill="transparent" />
                            <circle id="score-circle" cx="96" cy="96" r="85" stroke="#3b82f6" stroke-width="14" fill="transparent" 
                                    stroke-dasharray="534" stroke-dashoffset="534" stroke-linecap="round" />
                        </svg>
                        <span id="score-text" class="absolute text-4xl font-black italic text-white">--%</span>
                    </div>
                    <h4 id="status-label" class="mt-8 text-sm font-black uppercase text-blue-500 tracking-widest italic animate-pulse-slow">Analyzing Telemetry...</h4>
                    <p class="text-[10px] text-slate-500 mt-2 font-bold uppercase tracking-widest">Reliability Score</p>
                </div>


                <div id="ai-panel" class="bg-blue-600 rounded-[40px] p-10 text-white shadow-2xl transition-all duration-700">
                    <div class="flex items-center gap-3 mb-8">
                        <span class="material-symbols-outlined text-xl">psychology</span>
                        <h3 class="text-[11px] font-black uppercase tracking-[0.3em] opacity-80">Predictive AI Insights</h3>
                    </div>
                    <div id="alerts-list" class="space-y-4">
                        <div class="bg-white/10 p-5 rounded-3xl border border-white/10 animate-pulse text-xs font-bold italic">
                            Synchronizing neural data...
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        async function runReport() {
            const vId = "<?php echo $vId; ?>";
            try {
                const res = await fetch(`get_report_data.php?vehicleId=${vId}`);
                const data = await res.json();
                
                if (data.success) {
                    const r = data.report;
                    
                    const maint = parseFloat(r.financials.maint) || 0;
                    const fuel = parseFloat(r.financials.fuel) || 0;
                    const total = maint + fuel;

                    document.getElementById('cost-maint').innerText = `LKR ${maint.toLocaleString()}`;
                    document.getElementById('cost-fuel').innerText = `LKR ${fuel.toLocaleString()}`;
                    document.getElementById('cost-total').innerText = `LKR ${total.toLocaleString()}`;
                    
                    const score = parseInt(r.reliability_score);
                    document.getElementById('score-text').innerText = score + '%';
                    document.getElementById('status-label').innerText = r.health_status;
                    document.getElementById('status-label').classList.remove('animate-pulse-slow');
                    
                    const offset = 534 - (534 * score / 100);
                    document.getElementById('score-circle').style.strokeDashoffset = offset;

                    const panel = document.getElementById('ai-panel');
                    const label = document.getElementById('status-label');
                    const circle = document.getElementById('score-circle');

                    if (score < 50) {
                        panel.className = "bg-red-600 rounded-[40px] p-10 text-white shadow-2xl transition-all duration-700";
                        label.className = "mt-8 text-sm font-black uppercase text-red-500 tracking-widest italic";
                        circle.style.stroke = "#ef4444";
                    } else if (score < 80) {
                        panel.className = "bg-amber-600 rounded-[40px] p-10 text-white shadow-2xl transition-all duration-700";
                        label.className = "mt-8 text-sm font-black uppercase text-amber-500 tracking-widest italic";
                        circle.style.stroke = "#f59e0b";
                    } else {
                        panel.className = "bg-blue-600 rounded-[40px] p-10 text-white shadow-2xl transition-all duration-700";
                        label.className = "mt-8 text-sm font-black uppercase text-blue-500 tracking-widest italic";
                        circle.style.stroke = "#3b82f6";
                    }

                    document.getElementById('alerts-list').innerHTML = r.alerts.map(a => `
                        <div class="bg-white/15 p-5 rounded-3xl border border-white/5 flex gap-4 items-start shadow-inner">
                            <span class="material-symbols-outlined text-sm opacity-50">data_thresholding</span>
                            <span class="text-xs font-bold italic tracking-tight">${a}</span>
                        </div>
                    `).join('');
                } else {
                    document.getElementById('alerts-list').innerHTML = `<p class="text-white/70 italic text-xs font-bold">${data.message}</p>`;
                }
            } catch (err) { 
                document.getElementById('alerts-list').innerHTML = `<p class="text-white/70 italic text-xs font-bold">ERROR: Database link interrupted.</p>`;
            }
        }
        
        window.onload = runReport;
    </script>
</body>
</html>