<?php 
require_once 'db.php'; 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
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
        .stat-value { transition: all 0.3s ease; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight italic uppercase">Fuel & Efficiency</h1>
                    <p class="text-gray-400 mt-1">Monitor consumption and LKR costs per kilometer.</p>
                </div>
                <button onclick="openModal()" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-lg flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">local_gas_station</span> Log Refueling
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="glass-card p-5">
                    <label class="block text-[10px] font-bold text-blue-500 uppercase mb-2 tracking-widest">Active Vehicle</label>
                    <select id="vehicle-select" onchange="fetchFuelLogs()" class="w-full rounded-lg px-4 py-2.5 outline-none font-bold text-sm cursor-pointer">
                        <option value="" disabled selected>Loading Garage...</option>
                    </select>
                </div>
                <div class="glass-card p-5 border-l-4 border-blue-500">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Avg. Efficiency</p>
                    <h3 id="stat-avg-fuel" class="text-xl font-bold text-white mt-1">-- <span class="text-xs font-normal text-gray-400">KM/L</span></h3>
                </div>
                <div class="glass-card p-5 border-l-4 border-yellow-500">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total Spend</p>
                    <h3 id="stat-total-cost" class="text-xl font-bold text-white mt-1">LKR 0</h3>
                </div>
                <div class="glass-card p-5 border-l-4 border-green-500">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Last Odometer</p>
                    <h3 id="stat-last-odo" class="text-xl font-bold text-white mt-1">-- <span class="text-xs font-normal text-gray-400">KM</span></h3>
                </div>
            </div>

            <div class="glass-card overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-500 text-xs font-bold uppercase border-b border-gray-700 bg-gray-800/50">
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Odometer</th>
                                <th class="px-6 py-4">Volume (L)</th>
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
        </div>
    </main>

    <div id="fuel-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-[#1e293b] p-8 rounded-2xl w-full max-w-md shadow-2xl border border-gray-700">
            <h2 id="modal-title" class="text-2xl text-white font-bold mb-6">Log Fuel Event</h2>
            <form id="fuel-form" class="space-y-4">
                <input type="hidden" id="form-id">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Date</label>
                    <input type="date" id="form-date" required class="w-full rounded-lg px-4 py-2 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Odometer (KM)</label>
                    <input type="number" id="form-odo" placeholder="Current reading" required class="w-full rounded-lg px-4 py-2 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Liters</label>
                        <input type="number" step="0.01" id="form-qty" placeholder="0.00" required class="w-full rounded-lg px-4 py-2 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Cost (LKR)</label>
                        <input type="number" step="0.01" id="form-cost" placeholder="0.00" required class="w-full rounded-lg px-4 py-2 outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-4 pt-4">
                    <button type="button" onclick="closeModal()" class="px-6 py-2.5 text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-2.5 rounded-lg transition">Save Entry</button>
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
                    const opt = new Option(`${data.nickname} (${data.make})`, id);
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
                
                // Convert object to array and sort by Odometer descending
                allLogs = Object.entries(data).map(([id, val]) => ({id, ...val}));
                allLogs.sort((a, b) => b.odometer - a.odometer);
                
                renderTable();
                calculateStats();
            } catch (e) { console.error(e); }
        }

        function calculateStats() {
            const statAvg = document.getElementById('stat-avg-fuel');
            const statCost = document.getElementById('stat-total-cost');
            const statOdo = document.getElementById('stat-last-odo');

            if(allLogs.length === 0) {
                statAvg.innerHTML = `-- <span class="text-xs font-normal text-gray-400">KM/L</span>`;
                statCost.innerText = "LKR 0";
                statOdo.innerText = "-- KM";
                return;
            }

            const totalSpend = allLogs.reduce((sum, log) => sum + parseFloat(log.cost), 0);
            statCost.innerText = `LKR ${totalSpend.toLocaleString()}`;
            statOdo.innerText = `${allLogs[0].odometer.toLocaleString()} KM`;

            if(allLogs.length >= 2) {
                const latest = allLogs[0].odometer;
                const earliest = allLogs[allLogs.length - 1].odometer;
                const totalDistance = latest - earliest;
                
                const totalFuel = allLogs.slice(0, -1).reduce((sum, log) => sum + parseFloat(log.quantity), 0);
                
                const avg = (totalDistance / totalFuel).toFixed(2);
                statAvg.innerHTML = `${avg} <span class="text-xs font-normal text-gray-400">KM/L</span>`;
            } else {
                statAvg.innerHTML = `Need 2+ logs`;
            }
        }

        function renderTable() {
            if(allLogs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No fuel records yet.</td></tr>`;
                return;
            }

            tbody.innerHTML = allLogs.map((log, index) => {
                let eff = "--";
                if(index < allLogs.length - 1) {
                    const dist = log.odometer - allLogs[index + 1].odometer;
                    eff = (dist / log.quantity).toFixed(2) + " KM/L";
                }
                return `
                <tr class="hover:bg-gray-800/40 transition">
                    <td class="px-6 py-4 text-white font-medium">${log.date}</td>
                    <td class="px-6 py-4 text-gray-400 font-mono">${log.odometer.toLocaleString()} km</td>
                    <td class="px-6 py-4 text-gray-300">${log.quantity} L</td>
                    <td class="px-6 py-4 text-green-400 font-bold">LKR ${parseFloat(log.cost).toLocaleString()}</td>
                    <td class="px-6 py-4"><span class="text-xs font-bold text-indigo-400 bg-indigo-400/10 px-2 py-1 rounded">${eff}</span></td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-3">
                            <button onclick="editEntry('${log.id}')" class="text-blue-400 hover:text-blue-200 transition">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button onclick="deleteEntry('${log.id}')" class="text-red-500 hover:text-red-300 transition">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }

        function openModal(editData = null) {
            if(!vehicleSelect.value) return alert("Please select a vehicle first");
            const form = document.getElementById('fuel-form');
            form.reset();
            
            if(editData) {
                document.getElementById('modal-title').innerText = "Edit Fuel Entry";
                document.getElementById('form-id').value = editData.id;
                document.getElementById('form-date').value = editData.date;
                document.getElementById('form-odo').value = editData.odometer;
                document.getElementById('form-qty').value = editData.quantity;
                document.getElementById('form-cost').value = editData.cost;
            } else {
                document.getElementById('modal-title').innerText = "Log Fuel Event";
                document.getElementById('form-id').value = "";
                document.getElementById('form-date').valueAsDate = new Date();
            }
            fuelModal.classList.replace('hidden', 'flex');
        }

        function closeModal() { fuelModal.classList.replace('flex', 'hidden'); }

        function editEntry(id) {
            const entry = allLogs.find(l => l.id === id);
            if(entry) openModal(entry);
        }

        document.getElementById('fuel-form').onsubmit = async (e) => {
            e.preventDefault();
            const payload = {
                vehicleId: vehicleSelect.value,
                id: document.getElementById('form-id').value,
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
            
            const result = await res.json();
            if(result.success) {
                closeModal();
                fetchFuelLogs();
            } else {
                alert("Error: " + result.error);
            }
        };

        async function deleteEntry(id) {
            if(!confirm("Are you sure you want to delete this fuel log?")) return;
            const res = await fetch(`manage_fuel.php?id=${id}&vehicleId=${vehicleSelect.value}`, { method: 'DELETE' });
            const result = await res.json();
            if(result.success) fetchFuelLogs();
        }

        init();
    </script>
</body>
</html>