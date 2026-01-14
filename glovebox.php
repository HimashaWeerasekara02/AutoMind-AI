<?php 
require_once 'db.php'; 
$vId = $_GET['vehicle_id'] ?? '';
$vName = $_GET['name'] ?? 'Vehicle';
if (!$vId) { header("Location: MyGarage.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $vName; ?> - Glovebox</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-300 p-8">
    <div class="max-w-4xl mx-auto">
        <a href="MyGarage.php" class="text-blue-400 hover:underline">← Back to Garage</a>
        <h1 class="text-3xl font-bold text-white mt-4 mb-8"><?php echo $vName; ?> Glovebox</h1>

        <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 mb-8">
            <h2 class="text-xl text-white font-bold mb-4">Upload New Document</h2>
            <form id="upload-form" class="flex flex-col md:flex-row gap-4">
                <input type="file" id="doc-file" class="bg-gray-900 p-2 rounded border border-gray-700 flex-1" required>
                <button type="submit" id="up-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition">
                    Upload
                </button>
            </form>
        </div>

        <div id="doc-list" class="grid gap-4">
            <p class="text-center py-10 text-gray-600">Loading documents...</p>
        </div>
    </div>

<script>
    const vId = "<?php echo $vId; ?>";

    async function loadDocs() {
        const list = document.getElementById('doc-list');
        try {
            const res = await fetch('manage_document.php'); 
            const data = await res.json();
            list.innerHTML = "";

            // Filter documents that belong to this vehicle_id
            const docs = Object.entries(data || {}).filter(([id, doc]) => doc.vehicle_id === vId);

            if (docs.length === 0) {
                list.innerHTML = "<div class='text-center py-10 bg-gray-800/50 rounded-xl border-2 border-dashed border-gray-700'>No documents found.</div>";
                return;
            }

            docs.forEach(([id, doc]) => {
                list.innerHTML += `
                <div class="bg-gray-800 p-4 rounded-lg border border-gray-700 flex justify-between items-center hover:border-blue-500 transition">
                    <div>
                        <p class="text-white font-bold">${doc.file_name}</p>
                        <p class="text-xs text-gray-500">Uploaded: ${doc.uploaded_at}</p>
                    </div>
                    <div class="flex gap-4">
                        <a href="${doc.file_path}" target="_blank" class="text-blue-400 font-semibold hover:text-blue-300">View</a>
                        <button onclick="deleteDoc('${id}')" class="text-red-500 hover:text-red-400">Delete</button>
                    </div>
                </div>`;
            });
        } catch (e) { list.innerHTML = "Error loading documents."; }
    }

    document.getElementById('upload-form').onsubmit = async (e) => {
        e.preventDefault();
        const btn = document.getElementById('up-btn');
        const fileInput = document.getElementById('doc-file');
        
        btn.disabled = true;
        btn.innerText = "Uploading...";

        const formData = new FormData();
        formData.append('document', fileInput.files[0]);
        formData.append('vehicle_id', vId);

        try {
            const res = await fetch('upload_document.php', { method: 'POST', body: formData });
            const result = await res.json();
            if(result.success) {
                fileInput.value = "";
                loadDocs();
            } else { alert(result.error); }
        } catch (err) { alert("Upload failed"); }
        finally {
            btn.disabled = false;
            btn.innerText = "Upload";
        }
    };

    async function deleteDoc(id) {
        if(!confirm("Delete this document forever?")) return;
        await fetch(`manage_document.php?id=${id}`, { method: 'DELETE' });
        loadDocs();
    }

    loadDocs();
</script>
</body>
</html>