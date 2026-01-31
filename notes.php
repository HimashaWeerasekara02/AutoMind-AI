<?php 
require_once 'db.php'; 
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Notes - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        .note-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; transition: all 0.2s ease-in-out; }
        .note-card:hover { transform: translateY(-4px); border-color: #4f46e5; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        select, input, textarea { background-color: #0f172a !important; border-color: #334155 !important; color: white !important; }
        select:focus, input:focus, textarea:focus { border-color: #6366f1 !important; outline: none; ring: 2px; ring-color: #6366f1; }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            
            <div class="mb-6">
                <a href="dashboard.php" class="inline-flex items-center text-xs font-bold uppercase tracking-[0.2em] text-gray-500 hover:text-indigo-400 transition-colors group">
                    <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Command Center
                </a>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">Vehicle Notes</h1>
                    <p class="text-gray-400 mt-2">Manage part numbers and technical reminders.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-64">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1 ml-1">Select Car</label>
                        <select id="vehicle-select" class="w-full rounded-lg px-4 py-2.5 shadow-inner border transition">
                            <option value="" disabled selected>Loading Vehicles...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div onclick="openModal()" class="note-card border-dashed border-2 flex flex-col items-center justify-center p-8 bg-transparent cursor-pointer hover:bg-gray-800/20 group">
                    <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-500 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <p class="mt-4 font-bold text-xs uppercase tracking-widest text-gray-500 group-hover:text-gray-300">Create New Note</p>
                </div>
                <div id="notes-container" class="contents"></div>
            </div>

            <div id="empty-state" class="hidden py-20 text-center col-span-full">
                <p class="text-gray-500 italic">No notes found for this vehicle.</p>
            </div>
        </div>
    </main>

    <div id="note-modal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-[#1e293b] p-8 rounded-2xl w-full max-w-lg shadow-2xl border border-gray-700">
            <h2 id="modal-title" class="text-2xl text-white font-bold mb-6">Vehicle Note</h2>
            <form id="note-form" class="space-y-5">
                <input type="hidden" id="form-id">
                <input type="text" id="form-title" placeholder="Title" required class="w-full rounded-lg px-4 py-3">
                <textarea id="form-content" rows="4" placeholder="Details..." required class="w-full rounded-lg px-4 py-3 resize-none"></textarea>
                <select id="form-category" class="w-full rounded-lg px-4 py-3">
                    <option value="General">General</option>
                    <option value="Parts">Parts</option>
                    <option value="Technical">Technical</option>
                </select>
                <div class="flex justify-end gap-3 pt-6">
                    <button type="button" onclick="closeModal()" class="px-6 py-2.5 text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit" id="save-btn" class="bg-indigo-600 text-white font-bold px-10 py-2.5 rounded-lg">Save Note</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const vehicleSelect = document.getElementById('vehicle-select');
        const container = document.getElementById('notes-container');
        const noteModal = document.getElementById('note-modal');

        async function init() {
            const res = await fetch('get_vehicles.php');
            const data = await res.json();
            vehicleSelect.innerHTML = '<option value="" disabled selected>Select Vehicle</option>';
            for (const id in data) {
                const opt = document.createElement('option');
                opt.value = id;
                opt.textContent = data[id].nickname || `${data[id].make} ${data[id].model}`;
                vehicleSelect.appendChild(opt);
            }
        }

        async function fetchNotes() {
            const vId = vehicleSelect.value;
            if(!vId) return;
            const res = await fetch(`manage_notes.php?vehicleId=${vId}`);
            const data = await res.json();
            renderNotes(data);
        }

        function renderNotes(data) {
            container.innerHTML = Object.entries(data).map(([id, note]) => `
                <div class="note-card p-6 flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-indigo-400 text-[10px] font-bold uppercase">${note.category}</span>
                        <div class="flex gap-2">
                            <button onclick='editNote("${id}", ${JSON.stringify(note).replace(/'/g, "&apos;")})' class="text-gray-500 hover:text-white text-xs">Edit</button>
                            <button onclick="deleteNote('${id}')" class="text-gray-500 hover:text-red-500 text-xs">Delete</button>
                        </div>
                    </div>
                    <h3 class="text-white font-bold text-lg mb-2">${note.title}</h3>
                    <p class="text-gray-400 text-sm whitespace-pre-wrap">${note.content}</p>
                </div>
            `).join('');
        }

        document.getElementById('note-form').onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById('form-id').value;
            const payload = {
                vehicleId: vehicleSelect.value,
                title: document.getElementById('form-title').value,
                content: document.getElementById('form-content').value,
                category: document.getElementById('form-category').value
            };
            const method = id ? 'PATCH' : 'POST';
            const url = id ? `manage_notes.php?id=${id}` : 'manage_notes.php';
            
            await fetch(url, { method, body: JSON.stringify(payload) });
            closeModal();
            fetchNotes();
        };

        window.deleteNote = async (id) => {
            if(!confirm("Delete this note?")) return;
            const vId = vehicleSelect.value;
            await fetch(`manage_notes.php?id=${id}&vehicleId=${vId}`, { method: 'DELETE' });
            fetchNotes();
        };

        window.openModal = () => {
            if(!vehicleSelect.value) return alert("Select a vehicle first");
            document.getElementById('note-form').reset();
            document.getElementById('form-id').value = "";
            noteModal.classList.replace('hidden', 'flex');
        };
        window.closeModal = () => noteModal.classList.replace('flex', 'hidden');
        window.editNote = (id, note) => {
            document.getElementById('form-id').value = id;
            document.getElementById('form-title').value = note.title;
            document.getElementById('form-content').value = note.content;
            document.getElementById('form-category').value = note.category;
            noteModal.classList.replace('hidden', 'flex');
        };

        vehicleSelect.onchange = fetchNotes;
        init();
    </script>
</body>
</html>