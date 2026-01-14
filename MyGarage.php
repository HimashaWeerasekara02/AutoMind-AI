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
    </style>
</head>

<body class="bg-gray-900 text-gray-300 p-8">

<div id="toast" class="fixed top-8 left-1/2 -translate-x-1/2 z-50">
    <div id="toast-content" class="bg-green-600 text-white px-6 py-3 rounded-lg">
        <span id="toast-message"></span>
    </div>
</div>

<div class="max-w-6xl mx-auto">

    <h1 class="text-3xl font-bold text-white mb-6">My Garage</h1>

    <button id="open-modal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-6 transition">
        ➕ Add Vehicle
    </button>

    <input id="garage-search"
           class="w-full mb-6 p-3 rounded bg-gray-800 border border-gray-700 focus:border-blue-500 outline-none"
           placeholder="Search vehicle by nickname, make, or model...">

    <div id="vehicle-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        </div>

    <div id="modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-40">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-md shadow-xl border border-gray-700">
            <h2 class="text-xl text-white font-bold mb-4">Add New Vehicle</h2>
            <form id="add-vehicle-form" class="space-y-3">
                <input id="vehicle-nickname" class="w-full p-2 rounded bg-gray-700 border border-gray-600" placeholder="Nickname (e.g. My Daily)" required>
                <input id="vehicle-make" class="w-full p-2 rounded bg-gray-700 border border-gray-600" placeholder="Make (e.g. Toyota)" required>
                <input id="vehicle-model" class="w-full p-2 rounded bg-gray-700 border border-gray-600" placeholder="Model (e.g. RAV4)" required>
                <input id="vehicle-year" type="number" class="w-full p-2 rounded bg-gray-700 border border-gray-600" placeholder="Year" required>
                <input id="vehicle-plate" class="w-full p-2 rounded bg-gray-700 border border-gray-600" placeholder="License Plate (Optional)">

                <div class="flex justify-end gap-3 mt-4">
                    <button type="button" id="close-modal" class="px-4 py-2 text-gray-400 hover:text-white transition">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const modal = document.getElementById("modal");
    const list  = document.getElementById("vehicle-list");

    // Modal Controls
    document.getElementById("open-modal").onclick = () => {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    };
    
    document.getElementById("close-modal").onclick = () => {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    };

    // Toast Notification System
    function toast(msg, err=false){
        const t = document.getElementById("toast");
        document.getElementById("toast-message").innerText = msg;
        document.getElementById("toast-content").className =
            err ? "bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg"
                : "bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg";
        t.classList.add("show");
        setTimeout(() => t.classList.remove("show"), 3000);
    }

    /* LOAD VEHICLES */
    async function loadVehicles(){
        try {
            list.innerHTML = "<p class='text-gray-500'>Loading your garage...</p>";
            
            // ✅ Corrected filename to match your PHP file: get_vehicles.php
            const res = await fetch("get_vehicles.php");
            
            if (!res.ok) throw new Error("Server responded with error " + res.status);
            
            const data = await res.json();

            list.innerHTML = ""; // Clear loader

            if(!data || Object.keys(data).length === 0){
                list.innerHTML = "<p class='col-span-full text-center py-10 text-gray-500 italic'>No vehicles found in your garage.</p>";
                return;
            }

            for(const id in data){
                const v = data[id];
                list.innerHTML += `
                <div class="bg-gray-800 p-5 rounded-lg border border-gray-700 vehicle-card hover:border-blue-500 transition shadow-md">
                    <div class="flex justify-between items-start">
                        <h3 class="text-xl font-bold text-white mb-1">${v.nickname}</h3>
                        <span class="text-xs bg-gray-700 px-2 py-1 rounded text-gray-400 uppercase tracking-widest">${v.year}</span>
                    </div>
                    <p class="text-gray-400">${v.make} ${v.model}</p>
                    <div class="mt-4 pt-3 border-t border-gray-700 flex justify-between items-center">
                        <p class="text-sm font-mono text-blue-400">${v.plate || "NO PLATE"}</p>
                        <button class="text-xs text-gray-500 hover:text-white transition">Details →</button>
                    </div>
                </div>`;
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            list.innerHTML = `<p class="text-red-500">Error loading data. Make sure 'get_vehicles.php' exists.</p>`;
        }
    }

    /* ADD VEHICLE */
    document.getElementById("add-vehicle-form").onsubmit = async e => {
        e.preventDefault();

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
            
            if(!result.success) {
                return toast(result.error || "Failed to add vehicle", true);
            }

            // Success
            modal.classList.add("hidden");
            modal.classList.remove("flex");
            e.target.reset();
            toast("Vehicle added successfully!");
            loadVehicles();
        } catch (error) {
            toast("Connection error. Try again.", true);
        }
    };

    /* SEARCH FUNCTION */
    document.getElementById("garage-search").oninput = e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll(".vehicle-card").forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(q) ? "block" : "none";
        });
    };

    // Initial Load
    loadVehicles();
});
</script>

</body>
</html>