<?php 
require_once 'db.php'; 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance History - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .table-container { background: #1e293b; border-radius: 0.75rem; border: 1px solid #334155; }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .modal-animate { animation: modalIn 0.2s ease-out; }
        input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); }
        
<<<<<<< HEAD
=======
        /* Mobile vs Desktop Display Logic */
>>>>>>> main
        @media (max-width: 767px) {
            .desktop-only { display: none; }
            .mobile-only { display: block; }
        }
        @media (min-width: 768px) {
            .desktop-only { display: block; }
            .mobile-only { display: none; }
        }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight italic uppercase">Maintenance History</h1>
                    <p class="text-gray-400 mt-1 text-sm md:text-base">Manage service logs and repair records for your fleet.</p>
                </div>
                <button onclick="openAddModal()" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-lg">
                    + Add New Record
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
                <select id="vehicle-select" class="w-full bg-[#1e293b] border border-gray-700 text-white rounded-lg px-4 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>Loading Vehicles...</option>
                </select>
                
                <input type="date" id="filter-date-start" class="w-full bg-[#1e293b] border border-gray-700 text-white rounded-lg px-4 py-2 outline-none" title="Start Date">
                <input type="date" id="filter-date-end" class="w-full bg-[#1e293b] border border-gray-700 text-white rounded-lg px-4 py-2 outline-none" title="End Date">

                <select id="filter-type" class="w-full bg-[#1e293b] border border-gray-700 text-white rounded-lg px-4 py-2 outline-none">
                    <option value="">All Types</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Repair">Repair</option>
                    <option value="Upgrade">Upgrade</option>
                </select>

                <div class="relative w-full">
                    <input type="text" id="search-records" placeholder="Search..." class="w-full bg-[#1e293b] border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 outline-none">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-500">search</span>
                </div>

                <button id="reset-filters" onclick="resetFilters()" class="w-full bg-gray-800 hover:bg-gray-700 text-gray-400 font-semibold py-2 px-4 rounded-lg border border-gray-700 transition">Reset</button>
            </div>

            <div class="table-container shadow-2xl overflow-hidden">
                <div class="overflow-x-auto desktop-only">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-500 text-xs font-bold uppercase tracking-wider border-b border-gray-700 bg-gray-800/50">
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Type</th>
                                <th class="px-6 py-4">Service Provider</th>
                                <th class="px-6 py-4">Description</th>
                                <th class="px-6 py-4">Cost</th>
<<<<<<< HEAD
=======
                                <th class="px-6 py-4 text-center">Bill</th>
>>>>>>> main
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="history-tbody" class="divide-y divide-gray-700/50">
<<<<<<< HEAD
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Please select a vehicle to view logs.</td></tr>
=======
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">Please select a vehicle to view logs.</td></tr>
>>>>>>> main
                        </tbody>
                    </table>
                </div>

                <div id="mobile-history-list" class="mobile-only divide-y divide-gray-700/50">
                    <div class="px-6 py-10 text-center text-gray-500">Please select a vehicle to view logs.</div>
                </div>
            </div>
        </div>
    </main>

    <div id="record-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100] backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-[#1e293b] p-6 md:p-8 rounded-2xl w-full max-w-lg shadow-2xl border border-gray-700 modal-animate my-auto">
            <h2 id="modal-title" class="text-xl md:text-2xl text-white font-bold mb-6 italic uppercase">Record Service</h2>
            <form id="record-form" class="space-y-4">
                <input type="hidden" id="form-editing-id">
<<<<<<< HEAD
=======
                <input type="hidden" id="form-existing-bill">
>>>>>>> main
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Date</label>
                        <input type="date" id="form-date" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Type</label>
                        <select id="form-type" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                            <option value="Maintenance">Maintenance</option>
                            <option value="Repair">Repair</option>
                            <option value="Upgrade">Upgrade</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Service Provider</label>
                    <input type="text" id="form-provider" placeholder="e.g. Toyota Service Center" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Work Description</label>
                    <input type="text" id="form-desc" placeholder="e.g. Oil filter change" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Total Cost (LKR)</label>
                    <input type="number" step="0.01" id="form-cost" placeholder="0.00" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                </div>
<<<<<<< HEAD
=======
                
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Maintenance Bill (Optional)</label>
                    <label class="flex items-center justify-center w-full h-12 border-2 border-dashed border-gray-700 rounded-lg cursor-pointer hover:bg-gray-800 transition-colors">
                        <span id="file-name" class="text-sm text-gray-500 truncate px-4">Click to upload bill</span>
                        <input type="file" id="form-bill" class="hidden" accept="image/*,application/pdf" onchange="document.getElementById('file-name').innerText = this.files[0].name">
                    </label>
                </div>
>>>>>>> main

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-white py-2">Cancel</button>
                    <button type="submit" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-bold transition">Save Log</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const vehicleSelect = document.getElementById('vehicle-select');
        const tbody = document.getElementById('history-tbody');
        const mobileList = document.getElementById('mobile-history-list');
        const recordModal = document.getElementById('record-modal');
        let allRecords = [];

        async function init() {
            try {
                const res = await fetch('get_vehicles.php'); 
                const data = await res.json();
                vehicleSelect.innerHTML = '<option value="" disabled selected>Select Vehicle</option>';
                Object.keys(data).forEach(id => {
                    const v = data[id];
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = `${v.nickname} (${v.make} ${v.model})`;
                    vehicleSelect.appendChild(option);
                });
            } catch (e) { console.error("Load error", e); }
        }

        async function fetchRecords() {
            const vId = vehicleSelect.value;
            if(!vId) return;
<<<<<<< HEAD
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500 animate-pulse">Loading...</td></tr>';
            mobileList.innerHTML = '<div class="px-6 py-10 text-center text-gray-500 animate-pulse">Loading...</div>';
=======
            const loader = '<tr><td colspan="7" class="px-6 py-10 text-center text-gray-500 animate-pulse">Loading...</td></tr>';
            const mLoader = '<div class="px-6 py-10 text-center text-gray-500 animate-pulse">Loading...</div>';
            tbody.innerHTML = loader;
            mobileList.innerHTML = mLoader;
>>>>>>> main
            
            try {
                const res = await fetch(`get_maintenance.php?vehicleId=${vId}`);
                const data = await res.json();
                allRecords = data.success ? data.records : [];
                applyFilters(); 
            } catch (e) { 
<<<<<<< HEAD
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-red-400">Error loading data.</td></tr>';
=======
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-10 text-center text-red-400">Error loading data.</td></tr>';
>>>>>>> main
                mobileList.innerHTML = '<div class="px-6 py-10 text-center text-red-400">Error loading data.</div>';
            }
        }

        function applyFilters() {
            const startDate = document.getElementById('filter-date-start').value;
            const endDate = document.getElementById('filter-date-end').value;
            const type = document.getElementById('filter-type').value;
            const search = document.getElementById('search-records').value.toLowerCase();

            let filtered = allRecords.filter(r => {
                const matchType = !type || r.type === type;
                const matchSearch = !search || r.description.toLowerCase().includes(search) || r.serviceProvider.toLowerCase().includes(search);
                const matchDate = (!startDate || r.date >= startDate) && (!endDate || r.date <= endDate);
                return matchType && matchSearch && matchDate;
            });
<<<<<<< HEAD
=======

>>>>>>> main
            renderData(filtered);
        }

        function renderData(data) {
            if(data.length === 0) {
                const noData = "No matching records found.";
<<<<<<< HEAD
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">${noData}</td></tr>`;
=======
                tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">${noData}</td></tr>`;
>>>>>>> main
                mobileList.innerHTML = `<div class="px-6 py-10 text-center text-gray-500">${noData}</div>`;
                return;
            }

<<<<<<< HEAD
=======
            // Desktop Render
>>>>>>> main
            tbody.innerHTML = data.map(r => `
                <tr class="hover:bg-gray-800/40 border-b border-gray-700/30 transition">
                    <td class="px-6 py-4 text-white whitespace-nowrap">${r.date}</td>
                    <td class="px-6 py-4"><span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-blue-900/30 text-blue-400">${r.type}</span></td>
                    <td class="px-6 py-4 text-gray-300 font-medium">${r.serviceProvider}</td>
                    <td class="px-6 py-4 text-gray-400 text-sm">${r.description}</td>
                    <td class="px-6 py-4 text-green-400 font-bold">LKR ${parseFloat(r.totalCost).toLocaleString()}</td>
<<<<<<< HEAD
=======
                    <td class="px-6 py-4 text-center">
                        ${r.billUrl ? `<a href="${r.billUrl}" target="_blank" class="text-blue-400 hover:text-blue-300 transition inline-flex items-center gap-1"><span class="material-symbols-outlined text-base">visibility</span></a>` : '<span class="text-gray-600">-</span>'}
                    </td>
>>>>>>> main
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-3">
                            <button onclick="editRecord('${r.id}')" class="text-blue-400 hover:text-blue-300 font-bold text-sm">Edit</button>
                            <button onclick="deleteRecord('${r.id}')" class="text-red-500 hover:text-red-400 font-bold text-sm">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');

<<<<<<< HEAD
=======
            // Mobile Render
>>>>>>> main
            mobileList.innerHTML = data.map(r => `
                <div class="p-4 flex flex-col gap-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs text-gray-500">${r.date}</p>
                            <p class="text-white font-bold">${r.serviceProvider}</p>
                        </div>
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-blue-900/30 text-blue-400">${r.type}</span>
                    </div>
                    <p class="text-sm text-gray-400">${r.description}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-green-400 font-bold">LKR ${parseFloat(r.totalCost).toLocaleString()}</span>
                        <div class="flex gap-4">
<<<<<<< HEAD
=======
                            ${r.billUrl ? `<a href="${r.billUrl}" target="_blank" class="text-blue-400"><span class="material-symbols-outlined">description</span></a>` : ''}
>>>>>>> main
                            <button onclick="editRecord('${r.id}')" class="text-blue-400 font-bold text-sm">Edit</button>
                            <button onclick="deleteRecord('${r.id}')" class="text-red-500 font-bold text-sm">Delete</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function openAddModal() {
            if(!vehicleSelect.value) return alert("Please select a vehicle first.");
            document.getElementById('record-form').reset();
            document.getElementById('form-editing-id').value = '';
            document.getElementById('modal-title').innerText = "Record Service";
<<<<<<< HEAD
=======
            document.getElementById('file-name').innerText = "Click to upload bill";
>>>>>>> main
            recordModal.classList.replace('hidden', 'flex');
        }

        function editRecord(id) {
            const r = allRecords.find(rec => rec.id === id);
            if(!r) return;
            document.getElementById('form-editing-id').value = id;
            document.getElementById('form-date').value = r.date;
            document.getElementById('form-type').value = r.type;
            document.getElementById('form-provider').value = r.serviceProvider;
            document.getElementById('form-desc').value = r.description;
            document.getElementById('form-cost').value = r.totalCost;
<<<<<<< HEAD
=======
            document.getElementById('form-existing-bill').value = r.billUrl || '';
>>>>>>> main
            document.getElementById('modal-title').innerText = "Edit Service Record";
            recordModal.classList.replace('hidden', 'flex');
        }

        document.getElementById('record-form').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerText = "Saving...";
<<<<<<< HEAD
            
=======
>>>>>>> main
            const formData = new FormData();
            formData.append('editingId', document.getElementById('form-editing-id').value);
            formData.append('vehicleId', vehicleSelect.value);
            formData.append('date', document.getElementById('form-date').value);
            formData.append('type', document.getElementById('form-type').value);
            formData.append('serviceProvider', document.getElementById('form-provider').value);
            formData.append('description', document.getElementById('form-desc').value);
            formData.append('totalCost', document.getElementById('form-cost').value);
<<<<<<< HEAD

=======
            formData.append('existingBillUrl', document.getElementById('form-existing-bill').value);
            const fileInput = document.getElementById('form-bill');
            if(fileInput.files[0]) formData.append('bill_doc', fileInput.files[0]);
>>>>>>> main
            try {
                const res = await fetch('add_maintenance.php', { method: 'POST', body: formData });
                const result = await res.json();
                if(result.success) {
                    closeModal();
                    fetchRecords();
                } else { alert(result.message); }
            } catch (err) { alert("Error saving record."); }
            finally { btn.disabled = false; btn.innerText = "Save Log"; }
        };

        window.deleteRecord = async (recordId) => {
            if(!confirm("Are you sure?")) return;
            try {
                const res = await fetch('delete_maintenance.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: recordId, vehicleId: vehicleSelect.value })
                });
                const data = await res.json();
                if(data.success) fetchRecords();
            } catch(e) { alert("Error deleting."); }
        }

        function resetFilters() {
            document.getElementById('filter-date-start').value = '';
            document.getElementById('filter-date-end').value = '';
            document.getElementById('filter-type').value = '';
            document.getElementById('search-records').value = '';
            applyFilters();
        }

        function closeModal() { recordModal.classList.replace('flex', 'hidden'); }
        
        document.getElementById('filter-date-start').onchange = applyFilters;
        document.getElementById('filter-date-end').onchange = applyFilters;
        document.getElementById('filter-type').onchange = applyFilters;
        document.getElementById('search-records').oninput = applyFilters;
        vehicleSelect.onchange = fetchRecords;
        init();
    </script>
</body>
</html>