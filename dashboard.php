<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Dashboard - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .card-grad { background: linear-gradient(145deg, #1e293b, #111827); }
    </style>
</head>
<body class="text-gray-300 antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8 min-h-screen">
        <div class="max-w-6xl mx-auto">
            
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 id="vehicle-name" class="text-3xl font-bold text-white italic">Loading Vehicle...</h1>
                    <p class="text-gray-400">A comprehensive overview of your vehicle's health and maintenance.</p>
                </div>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Run Diagnostic
                </button>
            </div>

            <div id="alert-banner" class="hidden mb-8 card-grad border border-gray-700 rounded-xl p-6 flex justify-between items-center">
                <div class="flex items-center gap-6">
                    <div class="bg-yellow-500/10 p-4 rounded-full border border-yellow-500/20">
                        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <span class="text-yellow-500 text-xs font-bold uppercase tracking-wider">Critical Alert</span>
                        <h2 class="text-xl font-bold text-white">Oil Change Due in <span id="oil-miles">0</span> miles</h2>
                        <p class="text-gray-400 text-sm mt-1">Your vehicle is due for an oil change soon to ensure optimal engine performance.</p>
                        <a href="#" class="text-blue-400 text-sm font-semibold mt-2 inline-block hover:underline">Schedule Service &rarr;</a>
                    </div>
                </div>
                <div class="hidden md:block bg-gray-800 px-10 py-8 rounded-lg border border-gray-700 text-gray-500 font-bold">
                    Honda Civic
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="card-grad border border-gray-700 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-white mb-6">Upcoming Maintenance</h3>
                    <div id="maintenance-list" class="space-y-6">
                        </div>
                </div>

                <div class="card-grad border border-gray-700 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-white mb-6">Performance Analytics</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-400 text-sm">Fuel Efficiency Over Time</p>
                            <div class="flex items-baseline gap-2 mt-1">
                                <span id="mpg-val" class="text-3xl font-bold text-white">0.0 MPG</span>
                            </div>
                            <p class="text-green-500 text-xs font-bold mt-1">Last 6 Months +1.2%</p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-sm">Monthly Fuel Costs</p>
                            <div class="flex items-baseline gap-2 mt-1">
                                <span id="fuel-cost" class="text-3xl font-bold text-white">$0.00</span>
                            </div>
                            <p class="text-red-500 text-xs font-bold mt-1">This Year -5.5%</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-app.js";
        import { getFirestore, doc, onSnapshot } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-firestore.js";

        // Replace with your ACTUAL Firebase Config
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            authDomain: "YOUR_PROJECT.firebaseapp.com",
            projectId: "YOUR_PROJECT_ID",
            storageBucket: "YOUR_PROJECT.appspot.com",
            messagingSenderId: "YOUR_ID",
            appId: "YOUR_APP_ID"
        };

        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);

        // Fetch vehicle data (Replace 'honda-civic-id' with your actual Doc ID)
        const vehicleDoc = doc(db, "vehicles", "honda-civic-id");

        onSnapshot(vehicleDoc, (doc) => {
            if (doc.exists()) {
                const data = doc.data();
                
                // Update UI with Firebase Data
                document.getElementById('vehicle-name').innerText = data.name;
                document.getElementById('oil-miles').innerText = data.oil_change_miles;
                document.getElementById('mpg-val').innerText = `${data.mpg} MPG`;
                document.getElementById('fuel-cost').innerText = `$${data.monthly_fuel.toFixed(2)}`;
                
                // Show banner if oil is low
                if(data.oil_change_miles < 500) {
                    document.getElementById('alert-banner').classList.remove('hidden');
                }

                // Render Maintenance List
                const list = document.getElementById('maintenance-list');
                list.innerHTML = data.maintenance.map(item => `
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="w-3 h-3 rounded-full ${item.status === 'overdue' ? 'bg-red-500' : 'bg-yellow-500'}"></div>
                            <div>
                                <p class="text-white font-bold">${item.task}</p>
                                <p class="${item.status === 'overdue' ? 'text-red-500' : 'text-yellow-500'} text-xs">${item.due}</p>
                            </div>
                        </div>
                        <a href="#" class="text-blue-400 text-sm hover:underline">View Details</a>
                    </div>
                `).join('');
            }
        });
    </script>
</body>
</html>