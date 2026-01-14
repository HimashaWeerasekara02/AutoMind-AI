<?php 
require_once 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Garage - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: Inter, sans-serif; }
        #toast { visibility:hidden; opacity:0; transition:0.4s }
        #toast.show { visibility:visible; opacity:1 }
        #vehicle-list { min-height: 200px; }
    </style>
</head>

<body class="bg-gray-900 text-gray-300 p-8">

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="icon-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"></line>
        <line x1="5" y1="12" x2="19" y2="12"></line>
    </symbol>
    <symbol id="icon-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </symbol>
</svg>

<div id="toast" class="fixed top-8 left-1/2 -translate-x-1/2 z-50">
    <div id="toast-content" class="bg-green-600 text-white px-6 py-3 rounded-lg shadow-2xl">
        <span id="toast-message"></span>
    </div>
</div>

<div id="settings-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-md shadow-2xl border border-gray-700">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl text-white font-bold">Vehicle Settings</h2>
            <button onclick="closeSettings()" class="text-gray-500 hover:text-white">✕</button>
        </div>
        <form id="edit-vehicle-form" class="space-y-4">
            <input type="hidden" id="edit-v-id">
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Nickname</label>
                <input id="edit-nickname" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1 text-white" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Plate</label>
                    <input id="edit-plate" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1 text-white">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Year</label>
                    <input id="edit-year" type="number" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1 text-white" required>
                </div>
            </div>
            
            <div class="flex flex-col gap-3 mt-8">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-lg transition">Save Changes</button>
                <button type="button" id="delete-v-btn" class="w-full bg-transparent border border-red-900/50 hover:bg-red-900/20 text-red-500 py-3 rounded-xl font-semibold transition">Delete Vehicle</button>
            </div>
        </form>
    </div>
</div>

<div class="max-w-6xl mx-auto">
    <header class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">My Garage</h1>
            <p class="text-gray-400">Manage your vehicle profiles and important documents.</p>
        </div>
        <button id="open-modal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-2 transition duration-200 shadow-lg transform active:scale-95" data-modal-open="add-vehicle">
            <svg class="w-5 h-5"><use href="#icon-plus"></use></svg>
            <span>Add New Vehicle</span>
        </button>
    </header>

    <div class="relative mb-8">
        <input id="garage-search" class="w-full p-4 pl-12 rounded-xl bg-gray-800 border border-gray-700 focus:border-blue-500 outline-none transition-all shadow-inner text-white" placeholder="Find a vehicle by nickname, make, or model...">
        <span class="absolute left-4 top-4">
            <svg class="w-5 h-5 text-gray-400"><use href="#icon-search"></use></svg>
        </span>
    </div>

    <div id="vehicle-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>

    <div id="modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-40 backdrop-blur-sm">
        <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-md shadow-2xl border border-gray-700">
            <h2 class="text-2xl text-white font-bold mb-6">Add New Vehicle</h2>
            <form id="add-vehicle-form" class="space-y-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Vehicle Nickname</label>
                    <input id="vehicle-nickname" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1" placeholder="e.g. Blue Beast" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input id="vehicle-make" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 outline-none" placeholder="Make" required>
                    <input id="vehicle-model" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 outline-none" placeholder="Model" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input id="vehicle-year" type="number" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 outline-none" placeholder="Year" required>
                    <input id="vehicle-plate" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 outline-none" placeholder="Plate">
                </div>
                <div class="flex justify-end gap-4 mt-8">
                    <button type="button" id="close-modal" class="px-4 py-2 text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-bold">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modal");
    const settingsModal = document.getElementById("settings-modal");
    const list = document.getElementById("vehicle-list");

    document.getElementById("open-modal").onclick = () => modal.classList.replace("hidden", "flex");
    document.getElementById("close-modal").onclick = () => modal.classList.replace("flex", "hidden");
    window.closeSettings = () => settingsModal.classList.replace("flex", "hidden");

    function toast(msg, err=false){
        const t = document.getElementById("toast");
        document.getElementById("toast-message").innerText = msg;
        document.getElementById("toast-content").className = err ? "bg-red-600 text-white px-6 py-3 rounded-lg shadow-xl" : "bg-green-600 text-white px-6 py-3 rounded-lg shadow-xl";
        t.classList.add("show");
        setTimeout(() => t.classList.remove("show"), 3000);
    }

    async function loadVehicles(){
        try {
            const response = await fetch("get_vehicles.php?t=" + Date.now());
            const data = await response.json();
            list.innerHTML = ""; 

            if(!data || Object.keys(data).length === 0){
                list.innerHTML = `<div class="col-span-full text-center py-20 bg-gray-800/50 rounded-2xl border-2 border-dashed border-gray-700"><p class="text-xl text-gray-500">Your garage is empty.</p></div>`;
                return;
            }

            for(const id in data){
                const v = data[id];
                list.innerHTML += `
                <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 vehicle-card hover:border-blue-500 transition-all shadow-lg group">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-white group-hover:text-blue-400 transition-colors">${v.nickname}</h3>
                            <p class="text-gray-400 font-medium">${v.year} ${v.make} ${v.model}</p>
                        </div>
                        <span class="bg-gray-900 text-blue-400 text-xs font-mono px-3 py-1 rounded-full border border-gray-700">${v.plate || "NO PLATE"}</span>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <a href="glovebox.php?vehicle_id=${id}&name=${encodeURIComponent(v.nickname)}" class="flex-1 bg-gray-700 hover:bg-blue-600 text-white py-2 rounded-lg text-sm font-semibold text-center">View Docs</a>
                        <button onclick='openSettings("${id}", ${JSON.stringify(v)})' class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm transition">⚙️</button>
                    </div>
                </div>`;
            }
        } catch (e) { toast("Failed to load vehicles", true); }
    }

    window.openSettings = (id, data) => {
        document.getElementById("edit-v-id").value = id;
        document.getElementById("edit-nickname").value = data.nickname;
        document.getElementById("edit-plate").value = data.plate || "";
        document.getElementById("edit-year").value = data.year;
        settingsModal.classList.replace("hidden", "flex");
    };

    document.getElementById("edit-vehicle-form").onsubmit = async (e) => {
        e.preventDefault();
        const id = document.getElementById("edit-v-id").value;
        const payload = {
            nickname: document.getElementById("edit-nickname").value,
            plate: document.getElementById("edit-plate").value,
            year: document.getElementById("edit-year").value
        };

        const res = await fetch(`manage_vehicle.php?id=${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        if((await res.json()).success) {
            closeSettings();
            loadVehicles();
            toast("Vehicle updated!");
        }
    };

    document.getElementById("delete-v-btn").onclick = async () => {
        if(!confirm("Delete this vehicle? All records will be lost!")) return;
        const id = document.getElementById("edit-v-id").value;
        const res = await fetch(`manage_vehicle.php?id=${id}`, { method: 'DELETE' });
        if((await res.json()).success) {
            closeSettings();
            loadVehicles();
            toast("Vehicle removed", true);
        }
    };

    document.getElementById("add-vehicle-form").onsubmit = async e => {
        e.preventDefault();
        const payload = {
            nickname: document.getElementById("vehicle-nickname").value,
            make: document.getElementById("vehicle-make").value,
            model: document.getElementById("vehicle-model").value,
            year: document.getElementById("vehicle-year").value,
            plate: document.getElementById("vehicle-plate").value
        };
        const res = await fetch("add_vehicle.php", { method: "POST", body: JSON.stringify(payload) });
        if((await res.json()).success) {
            modal.classList.replace("flex", "hidden");
            e.target.reset();
            loadVehicles();
            toast("Vehicle added!");
        }
    };

    // SEARCH LOGIC
    document.getElementById("garage-search").oninput = (e) => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll(".vehicle-card").forEach(card => {
            card.style.display = card.innerText.toLowerCase().includes(q) ? "block" : "none";
        });
    };

    loadVehicles();
});
</script>
</body>
</html>