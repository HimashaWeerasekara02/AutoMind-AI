<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Log - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        input, select { background-color: #0f172a !important; border-color: #334155 !important; color: white !important; }
        input:focus { border-color: #3b82f6 !important; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Fuel Log & Efficiency</h1>
                    <p class="text-gray-400 mt-1">Record refueling events to monitor consumption and costs.</p>
                </div>
                <button id="add-entry-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-lg shadow-indigo-900/20">
                    + Log Fueling
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <!-- Vehicle Selector -->
                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Active Vehicle</label>
                    <select id="vehicle-select" class="w-full rounded-lg px-4 py-3 outline-none transition">
                        <option value="" disabled selected>Loading Vehicles...</option>
                    </select>
                </div>

                <!-- Stats Cards -->
                <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="glass-card p-5">
                        <p class="text-gray-500 text-xs font-bold uppercase">Avg Efficiency</p>
                        <h3 id="stat-mpg" class="text-2xl font-bold text-white mt-1">-- MPG</h3>
                    </div>
                    <div class="glass-card p-5">
                        <p class="text-gray-500 text-xs font-bold uppercase">Total Spent</p>
                        <h3 id="stat-cost" class="text-2xl font-bold text-green-400 mt-1">$0.00</h3>
                    </div>
                    <div class="glass-card p-5">
                        <p class="text-gray-500 text-xs font-bold uppercase">Last Odometer</p>
                        <h3 id="stat-odo" class="text-2xl font-bold text-blue-400 mt-1">0 km</h3>
                    </div>
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
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Refuel Date</label>
                    <input type="date" id="form-date" required class="w-full rounded-lg px-4 py-2.5 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Odometer Reading (km)</label>
                    <input type="number" id="form-odo" required class="w-full rounded-lg px-4 py-2.5 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantity (Gal/L)</label>
                        <input type="number" step="0.01" id="form-qty" required class="w-full rounded-lg px-4 py-2.5 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Total Cost ($)</label>
                        <input type="number" step="0.01" id="form-cost" required class="w-full rounded-lg px-4 py-2.5 outline-none">
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
                const data = await res.json();
                vehicleSelect.innerHTML = '<option value="" disabled selected>Select Vehicle</option>';
                for (const id in data) {
                    const v = data[id];
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = `${v.year} ${v.make} ${v.model}`;
                    vehicleSelect.appendChild(opt);
                }
            } catch (e) { console.error(e); }
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