<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="icon-dashboard" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></symbol>
    <symbol id="icon-garage" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></symbol>
    <symbol id="icon-history" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></symbol>
    <symbol id="icon-diagnostics" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></symbol>
    <symbol id="icon-fuel" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22V8a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14"></path><path d="M15 6V4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v12"></path></symbol>
    <symbol id="icon-settings" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1-2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></symbol>
    <symbol id="icon-support" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line></symbol>
    <symbol id="icon-logout" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></symbol>
</svg>

<aside class="w-64 bg-gray-800 flex flex-col fixed inset-y-0 shadow-2xl border-r border-gray-700">
    <div class="flex items-center px-6 h-20 border-b border-gray-700">
        <img src="http://localhost/AutoMind-AI/Images/logo.png" alt="AutoMind AI Logo" class="w-10 h-10 object-contain">
        <span class="text-xl font-bold text-white ml-3">AutoMind AI</span>
    </div>

    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        
        <a href="http://localhost/AutoMind-AI/dashboard.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?php echo ($current_page == 'dashboard.php') ? 'active bg-gray-900 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6"><use href="#icon-dashboard"></use></svg>
            <span class="font-medium">Dashboard</span>
        </a>

        <a href="http://localhost/AutoMind-AI/MyGarage.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?php echo ($current_page == 'MyGarage.php') ? 'active bg-gray-900 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6"><use href="#icon-garage"></use></svg>
            <span class="font-medium">My Garage</span>
        </a>

        <a href="http://localhost/AutoMind-AI/maintenance.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?php echo ($current_page == 'maintenance.php') ? 'active bg-gray-900 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6"><use href="#icon-history"></use></svg>
            <span class="font-medium">Maintenance History</span>
        </a>

        <a href="http://localhost/AutoMind-AI/diagnostics.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?php echo ($current_page == 'diagnostics.php') ? 'active bg-gray-900 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6"><use href="#icon-diagnostics"></use></svg>
            <span class="font-medium">Diagnostics</span>
        </a>

        <a href="http://localhost/AutoMind-AI/fuellog.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?php echo ($current_page == 'fuellog.php') ? 'active bg-gray-900 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6"><use href="#icon-fuel"></use></svg>
            <span class="font-medium">Fuel Log</span>
        </a>

        <a href="http://localhost/AutoMind-AI/settings.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?php echo ($current_page == 'settings.php') ? 'active bg-gray-900 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6"><use href="#icon-settings"></use></svg>
            <span class="font-medium">Settings</span>
        </a>

        <a href="http://localhost/AutoMind-AI/support.php" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg transition duration-200 <?php echo ($current_page == 'support.php') ? 'active bg-gray-900 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
            <svg class="w-6 h-6"><use href="#icon-support"></use></svg>
            <span class="font-medium">Support</span>
        </a>
    </nav>

    <div class="p-4 border-t border-gray-700">
        <a href="http://localhost/AutoMind-AI/logout.php" id="logout-button" class="sidebar-link flex items-center space-x-3 px-4 py-3 rounded-lg text-gray-400 hover:bg-red-600 hover:text-white transition duration-200">
            <svg class="w-6 h-6"><use href="#icon-logout"></use></svg>
            <span class="font-medium">Log Out</span>
        </a>
    </div>
</aside>

<script>
    document.getElementById('logout-button').onclick = (e) => {
        if(!confirm("Are you sure you want to log out?")) {
            e.preventDefault();
        }
    };
</script>