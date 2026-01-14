<?php 
// This is now a PHP file to help manage server-side logic
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
        /* Prevent layout shift while loading */
        #vehicle-list { min-height: 200px; }
    </style>
</head>

<body class="bg-gray-900 text-gray-300 p-8">

<div id="toast" class="fixed top-8 left-1/2 -translate-x-1/2 z-50">
    <div id="toast-content" class="bg-green-600 text-white px-6 py-3 rounded-lg shadow-2xl">
        <span id="toast-message"></span>
    </div>
</div>

<div class="max-w-6xl mx-auto">
    <header class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white">My Garage</h1>
        <button id="open-modal" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-all transform hover:scale-105 active:scale-95 shadow-lg">
            ➕ Add Vehicle
        </button>
    </header>

    <div class="relative mb-8">
        <input id="garage-search"
               class="w-full p-4 pl-12 rounded-xl bg-gray-800 border border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all shadow-inner"
               placeholder="Search by nickname, make, or model...">
        <span class="absolute left-4 top-4 text-gray-500">🔍</span>
    </div>

    <div id="vehicle-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        </div>

    <div id="modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-40 backdrop-blur-sm">
        <div class="bg-gray-800 p-8 rounded-2xl w-full max-w-md shadow-2xl border border-gray-700">
            <h2 class="text-2xl text-white font-bold mb-6">Add New Vehicle</h2>
            <form id="add-vehicle-form" class="space-y-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Vehicle Nickname</label>
                    <input id="vehicle-nickname" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1" placeholder="e.g. Blue Beast" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Make</label>
                        <input id="vehicle-make" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1" placeholder="Toyota" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Model</label>
                        <input id="vehicle-model" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1" placeholder="RAV4" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Year</label>
                        <input id="vehicle-year" type="number" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1" placeholder="2023" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase ml-1">Plate</label>
                        <input id="vehicle-plate" class="w-full p-3 rounded-lg bg-gray-900 border border-gray-700 focus:border-blue-500 outline-none mt-1" placeholder="Optional">
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-8">
                    <button type="button" id="close-modal" class="px-4 py-2 text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2 rounded-lg font-bold shadow-lg transition">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modal");
    const list  = document.getElementById("vehicle-list");

    // Modal UI Toggles
    document.getElementById("open-modal").onclick = () => {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    };
    
    document.getElementById("close-modal").onclick = () => {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    };

    function toast(msg, err=false){
        const t = document.getElementById("toast");
        document.getElementById("toast-message").innerText = msg;
        document.getElementById("toast-content").className =
            err ? "bg-red-600 text-white px-6 py-3 rounded-lg shadow-xl"
                : "bg-green-600 text-white px-6 py-3 rounded-lg shadow-xl";
        t.classList.add("show");
        setTimeout(() => t.classList.remove("show"), 3000);
    }

    /* 1. LOAD VEHICLES (Using PHP time for cache busting) */
    async function loadVehicles(){
        try {
            list.innerHTML = `
                <div class="col-span-full text-center py-20">
                    <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mb-4"></div>
                    <p class="text-gray-500">Syncing with Firebase...</p>
                </div>`;
            
            // Add a timestamp to the URL so the browser NEVER loads a cached version
            const response = await fetch("get_vehicles.php?cache=" + new Date().getTime());
            
            if (!response.ok) throw new Error("HTTP Error " + response.status);
            
            const data = await response.json();
            list.innerHTML = ""; 

            if(!data || Object.keys(data).length === 0){
                list.innerHTML = `
                    <div class="col-span-full text-center py-20 bg-gray-800/50 rounded-2xl border-2 border-dashed border-gray-700">
                        <p class="text-xl text-gray-500">Your garage is empty.</p>
                        <p class="text-sm text-gray-600">Click "Add Vehicle" to get started.</p>
                    </div>`;
                return;
            }

            for(const id in data){
                const v = data[id];
                list.innerHTML += `
                <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 vehicle-card hover:border-blue-500 transition-all shadow-lg hover:shadow-blue-900/20 group">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-white group-hover:text-blue-400 transition-colors">${v.nickname}</h3>
                            <p class="text-gray-400 font-medium">${v.year} ${v.make} ${v.model}</p>
                        </div>
                        <span class="bg-gray-900 text-blue-400 text-xs font-mono px-3 py-1 rounded-full border border-gray-700">
                            ${v.plate || "NO PLATE"}
                        </span>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg text-sm transition font-semibold">View Docs</button>
                        <button class="bg-gray-700 hover:bg-red-900/50 hover:text-red-400 px-4 py-2 rounded-lg text-sm transition">⚙️</button>
                    </div>
                </div>`;
            }
        } catch (error) {
            console.error(error);
            list.innerHTML = `<div class="col-span-full bg-red-900/20 p-6 rounded-xl border border-red-500 text-red-500 text-center">
                Connection Error: Could not reach get_vehicles.php
            </div>`;
        }
    }

    /* 2. ADD VEHICLE */
    document.getElementById("add-vehicle-form").onsubmit = async e => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerText = "Saving...";

        const payload = {
            nickname: document.getElementById("vehicle-nickname").value,
            make: document.getElementById("vehicle-make").value,
            model: document.getElementById("vehicle-model").value,
            year: document.getElementById("vehicle-year").value,
            plate: document.getElementById("vehicle-plate").value
        };

        try {
            const res = await fetch("add_vehicle.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            
            if(!result.success) throw new Error(result.error);

            modal.classList.add("hidden");
            modal.classList.remove("flex");
            e.target.reset();
            toast("Vehicle added successfully!");
            loadVehicles();
        } catch (error) {
            toast(error.message, true);
        } finally {
            btn.disabled = false;
            btn.innerText = "Save";
        }
    };

    /* 3. SEARCH */
    document.getElementById("garage-search").oninput = e => {
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