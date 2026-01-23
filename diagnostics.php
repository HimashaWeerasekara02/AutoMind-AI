<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostics & Analysis - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; }
        .glass-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; }
        .drop-zone { border: 2px dashed #334155; transition: all 0.3s ease; }
        .drop-zone.active { border-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
        @keyframes pulse-once { 0% { opacity: 0.5; } 100% { opacity: 1; } }
        .animate-pulse-once { animation: pulse-once 0.5s ease-in-out; }
    </style>
</head>
<body class="text-gray-300 antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white tracking-tight">Diagnostics & Analysis</h1>
                <p class="text-gray-400 mt-1">Upload mechanic bills for an AI-powered analysis and cost estimation.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <div class="glass-card p-8">
                        <h2 class="text-xl font-semibold text-white mb-6">Analyze Your Mechanic Bill</h2>
                        <div class="drop-zone rounded-xl p-12 text-center flex flex-col items-center justify-center gap-4 cursor-pointer" id="drop-area">
                            <input type="file" id="file-input" class="hidden" accept=".pdf,.jpg,.png">
                            <div class="bg-blue-600/10 p-4 rounded-full text-blue-500">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                            </div>
                            <p class="text-lg font-medium text-white">Drag & drop files here or click to browse</p>
                        </div>
                    </div>

                    <div id="analysis-result-container">
                        <div class="glass-card p-6 text-center text-gray-500 italic">
                            Upload a bill above to see AI analysis results here.
                        </div>
                    </div>

                    <div class="glass-card p-6">
                         <h2 class="text-xl font-semibold text-white mb-2">"Known Issues" Lookup</h2>
                         <div class="flex gap-4">
                             <input type="text" id="issue-search" placeholder="Search issues (e.g. transmission)..." class="flex-1 bg-[#0f172a] border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:ring-2 focus:ring-blue-500">
                             <button id="search-btn" class="bg-blue-600 px-6 py-2 rounded-lg text-white font-bold hover:bg-blue-700 transition">Search</button>
                         </div>
                         <div id="search-results" class="mt-4 space-y-2"></div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="glass-card p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-bold text-white">Recent Analyses</h2>
                        </div>
                        <div id="recent-analyses-list" class="space-y-4">
                            <p class="text-gray-600 text-sm">Loading history...</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
    /**
     * FETCH PREVIOUS ANALYSES
     */
    async function loadHistory() {
        const recentList = document.getElementById('recent-analyses-list');
        try {
            const res = await fetch('manage_diagnostics.php'); 
            const data = await res.json();
            
            recentList.innerHTML = '';
            if (!data || Object.keys(data).length === 0) {
                recentList.innerHTML = '<p class="text-gray-600 text-sm italic">No records found.</p>';
                return;
            }

            const analyses = Object.entries(data).map(([id, val]) => ({ id, ...val }));
            analyses.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

            analyses.forEach((item) => {
                let statusClass = "text-yellow-500";
                const status = item.status ? item.status.toLowerCase() : "";
                if (status.includes("fair")) statusClass = "text-green-500";
                if (status.includes("high") || status.includes("overcharge")) statusClass = "text-red-500";

                recentList.innerHTML += `
                    <div class="bg-[#0f172a] p-4 rounded-xl border border-gray-800 cursor-pointer hover:border-blue-500 transition" 
                         onclick='renderResult(${JSON.stringify(item).replace(/'/g, "&apos;")})'>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-white font-medium">${item.title}</span>
                            <span class="text-gray-500">${new Date(item.createdAt).toLocaleDateString()}</span>
                        </div>
                        <span class="${statusClass} text-[10px] font-bold uppercase">${item.status}</span>
                    </div>
                `;
            });
        } catch (e) {
            recentList.innerHTML = '<p class="text-red-500 text-xs">Error loading history.</p>';
        }
    }

    /**
     * FILE UPLOAD & AI OCR TRIGGER
     */
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file-input');
    dropArea.onclick = () => fileInput.click();

    fileInput.onchange = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const originalHTML = dropArea.innerHTML;
        dropArea.innerHTML = `
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="text-white">AI is reading bill: ${file.name}...</p>
            </div>
        `;

        const formData = new FormData();
        formData.append('bill', file);
        formData.append('title', file.name.split('.')[0]);

        try {
            const res = await fetch('process_analysis.php', { method: 'POST', body: formData });
            const result = await res.json();

            if (result.success) {
                renderResult(result.data);
                loadHistory(); 
                dropArea.innerHTML = `<p class="text-green-500 font-bold">Analysis Complete!</p>`;
            } else {
                alert("AI Error: " + result.message);
            }
        } catch (error) {
            alert("Upload failed. Check your internet connection.");
        } finally {
            setTimeout(() => dropArea.innerHTML = originalHTML, 3000);
        }
    };

    /**
     * RENDER THE AI JSON RESPONSE
     */
    function renderResult(data) {
        const container = document.getElementById('analysis-result-container');
        container.innerHTML = `
            <div class="glass-card p-6 border-l-4 border-blue-500 shadow-xl animate-pulse-once">
                <div class="flex justify-between items-start mb-4">
                    <span class="bg-blue-900/30 text-blue-400 text-[10px] font-bold px-3 py-1 rounded-full uppercase">${data.status}</span>
                    <span class="text-gray-500 text-sm">${new Date(data.createdAt).toLocaleDateString()}</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Analysis for ${data.title}</h3>
                <p class="text-gray-400 mb-4">${data.description}</p>
                <div class="bg-black/20 p-4 rounded-lg mb-4">
                    <p class="text-white text-sm"><span class="font-bold text-blue-400">AI Insights:</span> ${data.insights}</p>
                </div>
            </div>
        `;
    }

    /**
     * SEARCH FACTORY ISSUES
     */
    document.getElementById('search-btn').onclick = async () => {
        const term = document.getElementById('issue-search').value.toLowerCase();
        const resultsDiv = document.getElementById('search-results');
        
        if (!term) return;
        resultsDiv.innerHTML = '<p class="text-gray-500 text-xs animate-pulse">Searching Database...</p>';

        try {
            const res = await fetch('manage_known_issues.php'); 
            const issues = await res.json();
            
            resultsDiv.innerHTML = '';
            let found = false;

            for (const id in issues) {
                const issue = issues[id];
                if (issue.issue_title.toLowerCase().includes(term) || 
                    issue.description.toLowerCase().includes(term)) {
                    found = true;
                    resultsDiv.innerHTML += `
                        <div class="p-4 bg-[#0f172a] rounded-xl border border-gray-700 hover:border-blue-500 transition animate-pulse-once">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="text-white text-sm font-bold">${issue.issue_title}</h4>
                                <span class="text-[10px] bg-blue-900/30 text-blue-400 px-2 py-0.5 rounded">${issue.model || 'All Models'}</span>
                            </div>
                            <p class="text-gray-400 text-xs leading-relaxed">${issue.description}</p>
                        </div>
                    `;
                }
            }

            if (!found) {
                resultsDiv.innerHTML = '<p class="text-red-400 text-xs p-2 italic">No matching factory issues found.</p>';
            }
        } catch (e) {
            resultsDiv.innerHTML = '<p class="text-red-500 text-xs">Error connecting to database.</p>';
        }
    };

    loadHistory();
    </script>
</body>
</html>