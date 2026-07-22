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
    <title>Maintenance History - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .table-container { background: #1e293b; border-radius: 0.75rem; border: 1px solid #334155; }
        input, select, textarea { background-color: #0f172a !important; border-color: #334155 !important; color: white !important; outline: none !important; }
        input:focus, select:focus, textarea:focus { border-color: #3b82f6 !important; }
        .modal-animate { animation: fadeIn 0.2s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white italic uppercase tracking-tight">Maintenance Logs</h1>
                    <p class="text-gray-400">Manage your vehicle service history and digital receipts.</p>
                </div>
                <button onclick="openAddModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-lg flex items-center gap-2">
                    <span class="material-symbols-outlined">add_circle</span> Add Record
                </button>
            </div>

            <div class="bg-[#1e293b] p-4 rounded-xl border border-gray-700 mb-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase px-1">Vehicle</label>
                        <select id="vehicle-select" onchange="fetchRecords()" class="w-full rounded-lg px-3 py-2 outline-none">
                            <option value="" disabled selected>Select Vehicle...</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase px-1">Service Type</label>
                        <select id="filter-type" onchange="applyFilters()" class="w-full rounded-lg px-3 py-2 outline-none">
                            <option value="all">All Types</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Repair">Repair</option>
                            <option value="AI Audit">AI Audit</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase px-1">From Date</label>
                        <input type="date" id="filter-date-start" onchange="applyFilters()" class="w-full rounded-lg px-3 py-2">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase px-1">Search Description</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-2 text-gray-500 text-sm">search</span>
                            <input type="text" id="search-records" oninput="applyFilters()" placeholder="Filter logs..." class="w-full rounded-lg pl-10 pr-4 py-2 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container shadow-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-500 text-xs font-bold uppercase tracking-wider border-b border-gray-700 bg-gray-800/50">
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4">Type</th>
                                <th class="px-6 py-4">Work Description</th>
                                <th class="px-6 py-4">Cost (LKR)</th>
                                <th class="px-6 py-4 text-center">Receipt</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="history-tbody" class="divide-y divide-gray-700/50">
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">Select a vehicle to view logs.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="record-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100] p-4 backdrop-blur-sm">
        <div class="bg-[#1e293b] p-8 rounded-2xl w-full max-w-lg border border-gray-700 modal-animate">
            <h2 id="modal-title" class="text-xl text-white font-bold mb-6 italic uppercase tracking-tight">Record Service</h2>
            <form id="record-form" class="space-y-4" enctype="multipart/form-data">
                <input type="hidden" id="form-editing-id" name="editingId">
                <input type="hidden" id="existing-bill-path" name="existingBillPath">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Service Date</label>
                        <input type="date" id="form-date" name="date" required class="w-full rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Service Type</label>
                        <select id="form-type" name="type" class="w-full rounded-lg px-4 py-2">
                            <option value="Maintenance">Maintenance</option>
                            <option value="Repair">Repair</option>
                            <option value="AI Audit">AI Audit</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Work Description</label>
                    <textarea id="form-desc" name="description" rows="4" placeholder="Explain what was done..." required class="w-full rounded-lg px-4 py-2 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Total Cost (LKR)</label>
                        <input type="number" step="0.01" id="form-cost" name="totalCost" placeholder="0.00" required class="w-full rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Digital Bill</label>
                        <input type="file" id="form-bill-file" name="bill_doc" accept="image/*,.pdf" class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white cursor-pointer">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-700">
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-white py-2 px-4 uppercase text-[10px] font-bold tracking-widest">Cancel</button>
                    <button type="submit" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold transition uppercase text-xs tracking-widest shadow-xl">Save Record</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const vehicleSelect = document.getElementById('vehicle-select');
        const tbody = document.getElementById('history-tbody');
        const modal = document.getElementById('record-modal');
        const recordForm = document.getElementById('record-form');
        let allRecords = [];

        async function init() {
            try {
                const res = await fetch('get_vehicles.php');
                if (!res.ok) throw new Error('Load failed');
                const data = await res.json();
                vehicleSelect.innerHTML = '<option value="" disabled selected>Select Vehicle</option>';
                Object.entries(data || {}).forEach(([id, v]) => {
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = (v.nickname || `${v.make} ${v.model}`).toUpperCase();
                    vehicleSelect.appendChild(opt);
                });
            } catch (e) { 
                console.error("Init Error", e); 
                vehicleSelect.innerHTML = '<option value="" disabled>Error loading garage</option>';
            }
        }

        async function fetchRecords() {
            const vId = vehicleSelect.value;
            if(!vId) return;
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500 italic animate-pulse uppercase text-[10px] tracking-widest">Syncing with database...</td></tr>';
            try {
                const res = await fetch(`get_maintenance.php?vehicleId=${vId}`);
                if (!res.ok) throw new Error('History link broken');
                const data = await res.json();
                
                if (data.records) {
                    if (Array.isArray(data.records)) {
                        allRecords = data.records;
                    } else {
                        allRecords = Object.entries(data.records).map(([id, val]) => ({ 
                            ...val, 
                            id: val.id || id 
                        }));
                    }
                } else {
                    allRecords = [];
                }
                
                applyFilters();
            } catch (e) { 
                console.error("Fetch Error", e);
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-red-500 font-bold uppercase text-xs">Failed to load history.</td></tr>'; 
            }
        }

        function applyFilters() {
            const search = document.getElementById('search-records').value.toLowerCase();
            const typeFilter = document.getElementById('filter-type').value;
            const startDate = document.getElementById('filter-date-start').value;

            const filtered = allRecords.filter(r => {
                const matchesSearch = (r.description || '').toLowerCase().includes(search);
                const matchesType = (typeFilter === 'all' || r.type === typeFilter);
                const matchesDate = (!startDate || r.date >= startDate);
                return matchesSearch && matchesType && matchesDate;
            });
            render(filtered);
        }

        function render(data) {
            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500 italic text-sm">No maintenance records found.</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(r => {
                const billPath = r.billUrl || r.billImage;
                const billBtn = billPath ? 
                    `<button onclick="window.open('${billPath}', '_blank')" class="bg-slate-700 hover:bg-slate-600 text-blue-400 px-3 py-1 rounded-md text-[10px] font-black uppercase flex items-center gap-1 mx-auto border border-blue-400/20 transition-all"><span class="material-symbols-outlined text-sm">attachment</span> View</button>` : 
                    `<span class="text-gray-600 text-[9px] uppercase font-black">No File</span>`;

                return `
                <tr class="hover:bg-gray-800/40 border-b border-gray-700/30 transition">
                    <td class="px-6 py-4 text-white font-medium whitespace-nowrap text-sm italic">${r.date}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase ${getTypeClass(r.type)}">${r.type}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-300 text-sm font-semibold max-w-xs truncate lg:max-w-md" title="${r.description}">${r.description}</div>
                    </td>
                    <td class="px-6 py-4 text-green-400 font-black whitespace-nowrap">LKR ${(parseFloat(r.totalCost || r.cost) || 0).toLocaleString()}</td>
                    <td class="px-6 py-4 text-center">${billBtn}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-1">
                            <button onclick="editRecord('${r.id}')" class="p-2 text-blue-400 hover:bg-blue-500/10 rounded-lg transition" title="Modify Record">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </button>
                            <button onclick="deleteRecord('${r.id}')" class="p-2 text-red-500 hover:bg-red-500/10 rounded-lg transition" title="Delete Permanentely">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }

        function getTypeClass(type) {
            switch(type) {
                case 'Maintenance': return 'bg-blue-900/30 text-blue-400 border border-blue-500/20';
                case 'Repair': return 'bg-amber-900/30 text-amber-400 border border-amber-500/20';
                case 'AI Audit': return 'bg-purple-900/30 text-purple-400 border border-purple-500/20';
                default: return 'bg-gray-700 text-gray-300';
            }
        }

        window.openAddModal = () => {
            if(!vehicleSelect.value) return alert("Select a target vehicle first.");
            recordForm.reset();
            document.getElementById('form-editing-id').value = '';
            document.getElementById('existing-bill-path').value = '';
            document.getElementById('modal-title').innerText = "Register Service Event";
            modal.classList.replace('hidden', 'flex');
        };

        window.editRecord = (id) => {
            const r = allRecords.find(rec => String(rec.id) === String(id));
            if(!r) {
                console.error("Record search failed for ID:", id);
                return;
            }
            document.getElementById('form-editing-id').value = id;
            document.getElementById('form-date').value = r.date || '';
            document.getElementById('form-type').value = r.type || 'Maintenance';
            document.getElementById('form-desc').value = r.description || '';
            document.getElementById('form-cost').value = r.totalCost || r.cost || 0;
            document.getElementById('existing-bill-path').value = r.billUrl || r.billImage || '';
            document.getElementById('modal-title').innerText = "Modify Service Event";
            modal.classList.replace('hidden', 'flex');
        };

        recordForm.onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submit-btn');
            btn.disabled = true; btn.innerText = "Synchronizing...";

            const formData = new FormData(recordForm);
            formData.append('vehicleId', vehicleSelect.value);

            try {
                const res = await fetch('add_maintenance.php', { method: 'POST', body: formData });
                const result = await res.json();
                if(result.success) { 
                    closeModal(); 
                    fetchRecords(); 
                } else { 
                    alert("Sync Error: " + result.message); 
                }
            } catch (err) { 
                console.error("Submit Error", err);
                alert("Communication link failure."); 
            }
            finally { btn.disabled = false; btn.innerText = "Save Record"; }
        };

        window.deleteRecord = async (id) => {
            if(!confirm("Terminate this record from history?")) return;
            try {
                const res = await fetch(`manage_maintenance.php?id=${id}`, { method: 'DELETE' });
                const result = await res.json();
                if(result.success) fetchRecords();
                else alert("Deletion Failed: " + result.message);
            } catch (e) { alert("Delete link error."); }
        };

        window.closeModal = () => modal.classList.replace('flex', 'hidden');
        init();
    </script>
</body>
</html>