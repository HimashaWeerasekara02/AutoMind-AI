<?php 
require_once 'db.php'; 

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
</head>
<body class="bg-gray-900 text-gray-300 p-8">

    <div id="edit-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-gray-800 border border-gray-700 w-full max-w-md rounded-2xl p-6 shadow-2xl">
            <h3 class="text-xl font-bold text-white mb-4">Edit Document</h3>
            <form id="edit-form" class="space-y-4">
                <input type="hidden" id="edit-doc-id">
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Document Name</label>
                    <input type="text" id="edit-name" class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white mt-1 outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Expiry Date</label>
                    <input type="date" id="edit-expiry" class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white mt-1 outline-none focus:border-blue-500">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded-xl transition">Cancel</button>
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-blue-900/20">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <nav class="mb-8">
            <a href="MyGarage.php" class="text-blue-400 hover:text-blue-300 transition flex items-center gap-2">
                <span>←</span> Back to My Garage
            </a>
        </nav>

        <header class="mb-10">
            <h1 class="text-4xl font-bold text-white"><?php echo htmlspecialchars($vName); ?></h1>
            <p class="text-gray-500 mt-2">Manage Insurance, Licenses & Registration</p>
        </header>

        <div class="bg-gray-800 p-6 rounded-2xl border border-gray-700 mb-10 shadow-xl">
            <h2 class="text-lg font-semibold text-white mb-4">Upload New Document</h2>
            <form id="upload-form" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1 block">Select File</label>
                    <input type="file" id="doc-file" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm file:mr-4 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer" required>
                </div>
                <div class="w-full md:w-48">
                    <label class="text-[10px] font-bold text-gray-500 uppercase ml-1 mb-1 block">Expiry Date</label>
                    <input type="date" id="expiry-date" class="w-full bg-gray-900 border border-gray-700 p-2 rounded-lg text-sm text-white">
                </div>
                <button type="submit" id="up-btn" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold transition-all">Upload</button>
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
            const res = await fetch('manage_document.php'); 
            const data = await res.json();
            list.innerHTML = "";
            const docs = Object.entries(data || {}).filter(([id, doc]) => doc.vehicle_id === vehicleId);
            countSpan.innerText = `${docs.length} Files`;

            if (docs.length === 0) {
                list.innerHTML = `<div class="text-center py-16 bg-gray-800/30 rounded-2xl border-2 border-dashed border-gray-700"><p class="text-gray-500">Glovebox is empty.</p></div>`;
                return;
            }

            const today = new Date().toISOString().split('T')[0];

            docs.forEach(([id, doc]) => {
                const isExpired = doc.expiry_date && doc.expiry_date < today;
                list.innerHTML += `
                <div class="bg-gray-800 p-5 rounded-xl border ${isExpired ? 'border-red-500/50 shadow-red-900/10' : 'border-gray-700'} flex justify-between items-center hover:border-blue-500/50 transition-all group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-900 rounded-lg flex items-center justify-center text-2xl">
                            ${doc.file_name.toLowerCase().endsWith('pdf') ? '📄' : '🖼️'}
                        </div>
                        <div>
                            <p class="text-white font-medium group-hover:text-blue-400 transition-colors">${doc.file_name}</p>
                            <p class="text-[10px] ${isExpired ? 'text-red-400 font-bold' : 'text-gray-500'}">
                                ${doc.expiry_date ? 'EXPIRES: ' + doc.expiry_date : 'NO EXPIRY'} ${isExpired ? '⚠️' : ''}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="${doc.file_path}" target="_blank" class="bg-gray-700 hover:bg-gray-600 text-white p-2 px-4 rounded-lg text-sm font-semibold">View</a>
                        <button onclick="openEditModal('${id}', '${doc.file_name}', '${doc.expiry_date || ''}')" class="bg-gray-700 hover:bg-blue-900/40 text-blue-400 p-2 px-3 rounded-lg">✎</button>
                        <button onclick="deleteDoc('${id}')" class="bg-gray-700 hover:bg-red-900/40 text-red-400 p-2 px-3 rounded-lg">🗑️</button>
                    </div>
                </div>`;
            });
        } catch (e) { list.innerHTML = "<p class='text-red-500'>Error loading.</p>"; }
    }

    // --- EDIT LOGIC ---
    function openEditModal(id, name, expiry) {
        document.getElementById('edit-doc-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-expiry').value = expiry;
        editModal.classList.replace('hidden', 'flex');
    }

    function closeEditModal() { editModal.classList.replace('flex', 'hidden'); }

    document.getElementById('edit-form').onsubmit = async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit-doc-id').value;
        const payload = {
            file_name: document.getElementById('edit-name').value,
            expiry_date: document.getElementById('edit-expiry').value
        };

        const res = await fetch(`manage_document.php?id=${id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        if(result.success) {
            closeEditModal();
            loadDocs();
        }
    };

    // --- UPLOAD LOGIC ---
    document.getElementById('upload-form').onsubmit = async (e) => {
        e.preventDefault();
        const btn = document.getElementById('up-btn');
        btn.disabled = true;
        const formData = new FormData();
        formData.append('document', document.getElementById('doc-file').files[0]);
        formData.append('vehicle_id', vehicleId);
        formData.append('expiry_date', document.getElementById('expiry-date').value);

        const res = await fetch('upload_document.php', { method: 'POST', body: formData });
        const result = await res.json();
        if(result.success) {
            e.target.reset();
            loadDocs();
        }
        btn.disabled = false;
    };

    async function deleteDoc(id) {
        if(!confirm("Delete this document?")) return;
        const res = await fetch(`manage_document.php?id=${id}`, { method: 'DELETE' });
        const result = await res.json();
        if(result.success) loadDocs();
    }

    loadDocs();
</script>
</body>
</html>