<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title><?php echo APP_NAME; ?> - Sistem Inventori Modern</title>
    
    <!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
    
    <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Boxicons - Latest Version -->
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="assets/css/mobile.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .hover-scale {
            transition: transform 0.2s ease-in-out;
        }
        .hover-scale:hover {
            transform: scale(1.05);
        }
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php
    // Pastikan variable $page tersedia
    $page = $_GET['page'] ?? 'dashboard';
    $allowedPages = ['dashboard', 'stok', 'pembelian', 'penjualan', 'jurnal', 'laporan', 'profil'];
    if (!in_array($page, $allowedPages)) {
        $page = 'dashboard';
    }
    ?>
    
    <!-- Mobile Sidebar -->
    <div id="mobileSidebar" class="fixed inset-0 z-50 lg:hidden hidden">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="toggleMobileSidebar()"></div>
        <div class="fixed left-0 top-0 h-full w-72 bg-white shadow-xl transform transition-transform duration-300 ease-in-out mobile-sidebar">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <i class='bx bx-package text-white text-xl'></i>
                        </div>
                        <h1 class="text-xl font-bold text-gray-800"><?php echo APP_NAME; ?></h1>
                    </div>
                    <button onclick="toggleMobileSidebar()" class="touch-button text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100">
                        <i class='bx bx-x text-2xl'></i>
                    </button>
                </div>
                
                <!-- Navigation -->
                <nav class="space-y-2">
                    <a href="?page=dashboard" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-home text-xl'></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="?page=stok" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-package text-xl'></i>
                        <span class="font-medium">Stok</span>
                    </a>
                    <a href="?page=customer" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-user text-xl'></i>
                        <span class="font-medium">Customer</span>
                    </a>
                    <a href="?page=supplier" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-store text-xl'></i>
                        <span class="font-medium">Supplier</span>
                    </a>
                    <a href="?page=pembelian" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-cart text-xl'></i>
                        <span class="font-medium">Pembelian</span>
                    </a>
                    <a href="?page=penjualan" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-cart text-xl'></i>
                        <span class="font-medium">Penjualan</span>
                    </a>
                    <a href="?page=hutang" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-credit-card text-xl'></i>
                        <span class="font-medium">Hutang</span>
                    </a>
                    <a href="?page=piutang" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-money text-xl'></i>
                        <span class="font-medium">Piutang</span>
                    </a>
                    <a href="?page=jurnal" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-book text-xl'></i>
                        <span class="font-medium">Jurnal</span>
                    </a>
                    <a href="?page=laporan" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-chart text-xl'></i>
                        <span class="font-medium">Laporan</span>
                    </a>
                    <a href="?page=profil" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-building text-xl'></i>
                        <span class="font-medium">Profil</span>
                    </a>
                    <a href="?page=coa" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors touch-button">
                        <i class='bx bx-list-ul text-xl'></i>
                        <span class="font-medium">COA</span>
                    </a>
                </nav>
                
                <!-- Footer -->
                <div class="absolute bottom-6 left-6 right-6">
                    <div class="text-center text-sm text-gray-500">
                        <p>Version <?php echo APP_VERSION; ?></p>
                        <p class="mt-1">© 2024 <?php echo APP_NAME; ?></p>
                    </div>
                </div>
            </div>
      </div>
    </div>

    <!-- Main Layout -->
    <div class="flex">
        <!-- Desktop Sidebar -->
        <div class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 lg:z-50">
            <div class="flex-1 flex flex-col min-h-0 bg-white border-r border-gray-200">
                <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 px-6 mb-8">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                            <i class='bx bx-package text-white text-xl'></i>
                        </div>
                        <h1 class="text-xl font-bold text-gray-800"><?php echo APP_NAME; ?></h1>
                    </div>
                    
                    <nav class="space-y-1">
                        <a href="?page=dashboard" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'dashboard' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-home text-xl mr-3'></i>
                            Dashboard
                        </a>
                        <a href="?page=stok" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'stok' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-package text-xl mr-3'></i>
                            Stok
                        </a>
                        <a href="?page=customer" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'customer' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-user text-xl mr-3'></i>
                            Customer
                        </a>
                        <a href="?page=supplier" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'supplier' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-truck text-xl mr-3'></i>
                            Supplier
                        </a>
                        <a href="?page=pembelian" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'pembelian' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-cart-add text-xl mr-3'></i>
                            Pembelian
                        </a>
                        <a href="?page=penjualan" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'penjualan' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-cart text-xl mr-3'></i>
                            Penjualan
                        </a>
                        <a href="?page=hutang" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'hutang' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-credit-card text-xl mr-3'></i>
                            Hutang
                        </a>
                        <a href="?page=piutang" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'piutang' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-money text-xl mr-3'></i>
                            Piutang
                        </a>
                        <a href="?page=jurnal" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'jurnal' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-book text-xl mr-3'></i>
                            Jurnal
                        </a>
                        <a href="?page=laporan" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'laporan' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-chart text-xl mr-3'></i>
                            Laporan
                        </a>
                        <a href="?page=coa" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'coa' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-book-bookmark text-xl mr-3'></i>
                            COA
                        </a>
                        <a href="?page=profil" class="group flex items-center px-3 py-3 text-sm font-medium rounded-lg transition-all duration-200 <?php echo $page == 'profil' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600'; ?>">
                            <i class='bx bx-building text-xl mr-3'></i>
                            Profil
                        </a>
</nav>
                </div>
                
                <!-- Footer Sidebar -->
                <div class="flex-shrink-0 border-t border-gray-200 p-4">
                    <div class="text-center text-sm text-gray-500">
                        <p>Version <?php echo APP_VERSION; ?></p>
                        <p class="mt-1">© 2024 <?php echo APP_NAME; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:pl-64 flex flex-col flex-1">
            <!-- Top Navigation -->
            <div class="sticky top-0 z-40 flex-shrink-0 flex h-16 bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 w-full">
                    <!-- Mobile menu button -->
                    <button onclick="toggleMobileSidebar()" class="lg:hidden touch-button p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                        <i class='bx bx-menu text-2xl'></i>
                    </button>
                    
                    <!-- Page title -->
                    <div class="flex-1 flex items-center justify-center lg:justify-start">
                        <h2 class="text-lg font-semibold text-gray-800 capitalize mobile-text-lg">
                            <?php 
                            $pageTitles = [
                                'dashboard' => 'Dashboard',
                                'stok' => 'Manajemen Stok',
                                'pembelian' => 'Pembelian',
                                'penjualan' => 'Penjualan',
                                'jurnal' => 'Jurnal',
                                'laporan' => 'Laporan',
                                'profil' => 'Profil Perusahaan'
                            ];
                            echo $pageTitles[$page] ?? 'Dashboard';
                            ?>
                        </h2>
                    </div>
                    
                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="touch-button p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class='bx bx-bell text-xl'></i>
                        </button>
                        
                        <!-- Profile dropdown -->
                        <div class="relative">
                            <button class="flex items-center space-x-2 touch-button p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center">
                                    <i class='bx bx-user text-white text-sm'></i>
                                </div>
                                <span class="hidden sm:block text-sm font-medium text-gray-700">Admin</span>
                                <i class='bx bx-chevron-down text-sm'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
