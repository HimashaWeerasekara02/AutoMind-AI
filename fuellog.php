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
        .glass-card { background: #1e293b; border-radius: 1rem; border: 1px solid #334155; transition: all 0.3s ease; }
        .glass-card:hover { border-color: #3b82f6; }
        .stat-card { border-left: 4px solid #3b82f6; }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .modal-animate { animation: modalIn 0.2s ease-out; }
        input, select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: white !important; }
        input:focus, select:focus { border-color: #3b82f6 !important; outline: none; }
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

            <div class="glass-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-gray-500 text-[10px] font-bold uppercase border-b border-gray-700 bg-gray-800/50">
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Odometer</th>
                                <th class="px-6 py-4">Fuel</th>
                                <th class="px-6 py-4">Cost</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="fuel-tbody" class="divide-y divide-gray-700">
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 italic uppercase font-bold text-xs tracking-widest">Select a vehicle to sync logs</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="fuel-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100] backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-[#1e293b] p-6 md:p-8 rounded-2xl w-full max-w-md shadow-2xl border border-gray-700 modal-animate my-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 id="modal-title" class="text-xl md:text-2xl text-white font-bold italic uppercase">Refuel Entry</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-white">&times;</button>
            </div>
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

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-white py-2">Cancel</button>
                    <button type="submit" id="save-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-bold transition uppercase text-xs">Save Entry</button>
                </div>
            </form>
        </div>
    </div>

<script>
    const DB_URL = "https://automind-ai-52b33-default-rtdb.asia-southeast1.firebasedatabase.app";
    const AUTH = "K2Np7mgZnDOQJCVjZLcyftVMapBOxZNzyHhJQ87T";
    const vehicleSelect = document.getElementById('vehicle-select');
    const fuelTbody = document.getElementById('fuel-tbody');
    let currentLogs = {};

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
        if (!vId) return;
        
        fuelTbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-blue-500 animate-pulse font-bold uppercase text-xs tracking-widest">Syncing Database...</td></tr>';
        
        try {
            const res = await fetch(`${DB_URL}/fuel_logs/${vId}.json?auth=${AUTH}`);
            const data = await res.json();
            currentLogs = data || {};
            fuelTbody.innerHTML = ''; 
            
            if (!data || Object.keys(data).length === 0) {
                fuelTbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-gray-600 uppercase font-bold text-xs tracking-widest">No logs found</td></tr>';
                resetStatsDisplay();
                return;
            }

            const sortedEntries = Object.entries(data).sort((a, b) => new Date(b[1].date) - new Date(a[1].date));
            
            sortedEntries.forEach(([id, log]) => {
                fuelTbody.innerHTML += `
                    <tr class="hover:bg-gray-800/30 transition border-b border-gray-800">
                        <td class="px-6 py-4 text-white font-bold text-sm">${log.date}</td>
                        <td class="px-6 py-4 text-gray-400 text-sm">${parseInt(log.odometer).toLocaleString()} KM</td>
                        <td class="px-6 py-4 text-gray-400 text-sm">${log.quantity} L</td>
                        <td class="px-6 py-4 text-green-400 font-bold">LKR ${parseFloat(log.cost).toLocaleString()}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <button onclick="editEntry('${id}')" class="text-gray-500 hover:text-blue-400 transition text-xs font-bold uppercase">Edit</button>
                                <button onclick="deleteEntry('${id}')" class="text-gray-500 hover:text-red-500 transition text-xs font-bold uppercase">Del</button>
                            </div>
                        </td>
                    </tr>`;
            });

            calculateStats(sortedEntries);
        } catch (e) { console.error("Fetch failed", e); }
    }

    function calculateStats(entries) {
        let totalCost = 0;
        entries.forEach(e => totalCost += parseFloat(e[1].cost || 0));
        const lastOdo = entries[0][1].odometer;

        let kml = "--";
        if (entries.length >= 2) {
            const dist = parseFloat(entries[0][1].odometer) - parseFloat(entries[1][1].odometer);
            const fuel = parseFloat(entries[0][1].quantity);
            if (dist > 0 && fuel > 0) kml = (dist / fuel).toFixed(2);
        }

        document.getElementById('stat-total-cost').innerText = `LKR ${totalCost.toLocaleString()}`;
        document.getElementById('stat-last-odo').innerHTML = `${parseInt(lastOdo).toLocaleString()} <span class="text-xs font-normal text-gray-500">KM</span>`;
        document.getElementById('stat-avg-fuel').innerHTML = `${kml} <span class="text-xs font-normal text-gray-500">KM/L</span>`;
    }

    function resetStatsDisplay() {
        document.getElementById('stat-total-cost').innerText = "LKR 0";
        document.getElementById('stat-last-odo').innerHTML = `-- <span class="text-xs font-normal text-gray-500">KM</span>`;
        document.getElementById('stat-avg-fuel').innerHTML = `-- <span class="text-xs font-normal text-gray-500">KM/L</span>`;
    }

    window.editEntry = (id) => {
        const log = currentLogs[id];
        document.getElementById('form-id').value = id;
        document.getElementById('form-date').value = log.date;
        document.getElementById('form-odo').value = log.odometer;
        document.getElementById('form-qty').value = log.quantity;
        document.getElementById('form-cost').value = log.cost;
        document.getElementById('modal-title').innerText = "Edit Fuel Log";
        document.getElementById('fuel-modal').classList.replace('hidden', 'flex');
    };

    window.deleteEntry = async (id) => {
        if (!confirm("Remove this entry?")) return;
        await fetch(`${DB_URL}/fuel_logs/${vehicleSelect.value}/${id}.json?auth=${AUTH}`, { method: 'DELETE' });
        fetchFuelLogs();
    };

    document.getElementById('fuel-form').onsubmit = async (e) => {
        e.preventDefault();
        const id = document.getElementById('form-id').value;
        const btn = document.getElementById('save-btn');
        btn.disabled = true; btn.innerText = "Syncing...";

        const payload = {
            date: document.getElementById('form-date').value,
            odometer: parseInt(document.getElementById('form-odo').value),
            quantity: parseFloat(document.getElementById('form-qty').value),
            cost: parseFloat(document.getElementById('form-cost').value),
            timestamp: Date.now()
        };

        const url = id 
            ? `${DB_URL}/fuel_logs/${vehicleSelect.value}/${id}.json?auth=${AUTH}` 
            : `${DB_URL}/fuel_logs/${vehicleSelect.value}.json?auth=${AUTH}`;
        
        const method = id ? 'PATCH' : 'POST';

        const res = await fetch(url, { method, body: JSON.stringify(payload) });
        if (res.ok) {
            closeModal();
            fetchFuelLogs();
        }
        btn.disabled = false; btn.innerText = "Save Entry";
    };

    document.getElementById('add-entry-btn').onclick = () => {
        if(!vehicleSelect.value) return alert("Select a vehicle first.");
        document.getElementById('fuel-form').reset();
        document.getElementById('form-id').value = "";
        document.getElementById('modal-title').innerText = "Refuel Entry";
        document.getElementById('form-date').valueAsDate = new Date();
        document.getElementById('fuel-modal').classList.replace('hidden', 'flex');
    };

    window.closeModal = () => document.getElementById('fuel-modal').classList.replace('flex', 'hidden');
    vehicleSelect.onchange = fetchFuelLogs;
    init();
</script>
</body>
</html>