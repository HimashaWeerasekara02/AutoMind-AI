<?php 
require_once 'db.php'; 
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostics & Analysis - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore-compat.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 1.5rem; transition: all 0.3s ease; }
        .glass-card:hover { border-color: #3b82f6; }
        .drop-zone { border: 2px dashed #334155; transition: all 0.3s ease; border-radius: 1.25rem; }
        .drop-zone:hover { border-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
        input, select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: white !important; }
        input:focus, select:focus { border-color: #3b82f6 !important; outline: none; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 min-h-screen p-4 md:p-8 transition-all duration-300">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-white text-2xl md:text-3xl font-bold italic uppercase tracking-tight">Diagnostics & Analysis</h1>
                    <p class="text-slate-400 text-sm mt-1">AI-Powered Mechanical Oversight & Cost Auditing</p>
                </div>

                <div class="glass-card px-4 py-3 flex items-center gap-4 w-full md:w-auto min-w-[280px]">
                    <span class="material-symbols-outlined text-blue-500">directions_car</span>
                    <div class="flex-1">
                        <label class="text-[9px] uppercase font-bold text-slate-500 block leading-none mb-1">Active Vehicle</label>
                        <select id="vehicle-selector" onchange="updateVehicleContext()" class="bg-transparent border-none text-white text-sm font-bold w-full p-0 focus:ring-0 cursor-pointer">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <div class="lg:col-span-3 space-y-6">
                    <div class="glass-card p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="material-symbols-outlined text-blue-500">receipt_long</span>
                            <h2 class="text-lg font-bold text-white uppercase tracking-tight italic">Analyze Mechanic Bill</h2>
                        </div>
                        <div class="drop-zone p-8 md:p-12 text-center flex flex-col items-center gap-4 cursor-pointer" id="drop-area">
                            <input type="file" id="file-input" class="hidden" accept=".pdf,.jpg,.png">
                            <div class="bg-blue-600/10 p-5 rounded-2xl text-blue-500">
                                <span class="material-symbols-outlined text-4xl">cloud_upload</span>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-white">Drag & Drop Bill Here</p>
                                <p class="text-slate-500 text-xs">Analyze costs for <span id="target-vehicle-name" class="text-blue-400 font-bold">your vehicle</span></p>
                            </div>
                            <button onclick="document.getElementById('file-input').click()" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold uppercase text-xs transition shadow-lg">Browse Files</button>
                        </div>
                    </div>

                    <div id="analysis-result-container" class="hidden"></div>

                    <div class="glass-card p-6 md:p-8">
                        <h2 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-6">Health Trends: <span id="chart-vehicle-label" class="text-blue-400">All Vehicles</span></h2>
                        <div class="chart-container h-[300px] w-full relative">
                            <canvas id="analyticsChart"></canvas>
                        </div>
                    </div>

                    <div class="glass-card p-6 md:p-8">
                        <h2 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-6">Known Issues Lookup</h2>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" id="issue-search" placeholder="Search (e.g., 'Transmission', 'Brakes')..." 
                                class="flex-1 rounded-lg px-4 py-2.5 text-sm">
                            <button onclick="lookupIssue()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-bold uppercase text-xs transition">Search Database</button>
                        </div>
                        <div id="search-results" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="glass-card p-6 border-l-4 border-blue-600">
                        <h3 class="text-blue-500 text-[10px] font-bold mb-6 uppercase tracking-widest">Cost Estimator</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-2">Part Category</label>
                                <select id="est-part-type" class="w-full rounded-lg px-3 py-2 text-sm">
                                    <option value="1.0">OEM (Original)</option>
                                    <option value="0.6">Aftermarket</option>
                                    <option value="0.4">Refurbished</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-2">Labor Complexity</label>
                                <select id="est-labor" class="w-full rounded-lg px-3 py-2 text-sm">
                                    <option value="5000">Simple (Filter/Oil)</option>
                                    <option value="15000">Medium (Brakes)</option>
                                    <option value="45000">Complex (Engine)</option>
                                </select>
                            </div>
                            <button onclick="calculateEstimate()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg font-bold uppercase text-[10px] tracking-widest transition">Calculate</button>
                            <div id="estimate-result" class="hidden mt-4 p-4 bg-gray-900/50 rounded-xl border border-gray-700 text-center">
                                <p id="estimate-vehicle-name" class="text-slate-500 text-[10px] uppercase font-bold mb-1"></p>
                                <p id="estimate-val" class="text-xl font-bold text-white italic">LKR 0</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        // 2. INITIALIZE FIREBASE (Replace with your actual config from Firebase Console)
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            authDomain: "automind-ai-52b33.firebaseapp.com",
            projectId: "automind-ai-52b33",
            storageBucket: "automind-ai-52b33.appspot.com",
            messagingSenderId: "YOUR_SENDER_ID",
            appId: "YOUR_APP_ID"
        };

        firebase.initializeApp(firebaseConfig);
        const db = firebase.firestore();
        
        let vehicleCache = {};
        let selectedVehicleId = "";

        async function loadUserVehicles() {
            const selector = document.getElementById('vehicle-selector');
            try {
                const response = await fetch('get_vehicles.php'); 
                vehicleCache = await response.json();
                if (Object.keys(vehicleCache).length === 0) {
                    selector.innerHTML = '<option value="">No vehicles found</option>';
                    return;
                }
                selector.innerHTML = '<option value="">Choose a vehicle...</option>';
                for (const [id, vehicle] of Object.entries(vehicleCache)) {
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = `${vehicle.nickname} (${vehicle.make})`;
                    selector.appendChild(option);
                }
            } catch (error) {
                selector.innerHTML = '<option value="">Error loading vehicles</option>';
            }
        }

        function updateVehicleContext() {
            selectedVehicleId = document.getElementById('vehicle-selector').value;
            const vehicle = vehicleCache[selectedVehicleId];
            if (vehicle) {
                document.getElementById('target-vehicle-name').innerText = `${vehicle.make} ${vehicle.model}`;
                document.getElementById('chart-vehicle-label').innerText = vehicle.nickname;
                document.getElementById('estimate-vehicle-name').innerText = `${vehicle.nickname} Estimate`;
            }
        }

        // 3. CORRECTED LOOKUP FUNCTION FOR FIRESTORE
        async function lookupIssue() {
            const query = document.getElementById('issue-search').value.toLowerCase();
            const resultsDiv = document.getElementById('search-results');
            
            if (!query) return alert("Please enter a search term");

            resultsDiv.innerHTML = '<div class="col-span-full text-blue-500 text-xs animate-pulse text-center">Querying Firestore Database...</div>';

            try {
                // Fetch issues from Firestore collection 'known_issues'
                const snapshot = await db.collection('known_issues').get();

                resultsDiv.innerHTML = '';
                let found = false;

                snapshot.forEach(doc => {
                    const issue = doc.data();
                    
                    // Match against issue_title or description (matches your screenshot fields)
                    const title = (issue.issue_title || "").toLowerCase();
                    const description = (issue.description || "").toLowerCase();
                    const model = (issue.model || "").toLowerCase();

                    if (title.includes(query) || description.includes(query) || model.includes(query)) {
                        found = true;
                        resultsDiv.innerHTML += `
                            <div class="bg-slate-800/50 p-5 rounded-xl border border-slate-700 hover:border-blue-500 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-white font-bold text-sm italic uppercase">${issue.issue_title}</h4>
                                    <span class="bg-blue-600/20 text-blue-400 text-[8px] px-2 py-0.5 rounded uppercase font-bold">${issue.year || '2014-2018'}</span>
                                </div>
                                <p class="text-[11px] text-slate-400 leading-relaxed">${issue.description}</p>
                                <div class="mt-3 pt-3 border-t border-slate-700/50 flex justify-between items-center">
                                    <span class="text-blue-500 text-[9px] font-bold uppercase">Model: ${issue.model}</span>
                                    <span class="material-symbols-outlined text-blue-500 text-sm">settings_suggest</span>
                                </div>
                            </div>`;
                    }
                });

                if (!found) {
                    resultsDiv.innerHTML = '<p class="text-xs text-slate-500 col-span-full text-center py-8 italic uppercase tracking-widest">No matching issues found in database.</p>';
                }
            } catch (e) {
                console.error(e);
                resultsDiv.innerHTML = '<p class="text-xs text-red-500 col-span-full text-center">Error connecting to Firestore. Check configuration.</p>';
            }
        }

        // --- CHART INIT ---
        const analyticsChart = new Chart(document.getElementById('analyticsChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Efficiency %',
                    data: [88, 85, 84, 89, 92, 90],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        function calculateEstimate() {
            if (!selectedVehicleId) return alert("Select a vehicle");
            const partMult = parseFloat(document.getElementById('est-part-type').value);
            const laborBase = parseFloat(document.getElementById('est-labor').value);
            const total = laborBase + (laborBase * 1.5 * partMult);
            document.getElementById('estimate-result').classList.remove('hidden');
            document.getElementById('estimate-val').innerText = `LKR ${Math.round(total).toLocaleString()}`;
        }

        window.addEventListener('DOMContentLoaded', loadUserVehicles);
    </script>
</body>
</html>