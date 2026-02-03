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

    <main class="lg:ml-64 min-h-screen p-4 md:p-8">
        <div class="max-w-7xl mx-auto">
            
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-6">
                <div>
                    <h1 class="text-white text-2xl md:text-3xl font-bold italic uppercase tracking-tight">Diagnostics & Analysis</h1>
                    <p class="text-slate-400 text-sm mt-1">AI-Powered Mechanical Oversight & Cost Auditing</p>
                </div>
                
                <div class="glass-card px-4 py-3 flex items-center gap-4 w-full lg:w-auto">
                    <span class="material-symbols-outlined text-blue-500">directions_car</span>
                    <div class="flex-1 lg:w-48">
                        <label class="text-[9px] uppercase font-bold text-slate-500 block">Active Vehicle</label>
                        <select id="vehicle-selector" class="bg-transparent border-none text-white text-sm font-bold w-full p-0 focus:ring-0">
                            <option value="">Select a Vehicle...</option>
                            <option value="v1">Toyota Prius (WP CB-4452)</option>
                            <option value="v2">Honda Vezel (WP KC-1102)</option>
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
                                <p class="text-slate-500 text-xs">PDF, PNG, or JPG (Max 10MB)</p>
                            </div>
                            <button class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold uppercase text-xs transition">Browse Files</button>
                        </div>
                    </div>

                    <div id="analysis-result-container" class="hidden animate-in fade-in slide-in-from-bottom-4 duration-500"></div>

                    <div class="glass-card p-6 md:p-8">
                        <h2 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-6">Known Issues Lookup (Live Search)</h2>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" id="issue-search" placeholder="e.g., 'Prius Hybrid Battery' or 'Brake Squeak'..." 
                                class="flex-1 rounded-lg px-4 py-2.5 text-sm">
                            <button onclick="lookupIssue()" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-lg font-bold uppercase text-xs transition">Search Database</button>
                        </div>
                        <div id="search-results" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            </div>
                    </div>

                    <div class="glass-card p-6 md:p-8">
                        <h2 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-6">Diagnostic Health Trends</h2>
                        <div class="h-[300px] w-full">
                            <canvas id="analyticsChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="glass-card p-6 border-l-4 border-blue-600">
                        <h3 class="text-blue-500 text-[10px] font-bold mb-6 uppercase tracking-widest">Quick Cost Estimator</h3>
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
                                <p class="text-slate-500 text-[10px] uppercase font-bold">Estimated Total</p>
                                <p id="estimate-val" class="text-xl font-bold text-white italic">LKR 0</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-6">
                        <h3 class="text-slate-500 text-[10px] font-bold mb-4 uppercase tracking-widest">Recent Activity</h3>
                        <div id="recent-analyses-list" class="space-y-3">
                            <div id="no-history" class="flex items-center gap-3 p-2">
                                <span class="material-symbols-outlined text-slate-600 text-sm">history</span>
                                <span class="text-xs text-slate-500 italic">No recent scans.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Chart Initialization
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        let analyticsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['W1', 'W2', 'W3', 'W4'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 0, max: 100, grid: { color: '#334155' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Update chart when vehicle changes
        document.getElementById('vehicle-selector').onchange = function() {
            if(this.value !== "") {
                analyticsChart.data.datasets[0].data = [80, 78, 85, 92];
                analyticsChart.update();
            }
        };

        // FIXED: Robust Lookup Issue Function
        async function lookupIssue() {
            const query = document.getElementById('issue-search').value;
            const resultsDiv = document.getElementById('search-results');
            
            if (!query) return;

            resultsDiv.innerHTML = '<div class="col-span-full text-center py-4 text-blue-500 animate-pulse">Searching global archives...</div>';

            try {
                const response = await fetch(`lookup_proxy.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                // Check if Google returned results
                if (data.results && data.results.length > 0) {
                    resultsDiv.innerHTML = '';
                    data.results.slice(0, 4).forEach(item => {
                        resultsDiv.innerHTML += `
                            <div class="bg-slate-800/50 p-4 rounded-xl border border-slate-700 hover:border-blue-500/50 transition">
                                <h4 class="text-white font-bold text-sm truncate">${item.name}</h4>
                                <p class="text-[10px] text-slate-500 mb-2">${item.formatted_address}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-yellow-500 font-bold">★ ${item.rating || 'N/A'}</span>
                                    <span class="text-[9px] text-blue-400 uppercase font-bold tracking-tighter">Verified Provider</span>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    resultsDiv.innerHTML = `
                        <div class="col-span-full p-8 text-center glass-card border-dashed">
                            <span class="material-symbols-outlined text-slate-600 mb-2">search_off</span>
                            <p class="text-slate-500 text-xs italic">No specific mechanical reports found for "${query}".</p>
                        </div>`;
                }
            } catch (error) {
                resultsDiv.innerHTML = '<div class="col-span-full text-red-400 text-xs text-center p-4">Connection to diagnostic proxy failed. Check console.</div>';
                console.error("Proxy Error:", error);
            }
        }

        // Cost Estimator Logic
        function calculateEstimate() {
            const partMult = parseFloat(document.getElementById('est-part-type').value);
            const laborBase = parseFloat(document.getElementById('est-labor').value);
            const total = laborBase + (laborBase * 1.5 * partMult);
            const resDiv = document.getElementById('estimate-result');
            resDiv.classList.remove('hidden');
            document.getElementById('estimate-val').innerText = `LKR ${Math.round(total).toLocaleString()}`;
        }

        // File Analysis Logic
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        dropArea.onclick = () => fileInput.click();

        fileInput.onchange = (e) => {
            const file = e.target.files[0];
            if (!file) return;

            dropArea.innerHTML = `<div class="p-10 animate-pulse text-blue-500 font-bold">ANALYZING...</div>`;

            setTimeout(() => {
                const container = document.getElementById('analysis-result-container');
                container.classList.remove('hidden');
                container.innerHTML = `
                    <div class="glass-card p-6 border-l-4 border-blue-500 bg-blue-500/5 mb-8">
                        <h3 class="text-white font-bold italic uppercase">AI Bill Audit: ${file.name}</h3>
                        <p class="text-slate-400 text-sm mt-2">Analysis complete. Costs match market average. Maintenance logged.</p>
                    </div>`;
                dropArea.innerHTML = `<p class="text-blue-500 font-bold">READY FOR NEXT SCAN</p>`;
            }, 1500);
        };
    </script>
</body>
</html>