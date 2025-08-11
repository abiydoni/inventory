<!-- --- FILE: views/stok/index.php --- -->
<?php
// CSRF Protection
$csrf_token = Helper::generateCSRF();

// Handle delete action
if (isset($_GET['hapus']) && Helper::validateCSRF($_GET['token'] ?? '')) {
    try {
    $id = (int)$_GET['hapus'];
        $db->execute('DELETE FROM stok WHERE id = ?', [$id]);
        echo "<script>Swal.fire('Sukses', 'Barang berhasil dihapus', 'success').then(() => {location.href='?page=stok'});</script>";
    } catch (Exception $e) {
        echo "<script>Swal.fire('Error', 'Gagal menghapus barang: " . addslashes($e->getMessage()) . "', 'error');</script>";
    }
}

// Search functionality
$search = $_GET['search'] ?? '';
$whereClause = '';
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE nama LIKE ? OR kode LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Pagination
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalRows = $db->fetch("SELECT COUNT(*) as total FROM stok $whereClause", $params)['total'];
$totalPages = ceil($totalRows / $perPage);

$rows = $db->fetchAll("SELECT * FROM stok $whereClause ORDER BY id DESC LIMIT ? OFFSET ?", 
    array_merge($params, [$perPage, $offset]));
?>

<!-- Header Section -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Stok</h1>
            <p class="mt-2 text-gray-600">Kelola produk dan inventori perusahaan Anda</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openTambah()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:ring-4 focus:ring-blue-300 transition-all duration-200">
                <i class='bx bx-plus mr-2'></i>
                Tambah Produk
            </button>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <div class="relative">
                <i class='bx bx-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400'></i>
                <input type="text" id="searchInput" placeholder="Cari produk berdasarkan nama atau kode..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="performSearch()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class='bx bx-search mr-2'></i>Cari
            </button>
            <?php if (!empty($search)): ?>
                <a href="?page=stok" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    <i class='bx bx-x mr-2'></i>Reset
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                <i class='bx bx-package text-2xl text-blue-600'></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Produk</p>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($totalRows); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                <i class='bx bx-box text-2xl text-green-600'></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Total Stok</p>
                <?php $totalStok = $db->fetch("SELECT SUM(stok) as total FROM stok")['total'] ?? 0; ?>
                <p class="text-2xl font-bold text-gray-900"><?php echo number_format($totalStok); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                <i class='bx bx-dollar text-2xl text-purple-600'></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Nilai Persediaan</p>
                <?php $nilaiPersediaan = $db->fetch("SELECT SUM(stok*harga) as total FROM stok")['total'] ?? 0; ?>
                <p class="text-2xl font-bold text-gray-900"><?php echo Helper::formatMoney($nilaiPersediaan); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Produk</h3>
</div>

    <?php if (empty($rows)): ?>
        <div class="text-center py-12">
            <i class='bx bx-package text-6xl text-gray-300 mb-4'></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada produk</h3>
            <p class="text-gray-500 mb-4">
                <?php echo !empty($search) ? 'Tidak ada produk yang sesuai dengan pencarian Anda.' : 'Mulai dengan menambahkan produk pertama Anda.'; ?>
            </p>
            <?php if (empty($search)): ?>
                <button onclick="openTambah()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class='bx bx-plus mr-2'></i>Tambah Produk Pertama
                </button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center mr-3">
                                        <i class='bx bx-package text-white text-lg'></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($r['nama']); ?></div>
                                        <div class="text-sm text-gray-500">ID: <?php echo $r['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($r['kode']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-gray-900"><?php echo number_format($r['stok']); ?></span>
                                    <?php if ($r['stok'] < 10): ?>
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class='bx bx-error mr-1'></i>Rendah
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo Helper::formatMoney($r['harga']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($r['stok'] > 20): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class='bx bx-check mr-1'></i>Tersedia
                                    </span>
                                <?php elseif ($r['stok'] > 0): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class='bx bx-warning mr-1'></i>Terbatas
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class='bx bx-x mr-1'></i>Habis
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="edit(<?php echo $r['id']; ?>)" 
                                            class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors">
                                        <i class='bx bx-edit mr-1'></i>Edit
                                    </button>
                                    <button onclick="hapus(<?php echo $r['id']; ?>)" 
                                            class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors">
                                        <i class='bx bx-trash mr-1'></i>Hapus
                                    </button>
                                </div>
            </td>
                        </tr>
                    <?php endforeach; ?>
    </tbody>
  </table>
</div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $perPage, $totalRows); ?> dari <?php echo $totalRows; ?> produk
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=stok&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i class='bx bx-chevron-left mr-1'></i>Sebelumnya
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=stok&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium <?php echo $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=stok&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" 
                               class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Selanjutnya<i class='bx bx-chevron-right ml-1'></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal Scripts -->
<script>
function performSearch() {
    const search = document.getElementById('searchInput').value;
    if (search.trim()) {
        location.href = `?page=stok&search=${encodeURIComponent(search)}`;
    }
}

function openTambah() {
  Swal.fire({
        title: 'Tambah Produk Baru',
    html: `
            <form id="tambahForm" class="text-left space-y-4">
                <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Produk *</label>
                    <input type="text" id="kode" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
                    <input type="text" id="nama" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stok *</label>
                        <input type="number" id="stok" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label>
                        <input type="number" id="harga" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        width: '500px',
    preConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'tambah');
            formData.append('csrf_token', document.getElementById('csrf_token').value);
            formData.append('kode', document.getElementById('kode').value);
            formData.append('nama', document.getElementById('nama').value);
            formData.append('stok', document.getElementById('stok').value);
            formData.append('harga', document.getElementById('harga').value);
            
            return fetch('?page=stok&aksi=api', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).catch(() => {
                Swal.showValidationMessage('Request error');
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value.status === 'success') {
                Swal.fire('Sukses!', result.value.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error!', result.value.message, 'error');
            }
    }
  });
}

function edit(id) {
    fetch('?page=stok&aksi=get&id=' + id + '&token=<?php echo $csrf_token; ?>')
        .then(r => r.json())
        .then(d => {
            if (!d) return Swal.fire('Error', 'Data tidak ditemukan', 'error');
            
    Swal.fire({
                title: 'Edit Produk',
                html: `
                    <form id="editForm" class="text-left space-y-4">
                        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" id="id" value="${id}">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Produk *</label>
                            <input type="text" id="kode" value="${d.kode}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
                            <input type="text" id="nama" value="${d.nama}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stok *</label>
                                <input type="number" id="stok" value="${d.stok}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga *</label>
                                <input type="number" id="harga" value="${d.harga}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Batal',
                width: '500px',
                preConfirm: () => {
                    const formData = new FormData();
                    formData.append('action', 'edit');
                    formData.append('csrf_token', document.getElementById('csrf_token').value);
                    formData.append('id', document.getElementById('id').value);
                    formData.append('kode', document.getElementById('kode').value);
                    formData.append('nama', document.getElementById('nama').value);
                    formData.append('stok', document.getElementById('stok').value);
                    formData.append('harga', document.getElementById('harga').value);
                    
                    return fetch('?page=stok&aksi=api', {
                        method: 'POST',
                        body: formData
                    }).then(r => r.json());
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.status === 'success') {
                        Swal.fire('Sukses!', result.value.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', result.value.message, 'error');
                    }
                }
            });
        });
}

function hapus(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus produk ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            const token = '<?php echo $csrf_token; ?>';
            location.href = `?page=stok&hapus=${id}&token=${token}`;
        }
    });
}

// Search on Enter key
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performSearch();
    }
});
</script>

<!-- API Handler -->
<?php
if (isset($_GET['aksi']) && $_GET['aksi'] == 'api') {
    try {
        if (!Helper::validateCSRF($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'tambah') {
            // Validasi input
            $kode = $_POST['kode'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $stok = $_POST['stok'] ?? '';
            $harga = $_POST['harga'] ?? '';
            
            if (!Helper::validateRequired($kode)) throw new Exception('Kode produk wajib diisi');
            if (!Helper::validateRequired($nama)) throw new Exception('Nama produk wajib diisi');
            if (!Helper::validateNumber($stok)) throw new Exception('Stok harus berupa angka positif');
            if (!Helper::validateNumber($harga)) throw new Exception('Harga harus berupa angka positif');
            
            // Cek kode unik
            $existing = $db->fetch("SELECT id FROM stok WHERE kode = ?", [Helper::sanitize($kode)]);
            if ($existing) throw new Exception('Kode produk sudah digunakan');
            
            // Insert
            $db->execute("INSERT INTO stok (kode, nama, stok, harga) VALUES (?, ?, ?, ?)", [
                Helper::sanitize($kode),
                Helper::sanitize($nama),
                (int)$stok,
                (int)$harga
            ]);
            
            Helper::jsonResponse(Helper::successResponse('Produk berhasil ditambahkan'));
            
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $kode = $_POST['kode'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $stok = $_POST['stok'] ?? '';
            $harga = $_POST['harga'] ?? '';
            
            if (!Helper::validateRequired($kode)) throw new Exception('Kode produk wajib diisi');
            if (!Helper::validateRequired($nama)) throw new Exception('Nama produk wajib diisi');
            if (!Helper::validateNumber($stok)) throw new Exception('Stok harus berupa angka positif');
            if (!Helper::validateNumber($harga)) throw new Exception('Harga harus berupa angka positif');
            
            // Cek kode unik (kecuali untuk produk yang sedang diedit)
            $existing = $db->fetch("SELECT id FROM stok WHERE kode = ? AND id != ?", [Helper::sanitize($kode), $id]);
            if ($existing) throw new Exception('Kode produk sudah digunakan');
            
            // Update
            $db->execute("UPDATE stok SET kode = ?, nama = ?, stok = ?, harga = ? WHERE id = ?", [
                Helper::sanitize($kode),
                Helper::sanitize($nama),
                (int)$stok,
                (int)$harga,
                $id
            ]);
            
            Helper::jsonResponse(Helper::successResponse('Produk berhasil diperbarui'));
            
        } else {
            throw new Exception('Action tidak valid');
        }
        
    } catch (Exception $e) {
        Helper::jsonResponse(Helper::errorResponse($e->getMessage()));
    }
}

if (isset($_GET['aksi']) && $_GET['aksi'] == 'get' && isset($_GET['id'])) {
    try {
        if (!Helper::validateCSRF($_GET['token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        $id = (int)$_GET['id'];
        $row = $db->fetch("SELECT * FROM stok WHERE id = ?", [$id]);
        
        if (!$row) {
            Helper::jsonResponse(Helper::errorResponse('Data tidak ditemukan'));
        }
        
        Helper::jsonResponse($row);
        
    } catch (Exception $e) {
        Helper::jsonResponse(Helper::errorResponse($e->getMessage()));
    }
}
?>