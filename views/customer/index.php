<?php
// CSRF Protection
$csrf_token = Helper::generateCSRF();

// Handle delete action
if (isset($_GET['hapus']) && Helper::validateCSRF($_GET['token'] ?? '')) {
    try {
        $id = (int)$_GET['hapus'];
        $db->execute('DELETE FROM customer WHERE id = ?', [$id]);
        echo "<script>Swal.fire('Sukses', 'Customer berhasil dihapus', 'success').then(() => {location.href='?page=customer'});</script>";
    } catch (Exception $e) {
        echo "<script>Swal.fire('Error', 'Gagal menghapus customer: " . addslashes($e->getMessage()) . "', 'error');</script>";
    }
}

// Search functionality
$search = $_GET['search'] ?? '';
$whereClause = '';
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE nama LIKE ? OR kode LIKE ? OR telepon LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

// Pagination
$page = max(1, (int)($_GET['p'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalRows = $db->fetch("SELECT COUNT(*) as total FROM customer $whereClause", $params)['total'];
$totalPages = ceil($totalRows / $perPage);

$rows = $db->fetchAll("SELECT * FROM customer $whereClause ORDER BY id DESC LIMIT ? OFFSET ?", 
    array_merge($params, [$perPage, $offset]));
?>

<!-- Header Section -->
<div class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Customer</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola data customer untuk transaksi penjualan</p>
            </div>
            <button onclick="openTambah()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class='bx bx-plus text-xl'></i>
                <span>Tambah Customer</span>
            </button>
        </div>
    </div>
</div>

<!-- Search and Stats -->
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
        <div class="flex-1 max-w-lg">
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Cari customer..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class='bx bx-search text-gray-400'></i>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600"><?php echo $totalRows; ?></div>
                <div class="text-sm text-gray-500">Total Customer</div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <button onclick="performSearch()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
            <i class='bx bx-search mr-2'></i>Cari
        </button>
        <?php if (!empty($search)): ?>
            <a href="?page=customer" class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                <i class='bx bx-x mr-2'></i>Reset
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Customer List -->
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Customer</h3>
    </div>

    <?php if (empty($rows)): ?>
        <div class="text-center py-12">
            <i class='bx bx-user text-6xl text-gray-300 mb-4'></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada customer</h3>
            <p class="text-gray-500 mb-4">Mulai dengan menambahkan customer pertama Anda</p>
            <button onclick="openTambah()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class='bx bx-plus mr-2'></i>Tambah Customer
            </button>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($rows as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($row['kode']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if ($row['telepon']): ?>
                                        <div class="flex items-center space-x-2">
                                            <i class='bx bx-phone text-gray-400'></i>
                                            <span><?php echo htmlspecialchars($row['telepon']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($row['email']): ?>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <i class='bx bx-envelope text-gray-400'></i>
                                            <span><?php echo htmlspecialchars($row['email']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate">
                                    <?php echo $row['alamat'] ? htmlspecialchars($row['alamat']) : '-'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button onclick="edit(<?php echo $row['id']; ?>)" 
                                            class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg transition-colors">
                                        <i class='bx bx-edit mr-1'></i>Edit
                                    </button>
                                    <button onclick="hapus(<?php echo $row['id']; ?>)" 
                                            class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg transition-colors">
                                        <i class='bx bx-trash mr-1'></i>Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="bg-white px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Menampilkan <?php echo ($offset + 1); ?> - <?php echo min($offset + $perPage, $totalRows); ?> dari <?php echo $totalRows; ?> customer
            </div>
            <div class="flex space-x-2">
                <?php if ($page > 1): ?>
                    <a href="?page=customer&p=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        <i class='bx bx-chevron-left mr-1'></i>Sebelumnya
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?page=customer&p=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm <?php echo $i === $page ? 'bg-blue-50 text-blue-600 border-blue-600' : 'text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=customer&p=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                        Selanjutnya<i class='bx bx-chevron-right ml-1'></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Add/Edit Modal Scripts -->
<script>
function performSearch() {
    const search = document.getElementById('searchInput').value;
    location.href = `?page=customer&search=${encodeURIComponent(search)}`;
}

function openTambah() {
    // Generate kode otomatis 8 digit
    function generateKode() {
        const timestamp = Date.now().toString();
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return 'CUST' + (timestamp + random).slice(-4);
    }
    
    Swal.fire({
        title: 'Tambah Customer Baru',
        html: `
            <form id="tambahForm" class="text-left space-y-4">
                <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Customer *</label>
                    <input type="text" id="kode" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly required>
                    <p class="text-xs text-gray-500 mt-1">Kode akan otomatis 8 digit</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Customer *</label>
                    <input type="text" id="nama" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="tel" id="telepon" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea id="alamat" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        width: '500px',
        didOpen: () => {
            document.getElementById('kode').value = generateKode();
        },
        preConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'tambah');
            formData.append('csrf_token', document.getElementById('csrf_token').value);
            formData.append('kode', document.getElementById('kode').value);
            formData.append('nama', document.getElementById('nama').value);
            formData.append('telepon', document.getElementById('telepon').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('alamat', document.getElementById('alamat').value);
            return fetch('api/customer.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .catch(() => { Swal.showValidationMessage('Request error'); });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value.status === 'success') {
                Swal.fire('Sukses!', result.value.message, 'success').then(() => { location.reload(); });
            } else {
                Swal.fire('Error!', result.value.message, 'error');
            }
        }
    });
}

function edit(id) {
    fetch(`api/customer.php?action=get&id=${id}&token=<?php echo $csrf_token; ?>`)
        .then(r => r.json())
        .then(d => {
            if (!d || d.status === 'error') {
                return Swal.fire('Error', d && d.message ? d.message : 'Data tidak ditemukan', 'error');
            }
            Swal.fire({
                title: 'Edit Customer',
                html: `
                    <form id="editForm" class="text-left space-y-4">
                        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" id="id" value="${id}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Customer *</label>
                            <input type="text" id="kode" value="${d.kode}" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Customer *</label>
                            <input type="text" id="nama" value="${d.nama}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                                <input type="tel" id="telepon" value="${d.telepon || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" value="${d.email || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea id="alamat" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg">${d.alamat || ''}</textarea>
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
                    formData.append('telepon', document.getElementById('telepon').value);
                    formData.append('email', document.getElementById('email').value);
                    formData.append('alamat', document.getElementById('alamat').value);
                    return fetch('api/customer.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .catch(() => { Swal.showValidationMessage('Request error'); });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.status === 'success') {
                        Swal.fire('Sukses!', result.value.message, 'success').then(() => { location.reload(); });
                    } else {
                        Swal.fire('Error!', result.value.message, 'error');
                    }
                }
            });
        })
        .catch(() => Swal.fire('Error', 'Gagal memuat data customer', 'error'));
}

function hapus(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus customer ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            const token = '<?php echo $csrf_token; ?>';
            location.href = `?page=customer&hapus=${id}&token=${token}`;
        }
    });
}
</script>
