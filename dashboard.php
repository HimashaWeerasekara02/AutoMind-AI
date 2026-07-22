<?php 
require_once 'db.php'; 
session_start();

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

$userId = $_SESSION['user_id'];
$displayName = $_SESSION['displayName'] ?? 'User';
$selectedVehicleId = $_GET['vehicleId'] ?? ''; 
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons+Round" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 1.5rem; transition: all 0.3s ease; }
        .metric-glow { filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.4)); }
        select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: white !important; }
        .ai-panel { border-radius: 2rem; box-shadow: 0 25px 50px -12px rgba(30, 58, 138, 0.3); }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 min-h-screen p-4 md:p-8 transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10 pb-6 border-b border-white/5">
                <div>
                    <h1 class="text-white text-3xl md:text-5xl font-black italic uppercase tracking-tighter">Command Center</h1>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                        <p class="text-slate-400 text-[10px] font-bold tracking-widest uppercase">
                            Telemetry: <span id="current-vehicle-name" class="text-blue-400">Connecting...</span>
                        </p>
                    </div>
                </div>
                
                <div class="w-full md:w-auto">
                    <select id="vehicle-switcher" onchange="switchVehicle(this.value)" 
                            class="w-full md:w-72 rounded-xl px-4 py-3 font-bold uppercase text-[10px] tracking-widest shadow-2xl outline-none focus:border-blue-500">
                        <option value="" disabled selected>Loading Garage...</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="glass-card p-8 relative overflow-hidden group">
                            <div class="flex justify-between items-start">
                                <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Predictive Fuel Level</p>
                                <span class="material-icons-round text-blue-500">local_gas_station</span>
                            </div>
                            <div class="flex items-baseline gap-2 mt-4">
                                <span id="fuel-level" class="text-6xl font-black text-white metric-glow italic">--</span>
                                <span class="text-blue-500 font-black italic text-xl">%</span>
                            </div>
                            <div class="mt-8 w-full bg-slate-900 h-2 rounded-full overflow-hidden">
                                <div id="fuel-bar" class="bg-blue-600 h-full w-0 transition-all duration-[1.5s] ease-out"></div>
                            </div>
                            <p id="fuel-calc-method" class="text-[8px] text-slate-600 uppercase font-bold mt-2 tracking-tighter">Initializing sensor logic...</p>
                        </div>

                        <div class="glass-card p-8">
                            <div class="flex justify-between items-start">
                                <p class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Efficiency Index</p>
                                <span class="material-icons-round text-green-500">eco</span>
                            </div>
                            <div class="flex items-baseline gap-2 mt-4">
                                <span id="efficiency-val" class="text-6xl font-black text-white metric-glow italic">--</span>
                                <span class="text-green-500 font-black italic text-xl">KM/L</span>
                            </div>
                            <div class="mt-6 flex items-center gap-2">
                                <span class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">Est. Range:</span>
                                <span id="range-val" class="text-white font-bold text-sm">--</span>
                                <span class="text-slate-500 text-[10px] font-bold uppercase">KM</span>
                            </div>
                        </div>
                    </div>

                    <div id="ai-container" class="ai-panel bg-blue-600 p-8 md:p-10 text-white relative overflow-hidden transition-all duration-700">
                        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                        <div class="flex items-center gap-3 mb-6">
                            <span class="material-icons-round text-xl opacity-80">psychology</span>
                            <p class="font-black text-[10px] uppercase tracking-[0.3em] opacity-80">Neural Analysis Engine</p>
                        </div>
                        <h2 id="ai-insight" class="text-xl md:text-3xl font-black leading-tight italic tracking-tighter uppercase max-w-2xl">Syncing data...</h2>
                        
                        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-white/10">
                            <div>
                                <div class="flex justify-between text-[10px] font-black uppercase tracking-widest mb-3 opacity-80">
                                    <span>Reliability Index</span>
                                    <span id="engine-health-text">--%</span>
                                </div>
                                <div class="w-full bg-black/20 h-2 rounded-full">
                                    <div id="engine-bar" class="bg-white h-full w-0 transition-all duration-[2s]"></div>
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest mb-1 opacity-80">Status</p>
                                <p id="engine-status" class="font-black text-lg italic uppercase tracking-tighter">Initializing...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="glass-card p-8 border-l-4 border-l-blue-500">
                        <h3 class="text-slate-500 text-[10px] font-black mb-8 uppercase tracking-widest">Financial Intelligence</h3>
                        <div class="space-y-6">
                            <div>
                                <p class="text-xs text-slate-400 font-bold mb-1 uppercase">Ownership Spend</p>
                                <p id="total-cost" class="text-3xl font-black text-white italic tracking-tighter">LKR 0</p>
                            </div>
                            <div class="flex justify-between items-end border-t border-white/5 pt-6">
                                <div>
                                    <p class="text-[10px] text-slate-500 font-black uppercase">Service Logs</p>
                                    <p id="service-count" class="text-2xl font-black text-white italic">0</p>
                                </div>
                                <a id="full-report-link" href="#" class="bg-blue-600/10 text-blue-400 px-4 py-2 rounded-lg font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 hover:text-white transition">Report →</a>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-8 border-l-4 border-l-indigo-500">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-slate-500 text-[10px] font-black uppercase tracking-widest">Technical Notes</h3>
                            <a href="notes.php" class="text-indigo-400 text-[10px] font-black uppercase hover:underline">Manage</a>
                        </div>
                        <div id="dashboard-notes-list" class="space-y-4">
                            <p class="text-slate-600 text-[10px] italic">Accessing database logs...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const urlVehicleId = "<?php echo $selectedVehicleId; ?>";
        let garageData = {};

        async function initDashboard() {
            try {
                const res = await fetch('get_vehicles.php');
                const data = await res.json();
                garageData = data; 
                const switcher = document.getElementById('vehicle-switcher');
                
                switcher.innerHTML = '';
                const entries = Object.entries(garageData);
                
                if (entries.length === 0) {
                    document.getElementById('current-vehicle-name').innerText = "Empty Garage";
                    switcher.innerHTML = '<option disabled selected>No Vehicles Found</option>';
                    return;
                }

                entries.forEach(([id, v]) => {
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = `${v.make} ${v.model}`.toUpperCase();
                    switcher.appendChild(opt);
                });

                const targetId = (urlVehicleId && garageData[urlVehicleId]) ? urlVehicleId : entries[0][0];
                switcher.value = targetId;
                switchVehicle(targetId);

            } catch (e) { console.error("Init Error", e); }
        }

        async function switchVehicle(vId) {
            const v = garageData[vId];
            if(!v) return;

            document.getElementById('current-vehicle-name').innerText = (v.nickname || `${v.make} ${v.model}`).toUpperCase();
            document.getElementById('full-report-link').href = `vehicle_report.php?vehicleId=${vId}`;
            
            try {
                const [mRes, fRes, nRes] = await Promise.all([
                    fetch(`get_maintenance.php?vehicleId=${vId}`),
                    fetch(`manage_fuel.php?vehicleId=${vId}`),
                    fetch(`manage_notes.php?vehicleId=${vId}`)
                ]);

                const mData = await mRes.json();
                const fData = await fRes.json();
                const nData = await nRes.json();

               
                let fuelLevel = 0;
                let calcMethod = "Sensor: Data Unavailable";
                const logs = Object.values(fData || {}).sort((a,b) => new Date(b.date) - new Date(a.date));
                const currentOdo = parseFloat(v.odometer) || 0;
                const tankCap = parseFloat(v.tank_capacity) || 45; 

                if (logs.length > 0) {
                    const lastLog = logs[0];
                    const lastLogDate = new Date(lastLog.date);
                    const today = new Date();
                    const diffDays = Math.floor((today - lastLogDate) / (1000 * 60 * 60 * 24));

                    let efficiency = 12; 
                    if (logs.length >= 2) {
                        const l1 = logs[0];
                        const l2 = logs[1];
                        const dist = parseFloat(l1.odometer) - parseFloat(l2.odometer);
                        if (dist > 0 && parseFloat(l1.quantity) > 0) {
                            efficiency = (dist / parseFloat(l1.quantity));
                        }
                    }

                    const odoDiff = currentOdo - parseFloat(lastLog.odometer);
                    
                    if (odoDiff > 0) {
                        const consumed = odoDiff / efficiency;
                        fuelLevel = Math.max(5, Math.round(100 - (consumed / tankCap * 100)));
                        calcMethod = `Pattern: Based on ${odoDiff}km travel since refuel.`;
                    } else if (diffDays === 0) {
                        fuelLevel = 100;
                        calcMethod = "Sensor: Fueling detected recently.";
                    } else {
                        const cycle = 7; 
                        fuelLevel = Math.max(10, Math.round(100 - (diffDays / cycle * 100)));
                        calcMethod = `Pattern: Logged ${diffDays} days ago. Predictive decay applied.`;
                    }
                }

                document.getElementById('fuel-level').innerText = fuelLevel;
                document.getElementById('fuel-bar').style.width = fuelLevel + '%';
                document.getElementById('fuel-calc-method').innerText = calcMethod;

                let totalSpent = 0;
                let currentEff = 0;
                const sortedLogs = Object.values(fData || {}).sort((a, b) => parseFloat(b.odometer) - parseFloat(a.odometer));

                if (sortedLogs.length >= 2) {
                    const latestLog = sortedLogs[0];
                    const previousLog = sortedLogs[1];
                    const distanceTraveled = parseFloat(latestLog.odometer) - parseFloat(previousLog.odometer);
                    const fuelConsumed = parseFloat(latestLog.quantity);

                    if (distanceTraveled > 0 && fuelConsumed > 0) {
                        currentEff = distanceTraveled / fuelConsumed;
                        document.getElementById('efficiency-val').innerText = currentEff.toFixed(1);
                        const remainingLiters = tankCap * (fuelLevel / 100);
                        const estRange = Math.round(currentEff * remainingLiters);
                        document.getElementById('range-val').innerText = estRange;
                    } else {
                        document.getElementById('efficiency-val').innerText = "--";
                        document.getElementById('range-val').innerText = "--";
                    }
                } else {
                    document.getElementById('efficiency-val').innerText = "NEW";
                    document.getElementById('range-val').innerText = "--";
                }

            
                sortedLogs.forEach(l => totalSpent += parseFloat(l.cost || 0));

                let mCount = 0;
                let reliability = 100;
                if (mData.success) {
                    const records = mData.records || [];
                    mCount = records.length;
                    records.forEach(r => totalSpent += parseFloat(r.totalCost || 0));

                    const lastServiceOdo = (records.length > 0) ? parseFloat(records[0].odometer || 0) : 0;
                    const mileageSinceService = currentOdo - lastServiceOdo;

                    if (mileageSinceService > 5000) {
                        reliability = Math.max(40, 100 - (mileageSinceService / 150));
                        document.getElementById('ai-container').className = "ai-panel bg-red-600 p-8 md:p-10 text-white relative overflow-hidden transition-all duration-700";
                        document.getElementById('ai-insight').innerText = `SERVICE OVERDUE BY ${Math.floor(mileageSinceService - 5000)} KM.`;
                        document.getElementById('engine-status').innerText = "URGENT";
                    } else {
                        document.getElementById('ai-container').className = "ai-panel bg-blue-600 p-8 md:p-10 text-white relative overflow-hidden transition-all duration-700";
                        document.getElementById('ai-insight').innerText = "ENGINE PERFORMING OPTIMALLY.";
                        document.getElementById('engine-status').innerText = "OPTIMAL";
                    }
                }

                document.getElementById('engine-health-text').innerText = Math.round(reliability) + '%';
                document.getElementById('engine-bar').style.width = reliability + '%';
                document.getElementById('total-cost').innerText = `LKR ${totalSpent.toLocaleString()}`;
                document.getElementById('service-count').innerText = mCount;

              
                const nEntries = Object.entries(nData || {}).slice(0, 2);
                document.getElementById('dashboard-notes-list').innerHTML = nEntries.length > 0 
                    ? nEntries.map(([id, note]) => `
                        <div class="border-b border-white/5 pb-3 last:border-0">
                            <p class="text-white text-xs font-bold truncate uppercase">${note.title}</p>
                            <p class="text-slate-500 text-[10px] line-clamp-1 italic">${note.content}</p>
                        </div>`).join('')
                    : '<p class="text-slate-600 text-[10px] italic">No technical notes.</p>';

            } catch (e) { console.warn("Telemetry Sync Error", e); }
        }

        window.onload = initDashboard;
    </script>
</body>
</html>