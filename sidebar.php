<?php

$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;


$current_page = basename($_SERVER['SCRIPT_NAME']); 


if ($isAdmin) {
    $nav_items = [
        ['file' => 'admin_dashboard.php', 'label' => 'Stats', 'icon' => 'icon-dashboard'],
        ['file' => 'admin_vehicles.php', 'label' => 'Fleet', 'icon' => 'icon-garage'],
        ['file' => 'admin_support.php', 'label' => 'Tickets', 'icon' => 'icon-support'],
    ];
} else {
    $nav_items = [
        ['file' => 'dashboard.php',   'label' => 'Dashboard',   'icon' => 'icon-dashboard'],
        ['file' => 'MyGarage.php',    'label' => 'My Garage',   'icon' => 'icon-garage'],
        ['file' => 'maintenance.php', 'label' => 'Maintenance',     'icon' => 'icon-history'],
        ['file' => 'diagnostics.php', 'label' => 'Diagnostics', 'icon' => 'icon-diagnostics'],
        ['file' => 'fuellog.php',     'label' => 'Fuel Log',        'icon' => 'icon-fuel'],
        ['file' => 'support.php',     'label' => 'Support',     'icon' => 'icon-support'],
        ['file' => 'settings.php',    'label' => 'Settings',    'icon' => 'icon-settings'],
    ];
}
?>

<style>
  
    @media (max-width: 1023px) {
        main {
            padding-top: 4rem !important; 
        }
    }
</style>


<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="icon-dashboard" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></symbol>
    <symbol id="icon-garage" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></symbol>
    <symbol id="icon-history" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></symbol>
    <symbol id="icon-diagnostics" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></symbol>
    <symbol id="icon-fuel" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22V8a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14"></path><path d="M15 6V4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v12"></path></symbol>
    <symbol id="icon-support" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line></symbol>
    <symbol id="icon-settings" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1-2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></symbol>
    <symbol id="icon-logout" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></symbol>
    <symbol id="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></symbol>
    <symbol id="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></symbol>
</svg>


<header class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6 z-[90]">
    <div class="flex items-center">
        <img src="Images/logo_New.png" alt="Logo" class="w-8 h-8 object-contain">
        <span class="text-lg font-black text-white ml-2 tracking-tighter italic uppercase leading-none">AutoMind <span class="text-blue-500">AI</span></span>
    </div>
    <button onclick="toggleSidebar()" class="p-2 text-gray-400 hover:text-white transition-colors">
        <svg class="w-6 h-6"><use href="#icon-menu"></use></svg>
    </button>
</header>


<div id="sidebar-backdrop" 
     onclick="toggleSidebar()"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden lg:hidden"></div>


<aside id="sidebar-menu" 
       class="fixed inset-y-0 left-0 w-64 bg-gray-900 flex flex-col shadow-2xl border-r border-gray-800 transition-transform duration-300 ease-in-out z-[110] -translate-x-full lg:translate-x-0">
 
    <div class="flex items-center justify-between px-6 h-24 border-b border-gray-800 shrink-0">
        <div class="flex items-center">
            <img src="Images/logo_New.png" alt="Logo" class="w-10 h-10 object-contain">
            <span class="text-xl font-black text-white ml-3 tracking-tighter italic uppercase leading-none">AutoMind <span class="text-blue-500">AI</span></span>
        </div>
        <!-- Mobile Close Button -->
        <button onclick="toggleSidebar()" class="lg:hidden p-1 text-gray-500 hover:text-white">
            <svg class="w-6 h-6"><use href="#icon-close"></use></svg>
        </button>
    </div>

    <!-- Nav Items -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto mt-4">
        <?php foreach ($nav_items as $item): 
            $is_active = ($current_page == $item['file']);
            $active_classes = 'bg-blue-600 text-white shadow-lg shadow-blue-900/40 border border-blue-400/30';
            $inactive_classes = 'text-gray-500 hover:bg-gray-800 hover:text-gray-200';
        ?>
            <a href="<?php echo $item['file']; ?>" 
               target="_self"
               class="relative flex items-center space-x-3 px-4 py-4 rounded-xl transition-all duration-200 <?php echo $is_active ? $active_classes : $inactive_classes; ?>">
                
                <svg class="w-5 h-5 flex-shrink-0 <?php echo $is_active ? 'text-white' : 'text-gray-500'; ?>">
                    <use href="#<?php echo $item['icon']; ?>"></use>
                </svg>
                
                <span class="font-bold text-sm tracking-wide"><?php echo $item['label']; ?></span>
                
                <?php if ($is_active): ?>
                    <div class="absolute left-0 w-1.5 h-6 bg-white rounded-r-full"></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Footer / Logout -->
    <div class="p-4 border-t border-gray-800 shrink-0">
        <a href="logout.php" 
           target="_self"
           onclick="return confirm('Log out of Command Center?');"
           class="flex items-center space-x-3 px-4 py-4 rounded-xl text-gray-500 hover:bg-red-500/10 hover:text-red-500 transition-all duration-200 group">
            <svg class="w-5 h-5 transition-colors">
                <use href="#icon-logout"></use>
            </svg>
            <span class="font-bold text-sm tracking-wide">Log Out</span>
        </a>
    </div>
</aside>

<script>
    /**
     * Handles the visibility of the mobile navigation sidebar
     */
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar-menu');
        const backdrop = document.getElementById('sidebar-backdrop');
        
        const isHidden = sidebar.classList.contains('-translate-x-full');
        
        if (isHidden) {
            // OPEN SIDEBAR
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            backdrop.classList.remove('hidden');
            // Prevent body scroll when menu is open
            document.body.style.overflow = 'hidden';
        } else {
            // CLOSE SIDEBAR
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            backdrop.classList.add('hidden');
            // Restore body scroll
            document.body.style.overflow = 'auto';
        }
    }

    // Close sidebar on window resize if moving to desktop width
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            const sidebar = document.getElementById('sidebar-menu');
            const backdrop = document.getElementById('sidebar-backdrop');
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            backdrop.classList.add('hidden');
            document.body.style.overflow = 'auto';
        } else {
            // If resizing to mobile, reset to hidden
            const sidebar = document.getElementById('sidebar-menu');
            sidebar.classList.add('-translate-x-full');
        }
    });
</script>