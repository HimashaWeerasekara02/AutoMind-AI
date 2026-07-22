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
    <title>My Garage - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border-radius: 1rem; border: 1px solid #334155; transition: all 0.3s ease; position: relative; }
        .glass-card:hover { border-color: #3b82f6; transform: translateY(-4px); }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .modal-animate { animation: modalIn 0.2s ease-out; }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen transition-all duration-300">
        <div class="max-w-7xl mx-auto">
    
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tighter italic uppercase">My Garage</h1>
                    <p class="text-gray-400 mt-1 text-sm font-medium uppercase tracking-wider">Fleet Oversight for <span class="text-blue-500 font-bold"><?php echo htmlspecialchars($displayName); ?></span></p>
                </div>
                <button onclick="openModal()" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-8 rounded-xl transition shadow-xl shadow-blue-900/20 uppercase text-xs tracking-widest">
                    + Register Vehicle
                </button>
            </div>

    
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-8">
                <div class="relative md:col-span-2">
                    <input type="text" id="searchGarage" onkeyup="filterVehicles()" placeholder="Search by make, model, or nickname..." 
                           class="w-full bg-[#1e293b] border border-gray-700 text-white rounded-xl pl-12 pr-4 py-3.5 outline-none focus:border-blue-500 transition-all">
                    <span class="material-symbols-outlined absolute left-4 top-3.5 text-gray-500">search</span>
                </div>
                <button onclick="loadGarage()" class="w-full bg-gray-800 hover:bg-gray-700 text-gray-400 font-bold py-2 px-4 rounded-xl border border-gray-700 transition uppercase text-[10px] tracking-widest">
                    Refresh Garage
                </button>
            </div>

   
            <div id="vehicle-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            </div>
        </div>
    </main>

   
    <div id="vehicleModal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-[110] backdrop-blur-md p-4 overflow-y-auto">
        <div class="bg-[#1e293b] p-8 rounded-3xl w-full max-w-lg shadow-2xl border border-gray-700 modal-animate my-auto">
            <h2 id="modalTitle" class="text-2xl text-white font-black mb-6 italic uppercase tracking-tight">Vehicle Identity</h2>
            <form id="vehicle-form" class="space-y-4">
                <input type="hidden" id="vehicleIdInput" name="id">
                
                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Nickname</label>
                    <input type="text" id="nickInput" name="nickname" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Make</label>
                        <input type="text" id="makeInput" name="make" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 outline-none" placeholder="e.g. Toyota">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Model</label>
                        <input type="text" id="modelInput" name="model" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 outline-none" placeholder="e.g. Axio">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Production Year</label>
                        <input type="number" id="yearInput" name="year" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5 ml-1">License Plate</label>
                        <input type="text" id="plateInput" name="plate" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 outline-none" placeholder="WP ABC-1234">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Odometer (KM)</label>
                        <input type="number" id="odoInput" name="odometer" required class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Fueling Profile</label>
                        <select id="fuelInput" name="fuel" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-3 text-white cursor-pointer focus:border-blue-500 outline-none">
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Vehicle Visual (Optional)</label>
                    <input type="file" name="image" id="fileInput" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer" accept="image/*">
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-4 pt-6">
                    <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-white font-bold uppercase text-[10px] tracking-widest transition-colors py-2">Cancel</button>
                    <button type="submit" id="saveBtn" class="bg-blue-600 hover:bg-blue-500 text-white px-10 py-3 rounded-xl font-black uppercase text-xs tracking-widest transition shadow-xl shadow-blue-900/20">Sync Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let allVehicles = {};
        let isEditMode = false;

        const grid = document.getElementById('vehicle-grid');
        const modal = document.getElementById('vehicleModal');
        const vehicleForm = document.getElementById('vehicle-form');

       
        async function loadGarage() {
            grid.innerHTML = `<div class="col-span-full py-20 text-center text-gray-600 italic uppercase font-bold text-xs tracking-widest animate-pulse">Establishing Telemetry Link...</div>`;
            try {
                const res = await fetch('get_vehicles.php');
                allVehicles = await res.json();
                renderVehicles(allVehicles);
            } catch (err) {
                grid.innerHTML = `<div class="col-span-full text-center py-10 text-red-500 font-bold uppercase text-xs">Link Error: Database Offline</div>`;
            }
        }

        function renderVehicles(vehicles) {
            const keys = Object.keys(vehicles);
            if (keys.length === 0 || (keys.length === 1 && keys[0] === 'error')) {
                grid.innerHTML = `<div class="col-span-full py-20 border-2 border-dashed border-gray-800 rounded-3xl text-center text-gray-600 uppercase font-black tracking-widest text-sm">Garage Empty. Initial Registration Required.</div>`;
                return;
            }

            grid.innerHTML = keys.map(id => {
                const v = vehicles[id];
                const img = v.imageUrl || 'https://via.placeholder.com/400x200?text=AutoMind+AI+Fleet';
                const odo = parseInt(v.odometer) || 0;
                const plate = v.plate || 'N/A';
                const fuelTag = v.fuel || 'Petrol';

                return `
                <div class="glass-card overflow-hidden flex flex-col vehicle-item shadow-2xl" data-search="${v.nickname} ${v.make} ${v.model}">
                    <div class="h-44 bg-gray-800 relative overflow-hidden group">
                        <img src="${img}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 to-transparent"></div>
                        <div class="absolute top-4 left-4">
                            <span class="bg-blue-600 text-[9px] font-black uppercase px-2.5 py-1 rounded-lg text-white shadow-xl border border-white/10 tracking-widest">${fuelTag}</span>
                        </div>
                        <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button onclick="editVehicle('${id}')" class="bg-blue-600/90 hover:bg-blue-500 backdrop-blur-md text-white p-2 rounded-xl transition shadow-xl">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button onclick="deleteVehicle('${id}')" class="bg-red-600/90 hover:bg-red-500 backdrop-blur-md text-white p-2 rounded-xl transition shadow-xl">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start mb-1">
                            <h3 class="text-xl font-black text-white italic uppercase tracking-tighter">${v.nickname}</h3>
                            <span class="text-blue-500 font-black text-[10px] uppercase tracking-widest border border-blue-500/20 px-2 py-0.5 rounded-md bg-blue-500/5">${plate}</span>
                        </div>
                        <p class="text-slate-500 text-[10px] mb-5 uppercase font-bold tracking-widest">${v.year} ${v.make} ${v.model}</p>
                        
                        <div class="flex items-center gap-3 mb-8 bg-slate-900/50 p-3 rounded-2xl border border-white/5">
                            <span class="material-symbols-outlined text-blue-500 text-lg">speed</span>
                            <div>
                                <p class="text-white font-black italic text-lg leading-none">${odo.toLocaleString()}</p>
                                <p class="text-[8px] text-slate-600 font-bold uppercase tracking-widest">Odometer Reading</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-auto">
                            <!-- Stats Link: Fixed camelCase parameter -->
                            <a href="dashboard.php?vehicleId=${id}" target="_self" class="text-center bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-black py-3 rounded-xl transition uppercase tracking-widest shadow-lg border border-white/5">Stats</a>
                            <!-- Glovebox Link: Pass ID and Name to satisfy glovebox.php requirements -->
                            <a href="glovebox.php?vehicle_id=${id}&name=${encodeURIComponent(v.nickname)}" target="_self" class="text-center bg-blue-600 hover:bg-blue-500 text-white text-[10px] font-black py-3 rounded-xl transition uppercase tracking-widest shadow-xl shadow-blue-900/20">Glovebox</a>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }
        
        function filterVehicles() {
            const term = document.getElementById('searchGarage').value.toLowerCase();
            document.querySelectorAll('.vehicle-item').forEach(item => {
                const text = item.getAttribute('data-search').toLowerCase();
                item.style.display = text.includes(term) ? 'flex' : 'none';
            });
        }

        function openModal() {
            isEditMode = false;
            vehicleForm.reset();
            document.getElementById('vehicleIdInput').value = '';
            document.getElementById('modalTitle').innerText = "Register Unit";
            modal.classList.replace('hidden', 'flex');
        }

        function editVehicle(id) {
            isEditMode = true;
            const v = allVehicles[id];
            if (!v) return;
            document.getElementById('modalTitle').innerText = "Modify Signature";
            document.getElementById('vehicleIdInput').value = id;
            document.getElementById('nickInput').value = v.nickname || '';
            document.getElementById('makeInput').value = v.make || '';
            document.getElementById('modelInput').value = v.model || '';
            document.getElementById('yearInput').value = v.year || '';
            document.getElementById('plateInput').value = v.plate || '';
            document.getElementById('odoInput').value = v.odometer || '';
            document.getElementById('fuelInput').value = v.fuel || 'Petrol';
            modal.classList.replace('hidden', 'flex');
        }

        vehicleForm.onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerText = "Synchronizing...";

            const formData = new FormData(vehicleForm);
            if (isEditMode) {
                formData.append('action', 'update');
            }

            try {
                const res = await fetch('manage_vehicle.php', { method: 'POST', body: formData });
                const result = await res.json();
                
                if (result.success) { 
                    closeModal(); 
                    loadGarage(); 
                } else { 
                    alert("Sync Failed: " + result.error); 
                }
            } catch (err) { 
                alert("Critical Link Error. Check Backend Logs."); 
            } finally { 
                btn.disabled = false; 
                btn.innerText = "Sync Data"; 
            }
        };

        async function deleteVehicle(id) {
            if (!confirm("Terminate vehicle record? Recovery is not possible.")) return;
            try {
                const res = await fetch(`manage_vehicle.php?id=${id}&action=delete`);
                const result = await res.json();
                if (result.success) {
                    loadGarage();
                } else { 
                    alert("Termination Failed: " + result.error); 
                }
            } catch (err) { 
                alert("Network Link Severed."); 
            }
        }

        function closeModal() {
            modal.classList.replace('flex', 'hidden');
        }

        loadGarage();
    </script>
</body>
</html>