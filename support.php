<?php 
require_once 'db.php'; // Keep this for session/sidebar consistency
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - AutoMind AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; color: #d1d5db; }
        .glass-card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; }
        input, textarea, select { background-color: #0f172a !important; border: 1px solid #334155 !important; color: white !important; }
        input:focus, textarea:focus, select:focus { border-color: #6366f1 !important; outline: none; ring: 2px; ring-color: #6366f1; }
        
        #toast { visibility: hidden; opacity: 0; transform: translateY(-20px); transition: all 0.3s ease; }
        #toast.show { visibility: visible; opacity: 1; transform: translateY(0); }
        #sidebar-menu { transition: transform 0.3s ease-in-out; }

        .status-pill { padding: 2px 8px; border-radius: 99px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-resolved { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
    </style>
</head>
<body class="antialiased">

    <?php include 'sidebar.php'; ?>

    <main class="lg:ml-64 p-4 md:p-8 min-h-screen transition-all duration-300">
        <div id="toast" class="fixed top-5 right-5 bg-indigo-600 text-white px-6 py-3 rounded-lg shadow-2xl z-[110] flex items-center gap-2">
            <span class="material-symbols-outlined">send</span>
            <span id="toast-message">Request sent!</span>
        </div>

        <div class="max-w-5xl mx-auto">
            <div class="mb-10">
                <h1 class="text-3xl font-extrabold text-white tracking-tight italic uppercase">Help & Support</h1>
                <p class="text-gray-400 mt-2">Get assistance with your vehicle diagnostics or account management.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
                <div class="lg:col-span-1 space-y-6">
                    <div class="glass-card p-6">
                        <h3 class="text-white font-bold mb-4 flex items-center gap-2 italic uppercase text-sm tracking-wider">
                            <span class="material-symbols-outlined text-indigo-400">quiz</span> Quick FAQ
                        </h3>
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="text-indigo-300 font-medium">How is maintenance predicted?</p>
                                <p class="text-gray-500 mt-1 italic text-xs">AI analyzes your vehicle logs and mileage patterns.</p>
                            </div>
                            <div>
                                <p class="text-indigo-300 font-medium">Support Hours?</p>
                                <p class="text-gray-500 mt-1 italic text-xs">Mon-Fri, 9AM - 6PM EST.</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card p-6 bg-indigo-600/5 border-indigo-500/20">
                        <h3 class="text-white font-bold mb-2 italic uppercase text-sm tracking-wider">Direct Contact</h3>
                        <p class="text-xs text-gray-400 mb-4 uppercase">Response: <span class="text-white font-medium">~24 Hours</span></p>
                        <a href="mailto:support@automind.ai" class="flex items-center gap-2 text-indigo-400 hover:text-indigo-300 transition font-medium text-sm">
                            <span class="material-symbols-outlined text-sm">mail</span> support@automind.ai
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="glass-card p-6 md:p-8 border-t-4 border-t-indigo-500">
                        <form id="support-form" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Subject</label>
                                    <input type="text" id="subject" placeholder="e.g. AI Error" class="w-full rounded-xl px-4 py-3" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Category</label>
                                    <select id="category" class="w-full rounded-xl px-4 py-3">
                                        <option value="Technical">Technical Issue</option>
                                        <option value="Billing">Billing/Account</option>
                                        <option value="Feature">Feature Request</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Message</label>
                                <textarea id="message" rows="5" placeholder="Describe your problem..." class="w-full rounded-xl px-4 py-3 resize-none" required></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" id="send-btn" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-12 rounded-xl transition shadow-lg flex items-center justify-center gap-2 uppercase text-sm tracking-widest">
                                    Send Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="glass-card overflow-hidden">
                <div class="p-6 border-b border-slate-700 bg-slate-800/30 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-white uppercase italic">Your Tickets</h2>
                    <span class="material-symbols-outlined text-indigo-400">history</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-900/50 text-[10px] uppercase font-bold text-gray-500 tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-6 py-4">Created</th>
                            </tr>
                        </thead>
                        <tbody id="ticket-list-body" class="divide-y divide-slate-800">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
        import { getFirestore, collection, addDoc, query, where, orderBy, onSnapshot, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

        // TODO: Replace with your actual Firebase config
        const firebaseConfig = {
            apiKey: "YOUR_API_KEY",
            authDomain: "YOUR_PROJECT.firebaseapp.com",
            projectId: "YOUR_PROJECT_ID",
            storageBucket: "YOUR_PROJECT.appspot.com",
            messagingSenderId: "YOUR_ID",
            appId: "YOUR_APP_ID"
        };

        const app = initializeApp(firebaseConfig);
        const db = getFirestore(app);
        const userId = "<?php echo $userId; ?>";

        // 1. Submit Ticket
        const supportForm = document.getElementById('support-form');
        const sendBtn = document.getElementById('send-btn');

        supportForm.onsubmit = async (e) => {
            e.preventDefault();
            sendBtn.disabled = true;
            sendBtn.innerHTML = `Sending...`;

            try {
                await addDoc(collection(db, "tickets"), {
                    userId: userId,
                    userEmail: "<?php echo $userEmail; ?>",
                    subject: document.getElementById('subject').value,
                    category: document.getElementById('category').value,
                    message: document.getElementById('message').value,
                    status: "pending",
                    createdAt: serverTimestamp()
                });

                showToast("Ticket submitted!");
                supportForm.reset();
            } catch (error) {
                console.error("Firebase Error:", error);
                alert("Failed to send: " + error.message);
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerText = "Send Ticket";
            }
        };

        // 2. Real-time Ticket History
        const ticketBody = document.getElementById('ticket-list-body');
        const q = query(
            collection(db, "tickets"), 
            where("userId", "==", userId),
            orderBy("createdAt", "desc")
        );

        onSnapshot(q, (snapshot) => {
            if (snapshot.empty) {
                ticketBody.innerHTML = `<tr><td colspan="3" class="px-6 py-8 text-center text-gray-500 italic">No tickets found.</td></tr>`;
                return;
            }

            ticketBody.innerHTML = snapshot.docs.map(doc => {
                const data = doc.data();
                const date = data.createdAt?.toDate().toLocaleDateString() || 'Just now';
                return `
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-6 py-4">
                            <span class="status-pill status-${data.status}">${data.status}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300 font-medium">${data.subject}</td>
                        <td class="px-6 py-4 text-xs text-gray-500">${date}</td>
                    </tr>
                `;
            }).join('');
        });

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-message').innerText = msg;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 4000);
        }
    </script>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> main
