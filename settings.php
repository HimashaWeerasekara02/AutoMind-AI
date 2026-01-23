<?php
// AutoMind AI - Configuration & Session Mock
$appName = "AutoMind AI";
$currentYear = date("Y");
$isLoggedIn = false; // Mock login state

// You would typically handle form submissions here
// if ($_SERVER['REQUEST_METHOD'] === 'POST') { ... }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $appName; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Base styles */
        body {
            font-family: 'Inter', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Page switching logic */
        .page {
            display: none;
        }
        .page.active {
            display: block;
        }
        
        /* Sidebar active link style */
        .sidebar-link.active {
            background-color: #111827; /* bg-gray-900 */
            color: white;
        }
        
        /* Simple bar chart mock */
        .chart-bar {
            background-color: #3b82f6; /* bg-blue-500 */
            border-radius: 4px 4px 0 0;
            transition: height 0.3s ease;
        }
        
        /* Hide scrollbar but allow scrolling */
        #main-content {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        #main-content::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        /* Toast Notification Style */
        #toast {
            visibility: hidden;
            opacity: 0;
            transform: translateY(-100%);
            transition: all 0.5s ease-in-out;
        }
        #toast.show {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }

        /* Interactive row hover */
        .interactive-row:hover {
            background-color: #374151; /* bg-gray-700 */
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-300">


            
            <!-- Page 2f: Settings -->
            <div id="settings-page" class="page">
                <h1 class="text-3xl font-bold text-white mb-6">Settings</h1>
                <div class="max-w-xl mx-auto bg-gray-800 rounded-lg shadow-lg p-8">
                    <form class="space-y-6">
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-4">Profile</h3>
                            <div class="space-y-4">
                                <div class="flex items-center space-x-4">
                                    <img src="https://placehold.co/64x64/5b21b6/ffffff?text=JD" alt="User Avatar" class="w-16 h-16 rounded-full">
                                    <button type="button" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">Change Picture</button>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Full Name</label>
                                    <input type="text" value="Jane Doe" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">Email Address</label>
                                    <input type="email" value="jane.doe@email.com" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

           

    

    
    <script>
       

    </script>

</body>
</html>