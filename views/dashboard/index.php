<?php
// Ambil data statistik
$totalStok = $db->fetch("SELECT SUM(stok) as total FROM stok")['total'] ?? 0;
$nilaiPersediaan = $db->fetch("SELECT SUM(stok*harga) as total FROM stok")['total'] ?? 0;
$totalProduk = $db->fetch("SELECT COUNT(*) as total FROM stok")['total'] ?? 0;

$bulanIni = date('Y-m');
$penjualanBulan = $db->fetch("SELECT SUM(total) as total FROM penjualan WHERE substr(tanggal,1,7)=?", [$bulanIni])['total'] ?? 0;
$pembelianBulan = $db->fetch("SELECT SUM(total) as total FROM pembelian WHERE substr(tanggal,1,7)=?", [$bulanIni])['total'] ?? 0;

// Data untuk chart
$bulanLabels = [];
$penjualanData = [];
$pembelianData = [];

for ($i = 5; $i >= 0; $i--) {
    $bulan = date('Y-m', strtotime("-$i months"));
    $bulanLabels[] = date('M Y', strtotime($bulan));
    
    $penjualan = $db->fetch("SELECT SUM(total) as total FROM penjualan WHERE substr(tanggal,1,7)=?", [$bulan])['total'] ?? 0;
    $pembelian = $db->fetch("SELECT SUM(total) as total FROM pembelian WHERE substr(tanggal,1,7)=?", [$bulan])['total'] ?? 0;
    
    $penjualanData[] = $penjualan;
    $pembelianData[] = $pembelian;
}

// Produk dengan stok terendah
$stokRendah = $db->fetchAll("SELECT nama, stok FROM stok WHERE stok < 10 ORDER BY stok ASC LIMIT 5");

// Transaksi terbaru
$transaksiTerbaru = $db->fetchAll("
    SELECT 'penjualan' as tipe, tanggal, total, 'Penjualan' as keterangan 
    FROM penjualan 
    UNION ALL 
    SELECT 'pembelian' as tipe, tanggal, total, 'Pembelian' as keterangan 
    FROM pembelian 
    ORDER BY tanggal DESC LIMIT 10
");
?>

<!-- Welcome Section -->
<div class="mb-6 md:mb-8">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl md:rounded-2xl p-6 md:p-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h1 class="text-2xl md:text-4xl font-bold mb-2">Selamat Datang!</h1>
                <p class="text-blue-100 text-base md:text-lg">Kelola inventori perusahaan Anda dengan mudah dan efisien</p>
            </div>
            <div class="hidden md:block">
                <i class='bx bx-package text-6xl md:text-8xl text-white/20'></i>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
    <!-- Total Produk -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 hover-scale">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Total Produk</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo number_format($totalProduk); ?></p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class='bx bx-package text-xl md:text-2xl text-blue-600'></i>
            </div>
        </div>
        <div class="mt-3 md:mt-4">
            <span class="text-xs md:text-sm text-green-600 font-medium">
                <i class='bx bx-trending-up mr-1'></i>+12%
            </span>
            <span class="text-xs md:text-sm text-gray-500 ml-2">dari bulan lalu</span>
        </div>
    </div>

    <!-- Total Stok -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 hover-scale">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Total Stok</p>
                <p class="text-2xl md:text-3xl font-bold text-gray-900"><?php echo number_format($totalStok); ?></p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class='bx bx-box text-xl md:text-2xl text-green-600'></i>
            </div>
        </div>
        <div class="mt-3 md:mt-4">
            <span class="text-xs md:text-sm text-green-600 font-medium">
                <i class='bx bx-trending-up mr-1'></i>+8%
            </span>
            <span class="text-xs md:text-sm text-gray-500 ml-2">dari bulan lalu</span>
        </div>
    </div>

    <!-- Nilai Persediaan -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 hover-scale">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Nilai Persediaan</p>
                <p class="text-lg md:text-2xl font-bold text-gray-900"><?php echo Helper::formatMoney($nilaiPersediaan); ?></p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class='bx bx-dollar text-xl md:text-2xl text-purple-600'></i>
            </div>
        </div>
        <div class="mt-3 md:mt-4">
            <span class="text-xs md:text-sm text-green-600 font-medium">
                <i class='bx bx-trending-up mr-1'></i>+15%
            </span>
            <span class="text-xs md:text-sm text-gray-500 ml-2">dari bulan lalu</span>
        </div>
    </div>

    <!-- Penjualan Bulan Ini -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 hover-scale">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs md:text-sm font-medium text-gray-600">Penjualan Bulan Ini</p>
                <p class="text-lg md:text-2xl font-bold text-gray-900"><?php echo Helper::formatMoney($penjualanBulan); ?></p>
            </div>
            <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class='bx bx-shopping-bag text-xl md:text-2xl text-orange-600'></i>
            </div>
        </div>
        <div class="mt-3 md:mt-4">
            <span class="text-xs md:text-sm text-green-600 font-medium">
                <i class='bx bx-trending-up mr-1'></i>+23%
            </span>
            <span class="text-xs md:text-sm text-gray-500 ml-2">dari bulan lalu</span>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8 mb-6 md:mb-8">
    <!-- Sales Chart -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Tren Penjualan & Pembelian</h3>
        <div class="h-64 md:h-80">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Stock Distribution Chart -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Distribusi Stok</h3>
        <div class="h-64 md:h-80">
            <canvas id="stockChart"></canvas>
        </div>
    </div>
</div>

<!-- Quick Actions & Alerts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8 mb-6 md:mb-8">
    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-2 gap-3 md:gap-4">
            <a href="?page=stok" class="flex flex-col items-center p-3 md:p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group touch-button">
                <i class='bx bx-plus-circle text-2xl md:text-3xl text-blue-600 mb-2 group-hover:scale-110 transition-transform'></i>
                <span class="text-xs md:text-sm font-medium text-blue-800 text-center">Tambah Produk</span>
            </a>
            <a href="?page=penjualan" class="flex flex-col items-center p-3 md:p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group touch-button">
                <i class='bx bx-shopping-bag text-2xl md:text-3xl text-green-600 mb-2 group-hover:scale-110 transition-transform'></i>
                <span class="text-xs md:text-sm font-medium text-green-800 text-center">Buat Penjualan</span>
            </a>
            <a href="?page=pembelian" class="flex flex-col items-center p-3 md:p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors group touch-button">
                <i class='bx bx-cart text-2xl md:text-3xl text-purple-600 mb-2 group-hover:scale-110 transition-transform'></i>
                <span class="text-xs md:text-sm font-medium text-purple-800 text-center">Buat Pembelian</span>
            </a>
            <a href="?page=laporan" class="flex flex-col items-center p-3 md:p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors group touch-button">
                <i class='bx bx-chart text-2xl md:text-3xl text-orange-600 mb-2 group-hover:scale-110 transition-transform'></i>
                <span class="text-xs md:text-sm font-medium text-orange-800 text-center">Lihat Laporan</span>
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Peringatan Stok</h3>
        <?php if (empty($stokRendah)): ?>
            <div class="text-center py-6 md:py-8 text-gray-500">
                <i class='bx bx-check-circle text-3xl md:text-4xl mb-2 text-green-500'></i>
                <p class="text-sm md:text-base">Semua produk memiliki stok yang cukup</p>
            </div>
        <?php else: ?>
            <div class="space-y-2 md:space-y-3">
                <?php foreach ($stokRendah as $produk): ?>
                    <div class="flex items-center justify-between p-2 md:p-3 bg-red-50 rounded-lg border border-red-200">
                        <div class="flex items-center space-x-2 md:space-x-3">
                            <i class='bx bx-error text-red-500 text-lg md:text-xl'></i>
                            <span class="font-medium text-gray-800 text-sm md:text-base"><?php echo htmlspecialchars($produk['nama']); ?></span>
                        </div>
                        <span class="text-xs md:text-sm text-red-600 font-semibold">Stok: <?php echo $produk['stok']; ?></span>
                    </div>
                <?php endforeach; ?>
  </div>
            <div class="mt-3 md:mt-4">
                <a href="?page=stok" class="text-xs md:text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Lihat semua produk <i class='bx bx-arrow-right'></i>
                </a>
  </div>
        <?php endif; ?>
  </div>
</div>

<!-- Recent Transactions -->
<div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
    <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Transaksi Terbaru</h3>
    <?php if (empty($transaksiTerbaru)): ?>
        <div class="text-center py-6 md:py-8 text-gray-500">
            <i class='bx bx-receipt text-3xl md:text-4xl mb-2'></i>
            <p class="text-sm md:text-base">Belum ada transaksi</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 md:py-3 px-2 md:px-4 font-medium text-gray-600 text-xs md:text-sm">Tanggal</th>
                        <th class="text-left py-2 md:py-3 px-2 md:px-4 font-medium text-gray-600 text-xs md:text-sm">Tipe</th>
                        <th class="text-left py-2 md:py-3 px-2 md:px-4 font-medium text-gray-600 text-xs md:text-sm">Total</th>
                        <th class="text-left py-2 md:py-3 px-2 md:px-4 font-medium text-gray-600 text-xs md:text-sm">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksiTerbaru as $transaksi): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-2 md:py-3 px-2 md:px-4 text-xs md:text-sm text-gray-600">
                                <?php echo Helper::formatDate($transaksi['tanggal']); ?>
                            </td>
                            <td class="py-2 md:py-3 px-2 md:px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    <?php echo $transaksi['tipe'] == 'penjualan' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <i class='bx <?php echo $transaksi['tipe'] == 'penjualan' ? 'bx-shopping-bag' : 'bx-cart'; ?> mr-1'></i>
                                    <?php echo $transaksi['keterangan']; ?>
                                </span>
                            </td>
                            <td class="py-2 md:py-3 px-2 md:px-4 text-xs md:text-sm font-medium text-gray-900">
                                <?php echo Helper::formatMoney($transaksi['total']); ?>
                            </td>
                            <td class="py-2 md:py-3 px-2 md:px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class='bx bx-check mr-1'></i>Selesai
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Charts JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($bulanLabels); ?>,
            datasets: [{
                label: 'Penjualan',
                data: <?php echo json_encode($penjualanData); ?>,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Pembelian',
                data: <?php echo json_encode($pembelianData); ?>,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: window.innerWidth < 768 ? 8 : 10
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: window.innerWidth < 768 ? 8 : 10
                        },
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });

    // Stock Distribution Chart
    const stockCtx = document.getElementById('stockChart').getContext('2d');
    new Chart(stockCtx, {
        type: 'doughnut',
        data: {
            labels: ['Stok Tersedia', 'Stok Rendah', 'Stok Kosong'],
            datasets: [{
                data: [
                    <?php echo $totalStok; ?>,
                    <?php echo count($stokRendah); ?>,
                    0
                ],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: window.innerWidth < 768 ? 10 : 12
                        }
                    }
                }
            }
        }
    });
});
</script>
