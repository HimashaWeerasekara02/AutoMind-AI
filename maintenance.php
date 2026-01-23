<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance History - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .table-container { background: #1e293b; border-radius: 0.75rem; overflow: hidden; border: 1px solid #334155; }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .modal-animate { animation: modalIn 0.2s ease-out; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white tracking-tight">Maintenance History</h1>
                    <p class="text-gray-400 mt-1">Manage service logs and repair records for your vehicles.</p>
                </div>
                <button id="add-record-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-lg shadow-blue-900/20">
                    + Add New Record
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <select id="vehicle-select" class="bg-[#1e293b] border border-gray-700 text-white rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <option value="" disabled selected>Loading Vehicles...</option>
                </select>
                
                <div class="relative md:col-span-2">
                    <input type="text" id="search-records" placeholder="Search by description or provider..." class="w-full bg-[#1e293b] border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 outline-none focus:border-blue-500">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <select id="filter-type" class="bg-[#1e293b] border border-gray-700 text-white rounded-lg px-4 py-2 outline-none">
                    <option value="">All Types</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Repair">Repair</option>
                </select>

                <button id="reset-filters" class="bg-gray-800 hover:bg-gray-700 text-gray-400 font-semibold py-2 px-4 rounded-lg border border-gray-700 transition">Reset</button>
            </div>

            <div class="table-container shadow-2xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-gray-500 text-xs font-bold uppercase tracking-wider border-b border-gray-700 bg-gray-800/50">
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4">Odometer</th>
                            <th class="px-6 py-4">Cost</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="history-tbody" class="divide-y divide-gray-700/50">
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Select a vehicle to view logs.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="record-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-[#1e293b] p-8 rounded-2xl w-full max-w-lg shadow-2xl border border-gray-700 modal-animate">
            <h2 id="modal-title" class="text-2xl text-white font-bold mb-6">Record Details</h2>
            <form id="record-form" class="space-y-5">
                <input type="hidden" id="form-id">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Service Date</label>
                        <input type="date" id="form-date" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Type</label>
                        <select id="form-type" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none focus:border-blue-500">
                            <option value="Maintenance">Maintenance</option>
                            <option value="Repair">Repair</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Description</label>
                    <input type="text" id="form-desc" placeholder="e.g., Synthetic Oil Change" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Odometer (km)</label>
                        <input type="number" id="form-odometer" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Total Cost ($)</label>
                        <input type="number" step="0.01" id="form-cost" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Service Provider</label>
                    <input type="text" id="form-provider" placeholder="e.g., City Garage" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white outline-none focus:border-blue-500">
                </div>

                <div class="flex justify-end gap-4 pt-4">
                    <button type="button" onclick="closeModal()" class="px-6 py-2.5 text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-2.5 rounded-lg transition">Save Record</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let allRecords = [];
        const vehicleSelect = document.getElementById('vehicle-select');
        const tbody = document.getElementById('history-tbody');
        const recordModal = document.getElementById('record-modal');

        // 1. Load Vehicles (Handling raw Firebase object)
        async function init() {
            try {
                const res = await fetch('get_vehicles.php'); 
                const data = await res.json();
                
                // Since get_vehicles.php returns a raw object { "id1":{...}, "id2":{...} }
                if(data && Object.keys(data).length > 0) {
                    vehicleSelect.innerHTML = '<option value="" disabled selected>Select Vehicle</option>';
                    
                    // Loop through object keys
                    for (const id in data) {
                        const v = data[id];
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = `${v.year} ${v.make} ${v.model} ${v.nickname ? `(${v.nickname})` : ''}`;
                        vehicleSelect.appendChild(option);
                    }
                } else {
                    vehicleSelect.innerHTML = '<option value="">No vehicles found</option>';
                }
            } catch (e) { 
                console.error("Vehicle load error", e);
                vehicleSelect.innerHTML = '<option value="">Error loading vehicles</option>';
            }
        }

        // 2. Fetch Records using the correct endpoint
        async function fetchRecords() {
            const vId = vehicleSelect.value;
            if(!vId) return;
            
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Loading service logs...</td></tr>`;
            
            try {
                // Changed from get_records.php to get_maintenance.php
                const res = await fetch(`get_maintenance.php?vehicleId=${vId}`);
                const data = await res.json();
                if(data.success) {
                    allRecords = data.records;
                    applyFilters(); 
                }
            } catch (e) { 
                console.error("Fetch error", e); 
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-red-500">Failed to load records.</td></tr>`;
            }
        }

        function applyFilters() {
            const searchQuery = document.getElementById('search-records').value.toLowerCase();
            const typeFilter = document.getElementById('filter-type').value;

            const filtered = allRecords.filter(r => {
                const matchesSearch = (r.description || "").toLowerCase().includes(searchQuery) || 
                                     (r.serviceProvider || "").toLowerCase().includes(searchQuery);
                const matchesType = typeFilter === "" || r.type === typeFilter;
                return matchesSearch && matchesType;
            });

            renderTable(filtered);
        }

        function renderTable(data) {
            if(data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No matching records found.</td></tr>`;
                return;
            }
            tbody.innerHTML = data.map(r => `
                <tr class="hover:bg-gray-800/40 transition group">
                    <td class="px-6 py-4 text-white font-medium">${r.date}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-tight ${r.type === 'Repair' ? 'bg-red-900/30 text-red-400' : 'bg-blue-900/30 text-blue-400'}">
                            ${r.type || 'Service'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-300">
                        <div class="font-semibold text-white">${r.description}</div>
                        <div class="text-xs text-gray-500">${r.serviceProvider}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-400 font-mono">${parseInt(r.odometer || 0).toLocaleString()} km</td>
                    <td class="px-6 py-4 text-green-400 font-bold">$${parseFloat(r.totalCost || 0).toFixed(2)}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-3">
                            <button onclick="editRecord('${r.id}')" class="text-blue-400 hover:text-blue-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="deleteRecord('${r.id}')" class="text-red-400 hover:text-red-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        window.closeModal = () => {
            recordModal.classList.add('hidden');
            recordModal.classList.remove('flex');
        };
        
        document.getElementById('add-record-btn').onclick = () => {
            if(!vehicleSelect.value) return alert("Please select a vehicle from the list first.");
            document.getElementById('record-form').reset();
            document.getElementById('form-id').value = "";
            document.getElementById('modal-title').innerText = "Add Maintenance Log";
            recordModal.classList.remove('hidden');
            recordModal.classList.add('flex');
        };

        window.editRecord = (id) => {
            const r = allRecords.find(rec => rec.id === id);
            if(!r) return;
            document.getElementById('form-id').value = r.id;
            document.getElementById('form-date').value = r.date;
            document.getElementById('form-type').value = r.type || 'Maintenance';
            document.getElementById('form-desc').value = r.description;
            document.getElementById('form-odometer').value = r.odometer;
            document.getElementById('form-cost').value = r.totalCost;
            document.getElementById('form-provider').value = r.serviceProvider;
            document.getElementById('modal-title').innerText = "Update Record";
            recordModal.classList.remove('hidden');
            recordModal.classList.add('flex');
        };

        document.getElementById('record-form').onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById('form-id').value;
            const payload = {
                userId: "user_jane_01", // Placeholder, match your login system
                vehicleId: vehicleSelect.value,
                date: document.getElementById('form-date').value,
                type: document.getElementById('form-type').value,
                description: document.getElementById('form-desc').value,
                odometer: document.getElementById('form-odometer').value,
                totalCost: document.getElementById('form-cost').value,
                serviceProvider: document.getElementById('form-provider').value
            };
            
            // Map to add_maintenance.php or manage_maintenance.php accordingly
            const url = id ? `manage_maintenance.php?id=${id}` : 'add_maintenance.php';
            const method = id ? 'PATCH' : 'POST';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if(result.success) {
                    closeModal();
                    fetchRecords();
                } else {
                    alert("Error: " + result.message);
                }
            } catch (e) { console.error("Save error", e); }
        };

        document.getElementById('search-records').oninput = applyFilters;
        document.getElementById('filter-type').onchange = applyFilters;
        document.getElementById('reset-filters').onclick = () => {
            document.getElementById('search-records').value = "";
            document.getElementById('filter-type').value = "";
            applyFilters();
        };
        vehicleSelect.onchange = fetchRecords;

        init();
    </script>
</body>
</html>