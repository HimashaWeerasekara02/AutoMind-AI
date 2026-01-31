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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border-radius: 1rem; border: 1px solid #334155; transition: all 0.3s ease; position: relative; }
        .glass-card:hover { border-color: #3b82f6; transform: translateY(-4px); }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .modal-animate { animation: modalIn 0.2s ease-out; }
        input:focus, select:focus { border-color: #3b82f6 !important; outline: none; }
        .error-border { border-color: #ef4444 !important; }
        
        /* Floating Action Buttons for the Card */
        .card-actions { opacity: 0; transition: opacity 0.2s ease; }
        .glass-card:hover .card-actions { opacity: 1; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight italic uppercase">My Garage</h1>
                    <p class="text-gray-400 mt-1 text-sm md:text-base">Manage your fleet for <span class="text-blue-400 font-bold"><?php echo htmlspecialchars($displayName); ?></span></p>
                </div>
                <button onclick="openModal()" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-lg">
                    + Register Vehicle
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-8">
                <div class="relative md:col-span-2">
                    <input type="text" id="searchGarage" placeholder="Search by make, model, or plate..." 
                           class="w-full bg-[#1e293b] border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 outline-none">
                    <span class="material-symbols-outlined absolute left-3 top-3 text-gray-500">search</span>
                </div>
                <button onclick="refreshGarage()" class="w-full bg-gray-800 hover:bg-gray-700 text-gray-400 font-semibold py-2 px-4 rounded-lg border border-gray-700 transition">
                    Refresh Garage
                </button>
            </div>

            <div id="vehicle-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                </div>
        </div>
    </main>

    <div id="vehicleModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-[100] backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-[#1e293b] p-6 md:p-8 rounded-2xl w-full max-w-lg shadow-2xl border border-gray-700 modal-animate my-auto">
            <h2 id="modalTitle" class="text-xl md:text-2xl text-white font-bold mb-6 italic uppercase">New Vehicle</h2>
            
            <form id="vehicle-form" class="space-y-4">
                <input type="hidden" id="vehicleIdInput">

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nickname</label>
                    <input type="text" id="nickInput" name="nickname" placeholder="e.g. Blue Beast" required 
                           class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    <p id="nickError" class="text-red-500 text-[10px] font-bold mt-1 hidden uppercase tracking-tighter">This nickname is already in your garage.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Make</label>
                        <input type="text" id="makeInput" name="make" placeholder="Toyota" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Model</label>
                        <input type="text" id="modelInput" name="model" placeholder="Camry" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Year</label>
                        <input type="number" id="yearInput" name="year" placeholder="2024" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">License Plate</label>
                        <input type="text" id="plateInput" name="plate" placeholder="ABC-1234" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Odometer (KM)</label>
                        <input type="number" id="odoInput" name="odometer" placeholder="0" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Fuel Type</label>
                        <select id="fuelInput" name="fuel" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Vehicle Photo</label>
                    <label class="flex items-center justify-center w-full h-24 border-2 border-dashed border-gray-700 rounded-lg cursor-pointer hover:bg-gray-800 transition-colors">
                        <div class="text-center">
                            <span id="file-label" class="text-sm text-gray-500">Click to upload image</span>
                        </div>
                        <input type="file" name="image" id="fileInput" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </label>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4">
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-white py-2">Cancel</button>
                    <button type="submit" id="saveBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-bold transition">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('vehicleModal');
        const grid = document.getElementById('vehicle-grid');
        const searchInput = document.getElementById('searchGarage');
        const nickInput = document.getElementById('nickInput');
        const nickError = document.getElementById('nickError');
        const saveBtn = document.getElementById('saveBtn');
        const modalTitle = document.getElementById('modalTitle');
        const vehicleIdInput = document.getElementById('vehicleIdInput');
        
        let allVehicles = {};
        let isEditMode = false;

        async function loadGarage() {
            try {
                const res = await fetch('get_vehicles.php');
                const data = await res.json();
                allVehicles = data;
                renderVehicles(data);
            } catch (err) {
                grid.innerHTML = `<div class="col-span-full text-center py-10 text-red-400">Error loading garage.</div>`;
            }
        }

        function refreshGarage() {
            searchInput.value = '';
            grid.innerHTML = `<div class="col-span-full py-20 text-center"><p class="text-gray-500 animate-pulse font-bold italic uppercase tracking-widest">Refreshing...</p></div>`;
            loadGarage();
        }

        function renderVehicles(vehicles) {
            const keys = Object.keys(vehicles);
            if (keys.length === 0) {
                grid.innerHTML = `<div class="col-span-full py-20 border-2 border-dashed border-gray-800 rounded-2xl text-center text-gray-500 uppercase font-bold tracking-widest">No vehicles found.</div>`;
                return;
            }

            grid.innerHTML = keys.map(id => {
                const v = vehicles[id];
                const img = v.imageUrl || 'https://via.placeholder.com/400x200?text=AutoMind+AI';
                return `
                <div class="glass-card overflow-hidden flex flex-col">
                    <div class="h-40 bg-gray-800 relative overflow-hidden">
                        <img src="${img}" class="w-full h-full object-cover">
                        <div class="absolute top-3 left-3">
                            <span class="bg-blue-600 text-[10px] font-black uppercase px-2 py-1 rounded text-white shadow-lg">${v.fuel}</span>
                        </div>
                        
                        <div class="card-actions absolute top-3 right-3 flex gap-2">
                            <button onclick="editVehicle('${id}')" class="bg-white/10 hover:bg-blue-600 backdrop-blur-md text-white p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                            <button onclick="deleteVehicle('${id}')" class="bg-white/10 hover:bg-red-600 backdrop-blur-md text-white p-1.5 rounded-lg transition">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-5 flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-bold text-white italic uppercase">${v.nickname}</h3>
                            <span class="text-blue-400 font-bold text-xs">${v.plate || 'NO PLATE'}</span>
                        </div>
                        <p class="text-gray-500 text-xs mb-4 uppercase font-bold">${v.year} ${v.make} ${v.model}</p>
                        
                        <div class="flex items-center gap-2 mb-6 bg-gray-900/50 p-2 rounded-lg border border-gray-800">
                            <span class="material-symbols-outlined text-blue-500 text-sm">speed</span>
                            <span class="text-white font-bold">${parseInt(v.odometer).toLocaleString()}</span>
                            <span class="text-[10px] text-gray-500 font-bold">KM</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="dashboard.php?vehicleId=${id}" class="text-center bg-gray-800 hover:bg-gray-700 text-white text-[10px] font-bold py-2.5 rounded-lg transition uppercase">Stats</a>
                            <a href="glovebox.php?vehicle_id=${id}&name=${encodeURIComponent(v.nickname)}" class="text-center bg-blue-600 hover:bg-blue-500 text-white text-[10px] font-bold py-2.5 rounded-lg transition uppercase">Glovebox</a>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        // --- Logic Functions ---

        function openModal() {
            isEditMode = false;
            modalTitle.innerText = "New Vehicle";
            vehicleIdInput.value = "";
            document.getElementById('vehicle-form').reset();
            document.getElementById('file-label').innerText = "Click to upload image";
            modal.classList.replace('hidden', 'flex');
        }

        function editVehicle(id) {
            isEditMode = true;
            const v = allVehicles[id];
            modalTitle.innerText = "Edit Vehicle";
            vehicleIdInput.value = id;

            // Pre-fill form
            document.getElementById('nickInput').value = v.nickname;
            document.getElementById('makeInput').value = v.make;
            document.getElementById('modelInput').value = v.model;
            document.getElementById('yearInput').value = v.year;
            document.getElementById('plateInput').value = v.plate || '';
            document.getElementById('odoInput').value = v.odometer;
            document.getElementById('fuelInput').value = v.fuel;

            modal.classList.replace('hidden', 'flex');
        }

        async function deleteVehicle(id) {
            if (!confirm("Are you sure you want to delete this vehicle?")) return;
            
            try {
                const res = await fetch(`manage_vehicle.php?id=${id}`, { method: 'DELETE' });
                const result = await res.json();
                if (result.success) refreshGarage();
                else alert(result.error);
            } catch (e) { alert("Error deleting vehicle."); }
        }

        // Form Submission
        document.getElementById('vehicle-form').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const id = vehicleIdInput.value;

            if (isEditMode) {
                // PATCH request (JSON)
                const data = Object.fromEntries(formData.entries());
                try {
                    const res = await fetch(`manage_vehicle.php?id=${id}`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await res.json();
                    if (result.success) { closeModal(); refreshGarage(); }
                    else alert(result.error);
                } catch (e) { alert("Update failed."); }
            } else {
                // POST request (Original add logic)
                try {
                    const res = await fetch('add_vehicle.php', { method: 'POST', body: formData });
                    const result = await res.json();
                    if (result.success) { closeModal(); refreshGarage(); }
                    else alert(result.message);
                } catch (e) { alert("Registration failed."); }
            }
        };

        function closeModal() { modal.classList.replace('flex', 'hidden'); }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                document.getElementById('file-label').innerText = input.files[0].name;
            }
        }

        // Initial Load
        loadGarage();
    </script>
</body>
</html>