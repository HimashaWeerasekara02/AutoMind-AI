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

    <!-- Main Content -->
    <main class="ml-64 p-8 min-h-screen">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
                <div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight">Personalized Vehicle Notes</h1>
                    <p class="text-gray-400 mt-2">Manage part numbers, technical details, and custom reminders for your fleet.</p>
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

            <!-- Notes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Add New Note Trigger Card -->
                <div onclick="openModal()" class="note-card border-dashed border-2 flex flex-col items-center justify-center p-8 bg-transparent cursor-pointer hover:bg-gray-800/20 group">
                    <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-500 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <p class="mt-4 font-bold text-xs uppercase tracking-widest text-gray-500 group-hover:text-gray-300">Create New Note</p>
                </div>

                <!-- Container for notes loaded from DB -->
                <div id="notes-container" class="contents">
                    <!-- Notes will appear here -->
                </div>
            </div>

            <!-- Loading/Empty State -->
            <div id="empty-state" class="hidden py-20 text-center col-span-full">
                <svg class="w-16 h-16 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-gray-500 italic">No notes found for this vehicle.</p>
            </div>
        </div>
    </main>

    <!-- Note Modal -->
    <div id="note-modal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-50 backdrop-blur-sm p-4">
        <div class="bg-[#1e293b] p-8 rounded-2xl w-full max-w-lg shadow-2xl border border-gray-700">
            <div class="flex justify-between items-center mb-6">
                <h2 id="modal-title" class="text-2xl text-white font-bold">Vehicle Note</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form id="note-form" class="space-y-5">
                <input type="hidden" id="form-id">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 tracking-wider">Note Title</label>
                    <input type="text" id="form-title" placeholder="e.g. Oil Filter Type" required class="w-full rounded-lg px-4 py-3 shadow-inner">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 tracking-wider">Note Details</label>
                    <textarea id="form-content" rows="4" placeholder="Enter measurements, part numbers, or reminders..." required class="w-full rounded-lg px-4 py-3 shadow-inner resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 tracking-wider">Category</label>
                        <select id="form-category" class="w-full rounded-lg px-4 py-3 shadow-inner">
                            <option value="General">General</option>
                            <option value="Parts">Parts</option>
                            <option value="Reminder">Reminder</option>
                            <option value="Technical">Technical</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-700">
                    <button type="button" onclick="closeModal()" class="px-6 py-2.5 text-gray-400 hover:text-white font-medium transition">Cancel</button>
                    <button type="submit" id="save-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-10 py-2.5 rounded-lg transition-all shadow-lg shadow-indigo-900/40">
                        Save Note
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const vehicleSelect = document.getElementById('vehicle-select');
        const container = document.getElementById('notes-container');
        const emptyState = document.getElementById('empty-state');
        const noteModal = document.getElementById('note-modal');

        // 1. Initial Load: Get Vehicles
        async function init() {
            console.log("AutoMind AI: Initializing vehicles...");
            try {
                const res = await fetch('get_vehicles.php');
                if (!res.ok) throw new Error("Server returned error " + res.status);
                const data = await res.json();
                
                vehicleSelect.innerHTML = '<option value="" disabled selected>Select Vehicle</option>';
                
                if (data && Object.keys(data).length > 0) {
                    for (const id in data) {
                        const v = data[id];
                        const opt = document.createElement('option');
                        opt.value = id;
                        opt.textContent = v.nickname || `${v.year} ${v.make} ${v.model}`;
                        vehicleSelect.appendChild(opt);
                    }
                } else {
                    vehicleSelect.innerHTML = '<option value="" disabled>No vehicles in garage</option>';
                }
            } catch (e) {
                console.error("AutoMind AI: Failed to load vehicles", e);
                vehicleSelect.innerHTML = '<option value="" disabled>Error connecting to DB</option>';
            }
        }

        // 2. Fetch Notes for Selected Vehicle
        async function fetchNotes() {
            const vId = vehicleSelect.value;
            if(!vId) return;

            container.innerHTML = '<div class="col-span-full py-10 text-center text-gray-500 animate-pulse">Retrieving notes...</div>';
            emptyState.classList.add('hidden');

            try {
                const res = await fetch(`manage_notes.php?vehicleId=${vId}`);
                if (!res.ok) throw new Error("Fetch failed with status " + res.status);
                const data = await res.json();
                renderNotes(data);
            } catch (e) {
                console.error("AutoMind AI: Error fetching notes", e);
                container.innerHTML = '<div class="col-span-full py-10 text-center text-red-500">Error loading notes. check manage_notes.php.</div>';
            }
        }

        // 3. Render Notes to Grid
        function renderNotes(data) {
            if (!data || Object.keys(data).length === 0) {
                container.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }

            container.innerHTML = Object.entries(data).map(([id, note]) => `
                <div class="note-card p-6 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-4">
                        <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 text-[10px] font-bold uppercase tracking-widest border border-indigo-500/20">
                            ${note.category || 'General'}
                        </span>
                        <div class="flex gap-1">
                             <button onclick='editNote("${id}", ${JSON.stringify(note).replace(/'/g, "&apos;")})' class="p-1.5 text-gray-500 hover:text-white transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                             </button>
                             <button onclick="deleteNote('${id}')" class="p-1.5 text-gray-500 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                             </button>
                        </div>
                    </div>
                    <h3 class="text-white font-bold text-lg mb-2 leading-tight">${note.title}</h3>
                    <p class="text-gray-400 text-sm leading-relaxed whitespace-pre-wrap flex-1">${note.content}</p>
                    <div class="mt-6 pt-4 border-t border-gray-700/50 flex items-center justify-between">
                        <span class="text-[10px] text-gray-600 font-medium italic">
                            ${note.createdAt ? new Date(note.createdAt).toLocaleDateString(undefined, {year: 'numeric', month: 'short', day: 'numeric'}) : ''}
                        </span>
                    </div>
                </div>
            `).join('');
        }

        // 4. Modal Interactions
        window.openModal = () => {
            if(!vehicleSelect.value) {
                alert("Please select a vehicle from the dropdown first.");
                return;
            }
            document.getElementById('note-form').reset();
            document.getElementById('form-id').value = "";
            document.getElementById('modal-title').innerText = "Create New Note";
            noteModal.classList.replace('hidden', 'flex');
        };

        window.closeModal = () => noteModal.classList.replace('flex', 'hidden');

        window.editNote = (id, note) => {
            document.getElementById('form-id').value = id;
            document.getElementById('form-title').value = note.title;
            document.getElementById('form-content').value = note.content;
            document.getElementById('form-category').value = note.category || 'General';
            document.getElementById('modal-title').innerText = "Edit Vehicle Note";
            noteModal.classList.replace('hidden', 'flex');
        };

        // 5. Submit Handler (Save/Update)
        document.getElementById('note-form').onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById('form-id').value;
            const saveBtn = document.getElementById('save-btn');
            
            const payload = {
                userId: "user_jane_01", // Mocked user ID
                vehicleId: vehicleSelect.value,
                title: document.getElementById('form-title').value.trim(),
                content: document.getElementById('form-content').value.trim(),
                category: document.getElementById('form-category').value,
                createdAt: id ? undefined : new Date().toISOString() 
            };

            saveBtn.disabled = true;
            saveBtn.innerText = "Processing...";

            const url = id ? `manage_notes.php?id=${id}` : 'manage_notes.php';
            const method = id ? 'PATCH' : 'POST';

            try {
                console.log(`AutoMind AI: Sending ${method} request to ${url}`);
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                if (!res.ok) {
                    const errorText = await res.text();
                    throw new Error(`Server returned ${res.status}: ${errorText}`);
                }

                const result = await res.json();
                if(result.success) {
                    console.log("AutoMind AI: Note saved successfully");
                    closeModal();
                    fetchNotes();
                } else {
                    alert("Database Error: " + (result.error || "Could not save."));
                }
            } catch (err) {
                console.error("AutoMind AI: Save connection error", err);
                alert("Connection failed! Make sure manage_notes.php is in your htdocs folder and XAMPP is running.");
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerText = "Save Note";
            }
        };

        // 6. Delete Handler
        window.deleteNote = async (id) => {
            if(!confirm("Are you sure you want to delete this note permanentely?")) return;
            try {
                const res = await fetch(`manage_notes.php?id=${id}`, { method: 'DELETE' });
                const result = await res.json();
                if(result.success) fetchNotes();
                else alert("Delete failed: " + result.error);
            } catch (e) {
                console.error("AutoMind AI: Delete request failed", e);
            }
        };

        // Events
        vehicleSelect.onchange = fetchNotes;
        init();
    </script>
</body>
</html>