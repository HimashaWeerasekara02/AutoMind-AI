<?php 
require_once 'db.php'; 
session_start();

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$vId = $_GET['vehicle_id'] ?? null;
$vName = $_GET['name'] ?? 'Vehicle';

if (!$vId) {
    header("Location: MyGarage.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vName); ?> - Glovebox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="antialiased p-8">

    <?php include 'sidebar.php'; ?>

    <div id="edit-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-gray-800 border border-gray-700 w-full max-w-md rounded-2xl p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-white mb-4 italic uppercase">Edit Document</h3>
            <form id="edit-form" class="space-y-4">
                <input type="hidden" id="edit-doc-id">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Document Title</label>
                    <input type="text" id="edit-title" class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white mt-1 outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Expiry Date</label>
                    <input type="date" id="edit-expiry" class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white mt-1 outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Replace File (Optional)</label>
                    <input type="file" id="edit-file" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm text-white mt-1">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded-xl transition uppercase text-xs">Cancel</button>
                    <button type="submit" id="save-edit-btn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-blue-900/20 uppercase text-xs">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <main class="lg:ml-64 max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="MyGarage.php" class="inline-flex items-center text-xs font-bold uppercase tracking-[0.2em] text-gray-500 hover:text-blue-400 transition-colors group">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to My Garage
            </a>
        </div>

        <header class="mb-10">
            <h1 class="text-4xl font-bold text-white tracking-tight italic uppercase"><?php echo htmlspecialchars($vName); ?> Glovebox</h1>
            <p class="text-gray-400 mt-2">Centralized storage for insurance, licenses, and registration.</p>
        </header>

        <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 mb-10 shadow-xl">
            <h2 class="text-lg font-semibold text-white mb-4 uppercase italic">Upload New Document</h2>
            <form id="upload-form" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1 block">Document Title</label>
                    <input type="text" id="doc-title" placeholder="e.g. Insurance" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm text-white mb-3 outline-none focus:border-blue-500" required>
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1 block">Select File</label>
                    <input type="file" id="doc-file" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm text-gray-400 cursor-pointer file:bg-blue-600 file:border-none file:text-white file:px-3 file:py-1 file:rounded-full file:text-xs file:font-bold hover:file:bg-blue-500" required>
                </div>
                <div class="w-full md:w-48">
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1 block">Expiry Date</label>
                    <input type="date" id="expiry-date" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm text-white outline-none focus:border-blue-500">
                </div>
                <button type="submit" id="up-btn" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold transition-all h-[42px] shadow-lg shadow-blue-900/20 uppercase text-xs">Upload</button>
            </form>
        </div>

        <div class="flex justify-between items-end mb-6">
            <h2 class="text-2xl font-bold text-white italic uppercase">Stored Documents</h2>
            <span id="doc-count" class="text-sm text-gray-500 italic">0 Files</span>
        </div>

        <div id="doc-list" class="grid gap-4"></div>
    </main>

<script>
    const vehicleId = "<?php echo $vId; ?>";
    const editModal = document.getElementById('edit-modal');

    async function loadDocs() {
        const list = document.getElementById('doc-list');
        const countSpan = document.getElementById('doc-count');
        try {
            const res = await fetch(`manage_document.php`); 
            const data = await res.json();
            list.innerHTML = "";
            
            const docs = Object.entries(data || {})
                .map(([id, val]) => ({ documentId: id, ...val }))
                .filter(doc => doc.vehicleId === vehicleId);

            countSpan.innerText = `${docs.length} Files`;

            if (docs.length === 0) {
                list.innerHTML = `
                <div class="text-center py-16 bg-gray-800/30 rounded-2xl border-2 border-dashed border-gray-700">
                    <p class="text-gray-500 uppercase text-[10px] font-bold tracking-widest">Glovebox is empty</p>
                </div>`;
                return;
            }

            const today = new Date().toISOString().split('T')[0];

            docs.forEach((doc) => {
                const isExpired = doc.expiryDate && doc.expiryDate < today;
                list.innerHTML += `
                <div class="bg-gray-800 p-5 rounded-xl border ${isExpired ? 'border-red-500/50 shadow-red-900/10' : 'border-gray-700'} flex justify-between items-center hover:border-blue-500/50 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center text-xl shadow-inner">
                            ${doc.fileUrl && doc.fileUrl.toLowerCase().endsWith('pdf') ? '📄' : '🖼️'}
                        </div>
                        <div>
                            <p class="text-white font-bold group-hover:text-blue-400 transition-colors uppercase text-sm">${doc.title}</p>
                            <p class="text-[10px] ${isExpired ? 'text-red-400 font-bold' : 'text-gray-500'} tracking-widest">
                                ${doc.expiryDate ? 'EXP: ' + doc.expiryDate : 'PERMANENT'} ${isExpired ? '⚠️ EXPIRED' : ''}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="${doc.fileUrl}" target="_blank" class="bg-gray-700 hover:bg-gray-600 text-white p-2 px-4 rounded-lg text-[10px] font-bold transition flex items-center">VIEW</a>
                        
                        <button onclick="openEditModal('${doc.documentId}', '${doc.title}', '${doc.expiryDate || ''}')" 
                                class="bg-gray-700 hover:bg-blue-900/40 text-blue-400 p-2 px-3 rounded-lg transition" title="Edit">
                            ✎
                        </button>
                        
                        <button onclick="deleteDoc('${doc.documentId}')" 
                                class="bg-gray-700 hover:bg-red-900/40 text-red-400 p-2 px-3 rounded-lg transition" title="Delete">
                            🗑️
                        </button>
                    </div>
                </div>`;
            });
        } catch (e) { 
            list.innerHTML = "<p class='text-red-500 text-center font-bold uppercase text-xs tracking-widest'>Sync Error. Check Connection.</p>"; 
        }
    }

    // Modal UI Handlers
    function openEditModal(id, title, expiry) {
        document.getElementById('edit-doc-id').value = id;
        document.getElementById('edit-title').value = title;
        document.getElementById('edit-expiry').value = expiry;
        editModal.classList.replace('hidden', 'flex');
    }
    function closeEditModal() { editModal.classList.replace('flex', 'hidden'); }

    // CRUD Logic
    document.getElementById('edit-form').onsubmit = async (e) => {
        e.preventDefault();
        const btn = document.getElementById('save-edit-btn');
        btn.disabled = true;
        btn.innerText = "Processing...";

        const formData = new FormData();
        formData.append('title', document.getElementById('edit-title').value);
        formData.append('expiryDate', document.getElementById('edit-expiry').value);
        if (document.getElementById('edit-file').files[0]) {
            formData.append('document', document.getElementById('edit-file').files[0]);
        }

        const res = await fetch(`manage_document.php?id=${document.getElementById('edit-doc-id').value}`, { 
            method: 'POST', 
            body: formData 
        });
        const result = await res.json();
        if(result.success) { 
            closeEditModal(); 
            loadDocs(); 
        }
        btn.disabled = false;
        btn.innerText = "Save Changes";
    };

    document.getElementById('upload-form').onsubmit = async (e) => {
        e.preventDefault();
        const btn = document.getElementById('up-btn');
        btn.disabled = true;
        btn.innerText = "...";

        const formData = new FormData();
        formData.append('document', document.getElementById('doc-file').files[0]);
        formData.append('title', document.getElementById('doc-title').value);
        formData.append('vehicleId', vehicleId);
        formData.append('expiryDate', document.getElementById('expiry-date').value);

        const res = await fetch('upload_document.php', { method: 'POST', body: formData });
        const result = await res.json();
        if(result.success) { 
            e.target.reset(); 
            loadDocs(); 
        }
        btn.disabled = false;
        btn.innerText = "Upload";
    };

    async function deleteDoc(id) {
        if(!confirm("Permanently delete this document?")) return;
        const res = await fetch(`manage_document.php?id=${id}`, { method: 'DELETE' });
        const result = await res.json();
        if(result.success) loadDocs();
    }

    loadDocs();
</script>
</body>
</html>