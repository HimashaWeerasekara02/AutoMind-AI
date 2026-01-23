<?php 
require_once 'db.php'; 

// Ensure these variables are captured correctly
$vId = $_GET['vehicle_id'] ?? null;
$vName = $_GET['name'] ?? 'Vehicle';

// If no ID is present, redirect to the main garage page
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
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-900 text-gray-300 p-8">

    <div id="edit-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-gray-800 border border-gray-700 w-full max-w-md rounded-2xl p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-white mb-4">Edit Document</h3>
            <form id="edit-form" class="space-y-4">
                <input type="hidden" id="edit-doc-id">
                
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Document Title</label>
                    <input type="text" id="edit-title" class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white mt-1 outline-none focus:border-blue-500" required>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Expiry Date</label>
                    <input type="date" id="edit-expiry" class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white mt-1 outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Replace Document File (Optional)</label>
                    <input type="file" id="edit-file" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm text-white mt-1 file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded-xl transition">Cancel</button>
                    <button type="submit" id="save-edit-btn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-blue-900/20">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <nav class="mb-8">
            <a href="MyGarage.php" class="text-blue-400 hover:text-blue-300 transition flex items-center gap-2 font-medium">
                <span class="text-xl">←</span> Back to My Garage
            </a>
        </nav>

        <header class="mb-10">
            <h1 class="text-4xl font-bold text-white tracking-tight"><?php echo htmlspecialchars($vName); ?> Glovebox</h1>
            <p class="text-gray-500 mt-2">Manage Insurance, Licenses & Registration for this vehicle.</p>
        </header>

        <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 mb-10 shadow-xl">
            <h2 class="text-lg font-semibold text-white mb-4">Upload New Document</h2>
            <form id="upload-form" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1 block">Document Title</label>
                    <input type="text" id="doc-title" placeholder="e.g. Insurance" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm text-white mb-3 outline-none focus:border-blue-500" required>
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1 block">Select File</label>
                    <input type="file" id="doc-file" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer" required>
                </div>
                <div class="w-full md:w-48">
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1 block">Expiry Date</label>
                    <input type="date" id="expiry-date" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm text-white outline-none focus:border-blue-500">
                </div>
                <button type="submit" id="up-btn" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold transition-all h-[42px]">Upload</button>
            </form>
        </div>

        <div class="flex justify-between items-end mb-6">
            <h2 class="text-2xl font-bold text-white">Stored Documents</h2>
            <span id="doc-count" class="text-sm text-gray-500 italic">0 Files</span>
        </div>

        <div id="doc-list" class="grid gap-4"></div>
    </div>

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
            
            // Map Firebase object and filter by vehicleId
            const docs = Object.entries(data || {})
                .map(([id, val]) => ({ documentId: id, ...val }))
                .filter(doc => doc.vehicleId === vehicleId);

            countSpan.innerText = `${docs.length} Files`;

            if (docs.length === 0) {
                list.innerHTML = `<div class="text-center py-16 bg-gray-800/30 rounded-2xl border-2 border-dashed border-gray-700"><p class="text-gray-500">Glovebox is empty.</p></div>`;
                return;
            }

            const today = new Date().toISOString().split('T')[0];

            docs.forEach((doc) => {
                const isExpired = doc.expiryDate && doc.expiryDate < today;
                list.innerHTML += `
                <div class="bg-gray-800 p-5 rounded-xl border ${isExpired ? 'border-red-500/50 shadow-red-900/10' : 'border-gray-700'} flex justify-between items-center hover:border-blue-500/50 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center text-2xl">
                            ${doc.fileUrl && doc.fileUrl.toLowerCase().endsWith('pdf') ? '📄' : '🖼️'}
                        </div>
                        <div>
                            <p class="text-white font-medium group-hover:text-blue-400 transition-colors">${doc.title}</p>
                            <p class="text-[10px] ${isExpired ? 'text-red-400 font-bold' : 'text-gray-500'}">
                                ${doc.expiryDate ? 'EXPIRES: ' + doc.expiryDate : 'NO EXPIRY'} ${isExpired ? '⚠️ EXPIRED' : ''}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="${doc.fileUrl}" target="_blank" class="bg-gray-700 hover:bg-gray-600 text-white p-2 px-4 rounded-lg text-sm font-semibold transition">View</a>
                        <button onclick="openEditModal('${doc.documentId}', '${doc.title}', '${doc.expiryDate || ''}')" class="bg-gray-700 hover:bg-blue-900/40 text-blue-400 p-2 px-3 rounded-lg transition">✎</button>
                        <button onclick="deleteDoc('${doc.documentId}')" class="bg-gray-700 hover:bg-red-900/40 text-red-400 p-2 px-3 rounded-lg transition">🗑️</button>
                    </div>
                </div>`;
            });
        } catch (e) { 
            list.innerHTML = "<p class='text-red-500 text-center'>Error loading documents.</p>"; 
        }
    }

    // --- EDIT LOGIC ---
    function openEditModal(id, title, expiry) {
        document.getElementById('edit-doc-id').value = id;
        document.getElementById('edit-title').value = title;
        document.getElementById('edit-expiry').value = expiry;
        document.getElementById('edit-file').value = ""; 
        editModal.classList.replace('hidden', 'flex');
    }

    function closeEditModal() { editModal.classList.replace('flex', 'hidden'); }

    document.getElementById('edit-form').onsubmit = async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit-doc-id').value;
        const btn = document.getElementById('save-edit-btn');
        btn.disabled = true;
        btn.innerText = "Saving...";

        const formData = new FormData();
        formData.append('title', document.getElementById('edit-title').value);
        formData.append('expiryDate', document.getElementById('edit-expiry').value);
        
        const fileInput = document.getElementById('edit-file');
        if (fileInput.files[0]) {
            formData.append('document', fileInput.files[0]);
        }

        const res = await fetch(`manage_document.php?id=${id}`, {
            method: 'POST', 
            body: formData
        });

        const result = await res.json();
        if(result.success) {
            closeEditModal();
            loadDocs();
        } else {
            alert("Update failed: " + result.error);
        }
        btn.disabled = false;
        btn.innerText = "Save Changes";
    };

    // --- UPLOAD LOGIC ---
    document.getElementById('upload-form').onsubmit = async (e) => {
        e.preventDefault();
        const btn = document.getElementById('up-btn');
        btn.disabled = true;
        btn.innerText = "Uploading...";

        const formData = new FormData();
        formData.append('document', document.getElementById('doc-file').files[0]);
        formData.append('title', document.getElementById('doc-title').value);
        formData.append('vehicleId', vehicleId);
        formData.append('expiryDate', document.getElementById('expiry-date').value);

        try {
            const res = await fetch('upload_document.php', { method: 'POST', body: formData });
            const result = await res.json();
            if(result.success) {
                e.target.reset();
                loadDocs();
            } else {
                alert("Upload failed: " + result.error);
            }
        } catch (err) {
            alert("Network error.");
        }
        btn.disabled = false;
        btn.innerText = "Upload";
    };

    async function deleteDoc(id) {
        if(!confirm("Are you sure you want to delete this document?")) return;
        const res = await fetch(`manage_document.php?id=${id}`, { method: 'DELETE' });
        const result = await res.json();
        if(result.success) loadDocs();
    }

    loadDocs();
</script>
</body>
</html>