<?php 
require_once 'db.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['user_id'];
$displayName = $_SESSION['displayName'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Log - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        input, select { background-color: #0f172a !important; border-color: #334155 !important; color: white !important; }
        input:focus { border-color: #3b82f6 !important; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight italic uppercase">Fuel & Efficiency</h1>
                    <p class="text-gray-400 mt-1 text-sm md:text-base">Track LKR per Kilometer and vehicle performance.</p>
                </div>
                <button id="add-entry-btn" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-lg">
                    + Log Refueling
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="glass-card p-5">
                    <label class="block text-[10px] font-bold text-blue-500 uppercase mb-2 tracking-widest">Active Vehicle</label>
                    <select id="vehicle-select" class="w-full rounded-lg px-4 py-2.5 outline-none cursor-pointer font-bold text-sm">
                        <option value="" disabled selected>Loading Garage...</option>
                    </select>
                </div>
                <div class="glass-card p-5 stat-card">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Avg. Efficiency</p>
                    <h3 id="stat-avg-fuel" class="text-xl font-bold text-white mt-1">-- <span class="text-xs font-normal text-gray-500">KM/L</span></h3>
                </div>
                <div class="glass-card p-5 stat-card border-yellow-500">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total Spend</p>
                    <h3 id="stat-total-cost" class="text-xl font-bold text-white mt-1">LKR 0</h3>
                </div>
                <div class="glass-card p-5 stat-card border-green-500">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Last Odometer</p>
                    <h3 id="stat-last-odo" class="text-xl font-bold text-white mt-1">-- <span class="text-xs font-normal text-gray-500">KM</span></h3>
                </div>
            </div>

            <!-- Table -->
            <div class="glass-card overflow-hidden shadow-2xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 text-xs font-bold uppercase tracking-wider border-b border-gray-700 bg-gray-800/50">
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Odometer</th>
                            <th class="px-6 py-4">Volume (L/Gal)</th>
                            <th class="px-6 py-4">Total Cost</th>
                            <th class="px-6 py-4">Efficiency</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="fuel-tbody" class="divide-y divide-gray-700/50">
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">Please select a vehicle.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div id="fuel-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-[#1e293b] p-8 rounded-2xl w-full max-w-md shadow-2xl border border-gray-700">
            <h2 class="text-2xl text-white font-bold mb-6">Log Fuel Event</h2>
            <form id="fuel-form" class="space-y-4">
                <input type="hidden" id="form-id">
                
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Date</label>
                    <input type="date" id="form-date" required class="w-full rounded-lg px-4 py-2">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Odometer (KM)</label>
                    <input type="number" id="form-odo" placeholder="Current Reading" required class="w-full rounded-lg px-4 py-2">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Liters</label>
                        <input type="number" step="0.01" id="form-qty" placeholder="0.00" required class="w-full rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Total Cost (LKR)</label>
                        <input type="number" step="0.01" id="form-cost" placeholder="0.00" required class="w-full rounded-lg px-4 py-2">
                    </div>
                </div>
                <div class="flex justify-end gap-4 pt-4">
                    <button type="button" onclick="closeModal()" class="px-6 py-2.5 text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-2.5 rounded-lg transition">Save Entry</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const vehicleSelect = document.getElementById('vehicle-select');
        const tbody = document.getElementById('fuel-tbody');
        const fuelModal = document.getElementById('fuel-modal');
        let allLogs = [];

    async function init() {
        try {
            const res = await fetch('get_vehicles.php'); 
            const vehicles = await res.json();
            vehicleSelect.innerHTML = '<option value="" disabled selected>Select a Vehicle</option>';
            Object.entries(vehicles).forEach(([id, data]) => {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = `${data.nickname} (${data.make})`;
                vehicleSelect.appendChild(opt);
            });
        } catch (e) { console.error("Initialization failed", e); }
    }

        async function fetchFuelLogs() {
            const vId = vehicleSelect.value;
            if(!vId) return;
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Fetching logs...</td></tr>`;
            
            try {
                const res = await fetch(`manage_fuel.php?vehicleId=${vId}`);
                const data = await res.json();
                allLogs = Object.entries(data || {}).map(([id, val]) => ({id, ...val}));
                allLogs.sort((a, b) => new Date(b.date) - new Date(a.date));
                renderTable();
                calculateStats();
            } catch (e) { console.error(e); }
        }

        function calculateStats() {
            if(allLogs.length < 2) {
                document.getElementById('stat-mpg').innerText = "N/A";
                return;
            }
            const sorted = [...allLogs].sort((a,b) => a.odometer - b.odometer);
            const totalMiles = sorted[sorted.length-1].odometer - sorted[0].odometer;
            const totalFuel = sorted.slice(1).reduce((sum, log) => sum + parseFloat(log.quantity), 0);
            
            const efficiency = (totalMiles / totalFuel).toFixed(2);
            document.getElementById('stat-mpg').innerText = `${efficiency} MPG`;
            
            const totalCost = allLogs.reduce((sum, log) => sum + parseFloat(log.cost), 0);
            document.getElementById('stat-cost').innerText = `$${totalCost.toFixed(2)}`;
            document.getElementById('stat-odo').innerText = `${sorted[sorted.length-1].odometer} km`;
        }

        function renderTable() {
            if(allLogs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No fuel entries found.</td></tr>`;
                return;
            }
            tbody.innerHTML = allLogs.map((log, index) => {
                let eff = "--";
                if(index < allLogs.length - 1) {
                    const prev = allLogs[index + 1];
                    const dist = log.odometer - prev.odometer;
                    eff = (dist / log.quantity).toFixed(2) + " MPG";
                }
                return `
                <tr class="hover:bg-gray-800/40 transition">
                    <td class="px-6 py-4 text-white font-medium">${log.date}</td>
                    <td class="px-6 py-4 text-gray-400 font-mono">${log.odometer} km</td>
                    <td class="px-6 py-4 text-gray-300">${log.quantity}</td>
                    <td class="px-6 py-4 text-green-400 font-bold">$${parseFloat(log.cost).toFixed(2)}</td>
                    <td class="px-6 py-4"><span class="text-xs font-bold text-indigo-400">${eff}</span></td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="deleteEntry('${log.id}')" class="text-red-500 hover:text-red-300 transition">🗑️</button>
                    </td>
                </tr>
            `}).join('');
        }

        window.closeModal = () => fuelModal.classList.replace('flex', 'hidden');
        document.getElementById('add-entry-btn').onclick = () => {
            if(!vehicleSelect.value) return alert("Select a vehicle first");
            document.getElementById('fuel-form').reset();
            fuelModal.classList.replace('hidden', 'flex');
        };

        document.getElementById('fuel-form').onsubmit = async (e) => {
            e.preventDefault();
            const payload = {
                vehicleId: vehicleSelect.value,
                date: document.getElementById('form-date').value,
                odometer: parseInt(document.getElementById('form-odo').value),
                quantity: parseFloat(document.getElementById('form-qty').value),
                cost: parseFloat(document.getElementById('form-cost').value)
            };
            const res = await fetch('manage_fuel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if((await res.json()).success) {
                closeModal();
                fetchFuelLogs();
            }
        };

        async function deleteEntry(id) {
            if(!confirm("Delete this entry?")) return;
            await fetch(`manage_fuel.php?id=${id}`, { method: 'DELETE' });
            fetchFuelLogs();
        }

        vehicleSelect.onchange = fetchFuelLogs;
        init();
    </script>
</body>
</html>