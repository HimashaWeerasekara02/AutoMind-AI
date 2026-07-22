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

        @media (max-width: 768px) {
            .chart-container { height: 250px !important; }
        }
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
                <div class="w-full md:w-72">
                    <label class="text-[10px] uppercase font-black text-blue-500 block mb-2 tracking-widest">Select Target Vehicle</label>
                    <select id="vehicle-selector" class="w-full rounded-xl px-4 py-3 text-sm shadow-2xl border-blue-500/20">
                        <option value="">Establishing Garage Link...</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <div class="lg:col-span-3 space-y-6">
                    
                    <div class="glass-card p-6 md:p-8 border-t-4 border-t-blue-600">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="material-symbols-outlined text-blue-500">receipt_long</span>
                            <h2 class="text-lg font-bold text-white uppercase tracking-tight italic">Transmit Mechanic Bill</h2>
                        </div>
                        <div class="drop-zone p-8 md:p-12 text-center flex flex-col items-center gap-4 cursor-pointer" id="drop-area">
                            <input type="file" id="file-input" class="hidden" accept="image/*">
                            <div class="bg-blue-600/10 p-5 rounded-2xl text-blue-500">
                                <span class="material-symbols-outlined text-4xl">cloud_upload</span>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-white">Upload Service Invoice</p>
                                <p class="text-slate-500 text-xs">AI will audit prices and save to selected vehicle's logs.</p>
                            </div>
                            <button class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold uppercase text-xs transition shadow-lg">Browse Files</button>
                        </div>
                    </div>

                    <div id="analysis-result-container" class="hidden animate-in fade-in slide-in-from-bottom-4 duration-500"></div>

                    <div class="glass-card p-6 md:p-8">
                        <h2 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-6">Known Issues Lookup (Neural Fallback)</h2>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" id="issue-search" placeholder="Search car model (e.g. Toyota Axio)..." 
                                class="flex-1 rounded-lg px-4 py-2.5 text-sm outline-none">
                            <button id="search-btn" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2.5 rounded-lg font-bold uppercase text-xs transition">Scan Records</button>
                        </div>
                        <div id="search-results" class="mt-6 grid grid-cols-1 gap-4"></div>
                    </div>

                    <div class="glass-card p-6 md:p-8">
                        <h2 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-6">Service Cost Trajectory</h2>
                        <div class="chart-container h-[300px] w-full relative">
                            <canvas id="analyticsChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="glass-card p-6 border-l-4 border-indigo-600">
                        <h3 class="text-indigo-500 text-[10px] font-bold mb-6 uppercase tracking-widest">Market Price Benchmark</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-2 tracking-widest">Make</label>
                                <select id="est-make" class="w-full rounded-lg px-3 py-2 text-sm border-gray-800">
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-2 tracking-widest">Model</label>
                                <select id="est-model" class="w-full rounded-lg px-3 py-2 text-sm border-gray-800" disabled>
                                    <option value="">Select Make First</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase font-bold text-slate-500 block mb-2 tracking-widest">Component</label>
                                <select id="est-part" class="w-full rounded-lg px-3 py-2 text-sm border-gray-800" disabled>
                                    <option value="">Select Model First</option>
                                </select>
                            </div>
                            <button onclick="calculateEstimate()" id="est-calc-btn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold uppercase text-[10px] tracking-widest transition shadow-xl">Retrieve Benchmarks</button>
                            
                            <div id="estimate-result" class="hidden mt-4 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
                                <div id="estimate-val" class="space-y-3"></div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-6">
                        <h3 class="text-slate-500 text-[10px] font-bold mb-4 uppercase tracking-widest">Cognitive Audit</h3>
                        <p class="text-[10px] text-slate-400 leading-relaxed italic">
                            The system uses a grounded dataset to compare billed amounts against Sri Lankan market averages. Overcharged items are flagged automatically during invoice transmission.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        let vehicleCache = {};
        let partsDataset = [];
        const selector = document.getElementById('vehicle-selector');

        async function loadVehicles() {
            try {
                const res = await fetch('get_vehicles.php');
                vehicleCache = await res.json();
                selector.innerHTML = '<option value="">-- Choose Target Vehicle --</option>';
                for (const [id, v] of Object.entries(vehicleCache)) {
                    const opt = document.createElement('option');
                    opt.value = id;
                    opt.textContent = (v.nickname || `${v.year} ${v.make} ${v.model}`).toUpperCase();
                    selector.appendChild(opt);
                }
                
                const params = new URLSearchParams(window.location.search);
                if (params.has('vehicleId')) {
                    selector.value = params.get('vehicleId');
                }
            } catch (err) { selector.innerHTML = '<option value="">Sync Failure</option>'; }
        }

        const makeSelect = document.getElementById('est-make');
        const modelSelect = document.getElementById('est-model');
        const partSelect = document.getElementById('est-part');

        async function loadEstimatorData() {
            try {
                const res = await fetch('get_parts_data.php');
                partsDataset = await res.json();
                const makes = [...new Set(partsDataset.map(i => i.make))].sort();
                makeSelect.innerHTML = '<option value="">Select Make</option>' + makes.map(m => `<option value="${m}">${m}</option>`).join('');
                
                makeSelect.onchange = () => {
                    const filtered = partsDataset.filter(i => i.make === makeSelect.value);
                    const models = [...new Set(filtered.map(i => i.model))].sort();
                    modelSelect.innerHTML = '<option value="">Select Model</option>' + models.map(m => `<option value="${m}">${m}</option>`).join('');
                    modelSelect.disabled = !makeSelect.value;
                    partSelect.disabled = true;
                    partSelect.innerHTML = '<option value="">Select Model First</option>';
                };

                modelSelect.onchange = () => {
                    const filtered = partsDataset.filter(i => i.make === makeSelect.value && i.model === modelSelect.value);
                    const parts = [...new Set(filtered.map(i => i.part_name))].sort();
                    partSelect.innerHTML = '<option value="">Select Spare Part</option>' + parts.map(p => `<option value="${p}">${p}</option>`).join('');
                    partSelect.disabled = !modelSelect.value;
                };
            } catch (err) { console.error("Dataset error", err); }
        }

        function calculateEstimate() {
            const make = makeSelect.value;
            const model = modelSelect.value;
            const part = partSelect.value;
            const resDiv = document.getElementById('estimate-result');
            const valDiv = document.getElementById('estimate-val');

            if (!make || !model || !part) return alert("Please complete the profile selection.");

            const items = partsDataset.filter(i => i.make === make && i.model === model && i.part_name === part);
            const newPrices = items.filter(i => i.condition === 'New').map(i => i.price_lkr);
            const usedPrices = items.filter(i => i.condition === 'Used').map(i => i.price_lkr);

            const avgNew = newPrices.length ? Math.round(newPrices.reduce((a, b) => a + b, 0) / newPrices.length) : 0;
            const avgUsed = usedPrices.length ? Math.round(usedPrices.reduce((a, b) => a + b, 0) / usedPrices.length) : 0;

            resDiv.classList.remove('hidden');
            valDiv.innerHTML = `
                <div class="flex justify-between items-center bg-blue-600/10 p-3 rounded-xl border border-blue-600/20">
                    <span class="text-slate-400 text-[10px] font-black uppercase">Market New</span>
                    <span class="text-white font-black">${avgNew > 0 ? 'LKR ' + avgNew.toLocaleString() : 'N/A'}</span>
                </div>
                <div class="flex justify-between items-center bg-slate-700/30 p-3 rounded-xl border border-slate-700">
                    <span class="text-slate-400 text-[10px] font-black uppercase">Market Used</span>
                    <span class="text-white font-black">${avgUsed > 0 ? 'LKR ' + avgUsed.toLocaleString() : 'N/A'}</span>
                </div>
            `;
        }

        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        dropArea.onclick = () => fileInput.click();

        fileInput.onchange = async (e) => {
            const file = e.target.files[0];
            const vId = selector.value;
            
            
            if (!vId) { 
                alert("STRICT REQUIREMENT: You must select a Target Vehicle from the dropdown before analyzing a bill."); 
                fileInput.value = ''; 
                return; 
            }
            if (!file) return;

            const originalContent = dropArea.innerHTML;
            dropArea.innerHTML = `<div class="p-10 flex flex-col items-center gap-4"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div><div class="text-blue-500 font-black uppercase italic text-[10px] tracking-[0.2em] animate-pulse">Running Neural Bill Audit...</div></div>`;

            const formData = new FormData();
            formData.append('billImage', file);
            formData.append('vehicleId', vId);
            formData.append('make', vehicleCache[vId].make);
            formData.append('model', vehicleCache[vId].model);

            try {
                const res = await fetch('process_analysis.php', { method: 'POST', body: formData });
                const result = await res.json();
                if (result.success) {
                    renderOCRResult(result);
        
                } else { 
                    alert("Audit Failed: " + result.error); 
                }
            } catch (err) { alert("Link failure. Ensure XAMPP and Firebase are active."); }
            finally { dropArea.innerHTML = originalContent; }
        };

        function renderOCRResult(data) {
            const container = document.getElementById('analysis-result-container');
            container.classList.remove('hidden');
            const rows = data.analysis.map(row => `
                <tr class="hover:bg-gray-800/30 border-b border-gray-800/50">
                    <td class="px-6 py-4 font-bold text-white text-xs uppercase italic tracking-tight">${row.part}</td>
                    <td class="px-6 py-4 text-xs font-mono">Rs. ${row.billed.toLocaleString()}</td>
                    <td class="px-6 py-4 text-xs text-slate-500 font-mono">${row.market ? 'Rs. ' + row.market.toLocaleString() : 'N/A'}</td>
                    <td class="px-6 py-4 text-right">
                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase ${row.status === 'Overcharged' ? 'bg-red-500/10 text-red-500' : 'bg-green-500/10 text-green-500'}">${row.status}</span>
                    </td>
                </tr>`).join('');

            container.innerHTML = `
                <div class="glass-card overflow-hidden border-l-4 border-blue-500 mb-8 shadow-2xl">
                    <div class="p-6 bg-blue-500/5 flex justify-between items-center border-b border-gray-800">
                         <div>
                            <span class="text-blue-500 text-[10px] font-black uppercase tracking-widest">Neural Audit Terminal</span>
                            <h3 class="text-xl font-black text-white mt-1 italic uppercase tracking-tighter">Cost Variance Report</h3>
                         </div>
                         <div class="text-right">
                            <p class="text-slate-500 text-[10px] uppercase font-black tracking-widest">Invoice Total</p>
                            <p class="text-white font-black text-2xl italic tracking-tighter">Rs. ${data.total.toLocaleString()}</p>
                         </div>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-gray-800/50 text-slate-500 text-[10px] font-black uppercase tracking-widest">
                            <tr><th class="px-6 py-4 tracking-[0.2em]">Mechanical Component</th><th class="px-6 py-4">Billed Amount</th><th class="px-6 py-4">Market Average</th><th class="px-6 py-4 text-right">Verdict</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">${rows}</tbody>
                    </table>
                    <div class="p-4 bg-gray-900/50 text-center flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-green-500 text-sm">cloud_done</span>
                        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-widest">Transmission Successful. Logged to ${selector.options[selector.selectedIndex].text}</p>
                    </div>
                </div>`;
            container.scrollIntoView({ behavior: 'smooth' });
        }

        const searchBtn = document.getElementById('search-btn');
        const issueInput = document.getElementById('issue-search');
        const resultsDiv = document.getElementById('search-results');

        searchBtn.onclick = async () => {
            const query = issueInput.value.trim();
            if(!query) return;

            searchBtn.disabled = true;
            searchBtn.innerText = "Scanning...";
            resultsDiv.innerHTML = '<div class="py-10 text-center animate-pulse text-blue-500 font-black uppercase text-[10px] tracking-widest">Consulting Global Factory Datasets...</div>';

            try {
                const res = await fetch(`lookup_issues.php?query=${encodeURIComponent(query)}`);
                const data = await res.json();
                if(data.success) {
                    resultsDiv.innerHTML = data.results.map(r => `
                        <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6 shadow-xl">
                            <div class="flex justify-between items-center mb-6">
                                <h4 class="text-white font-black italic uppercase tracking-tighter text-lg">${r.car} Vulnerabilities</h4>
                                <span class="text-[9px] font-black px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 uppercase tracking-widest">${r.rating}</span>
                            </div>
                            <div class="space-y-4">
                                ${r.points.map((p, i) => `
                                    <div class="flex gap-4 items-start">
                                        <div class="w-6 h-6 bg-blue-600 text-white rounded-lg text-[10px] flex items-center justify-center font-black flex-shrink-0 shadow-lg">${i+1}</div>
                                        <p class="text-xs text-slate-300 font-medium leading-relaxed italic">${p}</p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('');
                } else {
                    resultsDiv.innerHTML = `<p class="text-center text-amber-500 text-xs p-6 bg-amber-500/5 rounded-xl border border-amber-500/20">${data.message}</p>`;
                }
            } catch (e) { resultsDiv.innerHTML = '<p class="text-red-500 text-xs">Link offline.</p>'; }
            finally { searchBtn.disabled = false; searchBtn.innerText = "Scan Records"; }
        };

        window.onload = () => {
            loadVehicles();
            loadEstimatorData();
            
            const ctx = document.getElementById('analyticsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'JAN'],
                    datasets: [{ label: 'Health Index', data: [85, 82, 88, 86, 91, 89], borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.05)', fill: true, tension: 0.4, borderWidth: 3 }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { legend: { display: false } }, 
                    scales: { 
                        y: { grid: { color: 'rgba(255,255,255,0.02)' }, ticks: { color: '#475569', font: { size: 9, weight: 'bold' } } }, 
                        x: { grid: { display: false }, ticks: { color: '#475569', font: { size: 9, weight: 'bold' } } } 
                    } 
                }
            });
        };
    </script>
</body>
</html>