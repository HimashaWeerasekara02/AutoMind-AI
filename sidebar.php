<?php
// sidebar.php
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
$current_page = basename($_SERVER['PHP_SELF']); 

/**
 * Define Navigation based on Role
 * We use your exact structure but separate the arrays.
 */
if ($isAdmin) {
    $nav_items = [
        ['file' => 'admin_support.php', 'label' => 'User Management', 'icon' => 'icon-support'],
        ['file' => 'system_reports.php', 'label' => 'Global Analytics', 'icon' => 'icon-dashboard'],
        ['file' => 'settings.php', 'label' => 'System Config', 'icon' => 'icon-settings'],
    ];
} else {
    $nav_items = [
        ['file' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'icon-dashboard'],
        ['file' => 'MyGarage.php', 'label' => 'My Garage', 'icon' => 'icon-garage'],
        ['file' => 'maintenance.php', 'label' => 'Maintenance', 'icon' => 'icon-history'],
        ['file' => 'diagnostics.php', 'label' => 'Diagnostics', 'icon' => 'icon-diagnostics'],
        ['file' => 'fuellog.php', 'label' => 'Fuel Log', 'icon' => 'icon-fuel'],
        ['file' => 'settings.php', 'label' => 'Settings', 'icon' => 'icon-settings'],
        ['file' => 'support.php', 'label' => 'Support', 'icon' => 'icon-support']
    ];
}
?>

<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="icon-dashboard" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></symbol>
    <symbol id="icon-garage" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></symbol>
    <symbol id="icon-history" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></symbol>
    <symbol id="icon-diagnostics" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></symbol>
    <symbol id="icon-fuel" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22V8a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14"></path><path d="M15 6V4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v12"></path></symbol>
    <symbol id="icon-settings" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1-2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></symbol>
    <symbol id="icon-support" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line></symbol>
    <symbol id="icon-logout" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></symbol>
</svg>

<div class="lg:hidden bg-gray-900 border-b border-gray-800 p-4 flex justify-between items-center sticky top-0 z-[50]">
    <div class="flex items-center">
        <img src="Images/logo.png" alt="Logo" class="w-8 h-8 mr-2">
        <span class="text-white font-bold italic uppercase tracking-tighter">AutoMind <span class="text-blue-500">AI</span></span>
    </div>
    <button id="mobile-toggle" class="text-gray-400 p-3 hover:bg-gray-800 rounded-lg focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
        </svg>
    </button>
</div>

<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[80] hidden lg:hidden transition-opacity"></div>

<aside id="sidebar-menu" class="fixed inset-y-0 left-0 w-75 bg-gray-900 flex flex-col shadow-2xl border-r border-gray-800 z-[90] transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out overflow-hidden">
    
    <div class="flex items-center px-6 h-24 border-b border-gray-800 shrink-0">
        <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-900/20">
            <img src="Images/logo.png" alt="Logo" class="w-8 h-8 object-contain">
        </div>
        <span class="text-xl font-black text-white ml-3 tracking-tighter italic uppercase">AutoMind <span class="text-blue-500">AI</span></span>
    </div>

    <nav class="flex-1 p-4 space-y-2 overflow-y-auto mt-4 relative">
        <?php 
            foreach ($nav_items as $item):
                $is_active = ($current_page == $item['file']);
        ?>
            <a href="<?php echo $item['file']; ?>" 
               class="relative z-[100] flex items-center space-x-3 px-4 py-4 rounded-xl transition-all duration-200 <?php echo $is_active ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-500 hover:bg-gray-800 hover:text-gray-200'; ?>">
                <svg class="w-5 h-5 flex-shrink-0">
                    <use href="#<?php echo $item['icon']; ?>"></use>
                </svg>
                <span class="font-bold text-sm tracking-wide"><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="p-4 border-t border-gray-800 shrink-0">
        <a href="logout.php" 
           id="logout-button"
           onclick="return confirm('Log out of Command Center?');"
           class="flex items-center space-x-3 px-4 py-4 rounded-xl text-gray-500 hover:bg-red-500/10 hover:text-red-500 transition-all duration-200 group">
            <svg class="w-5 h-5 text-gray-500 group-hover:text-red-500 transition-colors">
                <use href="#icon-logout"></use>
            </svg>
            <span class="font-bold text-sm tracking-wide">Log Out</span>
        </a>
    </div>
</aside>

<script>
    const sidebar = document.getElementById('sidebar-menu');
    const overlay = document.getElementById('sidebar-overlay');
    const toggle = document.getElementById('mobile-toggle');

    const toggleSidebar = (e) => {
        if(e) e.stopPropagation();
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        
        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = ''; 
        } else {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; 
        }
    };

    if (toggle) toggle.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);

    sidebar.addEventListener('click', (e) => {
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            // Keep link default behavior
        } else {
            e.stopPropagation();
        }
    });
</script>