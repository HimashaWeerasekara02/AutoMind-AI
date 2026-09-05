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
                            <button class="mt-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold uppercase text-xs transition shadow-lg">Browse Files</button>
                        </div>
                    </div>

                    <div id="analysis-result-container" class="hidden animate-in fade-in slide-in-from-bottom-4 duration-500"></div>

                    <div class="glass-card p-6 md:p-8">
                        <h2 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-6">Diagnostic Health Trends</h2>
                        <div class="chart-container h-[300px] w-full relative">
                            <canvas id="analyticsChart"></canvas>
                        </div>
                    </div>

                    <div class="glass-card p-6 md:p-8">
                        <h2 class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-6">Known Issues Lookup</h2>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" id="issue-search" placeholder="Enter keyword (e.g., 'Transmission')..." 
                                class="flex-1 rounded-lg px-4 py-2.5 text-sm">
                            <button id="search-btn" class="bg-slate-700 hover:bg-slate-600 text-white px-6 py-2.5 rounded-lg font-bold uppercase text-xs transition">Search</button>
                        </div>
                        <div id="search-results" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4"></div>
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
                        <h3 class="text-slate-500 text-[10px] font-bold mb-4 uppercase tracking-widest">Recent Analysis</h3>
                        <div id="recent-analyses-list" class="space-y-3">
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-800/50 cursor-pointer transition">
                                <span class="material-symbols-outlined text-slate-500 text-sm">history</span>
                                <span class="text-xs text-slate-400">No recent scans found.</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <script>
        // Chart Initialization with Blue Theme
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        const analyticsChart = new Chart(ctx, {
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
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: '#334155' }, ticks: { color: '#64748b', font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } }
                }
            }
        });

        // Estimate Logic
        function calculateEstimate() {
            const partMult = parseFloat(document.getElementById('est-part-type').value);
            const laborBase = parseFloat(document.getElementById('est-labor').value);
            const total = laborBase + (laborBase * 1.5 * partMult);
            const resDiv = document.getElementById('estimate-result');
            resDiv.classList.remove('hidden');
            document.getElementById('estimate-val').innerText = `LKR ${Math.round(total).toLocaleString()}`;
        }

        // File Upload Styling & Animation
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        dropArea.onclick = () => fileInput.click();

        fileInput.onchange = async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const originalContent = dropArea.innerHTML;
            dropArea.innerHTML = `
                <div class="p-10 flex flex-col items-center gap-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <div class="text-blue-500 font-bold uppercase italic text-xs tracking-widest">AI Audit in Progress...</div>
                </div>`;

            try {
                const formData = new FormData();
                formData.append('bill', file);

                const res = await fetch('process_analysis.php', { method: 'POST', body: formData });
                const result = await res.json();

                if (result.success && result.data) {
                    renderResult(result.data);
                } else {
                    renderResult({
                        title: "Analysis Failed",
                        description: result.message || "The AI could not process this document. Please try a clearer image.",
                        insights: "Tip: Ensure the bill is well-lit and text is clearly visible."
                    });
                }
            } catch (err) {
                renderResult({
                    title: "Connection Error",
                    description: "Could not reach the AI analysis server. Please check your internet connection.",
                    insights: "Error: " + err.message
                });
            }

            dropArea.innerHTML = originalContent;
            // Re-bind click after restoring content
            document.getElementById('drop-area').onclick = () => document.getElementById('file-input').click();
        };

        function renderResult(data) {
            const container = document.getElementById('analysis-result-container');
            container.classList.remove('hidden');
            container.innerHTML = `
                <div class="glass-card p-6 md:p-8 border-l-4 border-blue-500 bg-blue-500/5 mb-8">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-blue-500 text-[10px] font-bold uppercase tracking-widest">Analysis Result</span>
                            <h3 class="text-xl font-bold text-white mt-1 italic uppercase">${data.title}</h3>
                        </div>
                        <p class="text-slate-500 font-bold text-[10px] uppercase">${new Date().toLocaleDateString()}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <p class="text-slate-400 text-sm leading-relaxed">${data.description}</p>
                        <div class="bg-gray-900/80 p-5 rounded-xl border border-blue-500/20">
                            <p class="text-blue-400 text-[10px] font-bold uppercase mb-2 tracking-widest">AI Audit Insight</p>
                            <p class="text-white text-sm italic font-medium">"${data.insights}"</p>
                        </div>
                    </div>
                </div>
            `;
            container.scrollIntoView({ behavior: 'smooth' });
        }

        // Known Issues Lookup via Gemini AI
        document.getElementById('search-btn').addEventListener('click', async () => {
            const query = document.getElementById('issue-search').value.trim();
            const resultsDiv = document.getElementById('search-results');
            if (!query) {
                resultsDiv.innerHTML = '<p class="text-amber-400 text-xs col-span-2 text-center uppercase tracking-widest font-bold">Please enter a keyword to search.</p>';
                return;
            }

            resultsDiv.innerHTML = `
                <div class="col-span-2 flex justify-center py-8">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                </div>`;

            try {
                const res = await fetch('search_known_issues.php?q=' + encodeURIComponent(query));
                const data = await res.json();

                if (data.success && data.issues && data.issues.length > 0) {
                    resultsDiv.innerHTML = data.issues.map(issue => `
                        <div class="glass-card p-5 border-l-4 ${issue.severity === 'High' ? 'border-red-500' : issue.severity === 'Medium' ? 'border-amber-500' : 'border-green-500'}">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-white text-sm font-bold uppercase">${issue.title}</h4>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${issue.severity === 'High' ? 'bg-red-500/20 text-red-400' : issue.severity === 'Medium' ? 'bg-amber-500/20 text-amber-400' : 'bg-green-500/20 text-green-400'}">${issue.severity}</span>
                            </div>
                            <p class="text-slate-400 text-xs leading-relaxed">${issue.description}</p>
                            <p class="text-blue-400 text-[10px] mt-3 font-bold uppercase tracking-widest">Est. Cost: ${issue.estimatedCost}</p>
                        </div>
                    `).join('');
                } else {
                    resultsDiv.innerHTML = '<p class="text-slate-500 text-xs col-span-2 text-center uppercase tracking-widest">No known issues found for "' + query + '".</p>';
                }
            } catch (err) {
                resultsDiv.innerHTML = '<p class="text-red-400 text-xs col-span-2 text-center">AI Lookup Error: ' + err.message + '</p>';
            }
        });

        // Allow Enter key to trigger search
        document.getElementById('issue-search').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') document.getElementById('search-btn').click();
        });
    </script>
</body>
</html>