<?php
// Halaman hutang: daftar hutang usaha dari pembelian kredit
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE p.jenis_pembayaran = 'kredit'";
$params = [];

if ($search !== '') {
    $where .= " AND (s.nama LIKE ? OR p.id LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}

if ($status !== '') {
    $where .= " AND p.status_pembayaran = ?";
    $params[] = $status;
}

$hutang = $db->fetchAll("
    SELECT p.*, s.nama as supplier_nama, s.alamat as supplier_alamat
    FROM pembelian p
    LEFT JOIN supplier s ON p.supplier_id = s.id
    $where
    ORDER BY p.tanggal DESC
", $params);

// Hitung total hutang
$total_hutang = $db->fetch("
    SELECT SUM(total) as total FROM pembelian 
    WHERE jenis_pembayaran = 'kredit' AND status_pembayaran = 'belum_lunas'
")['total'] ?? 0;

// Hitung hutang lunas
$hutang_lunas = $db->fetch("
    SELECT SUM(total) as total FROM pembelian 
    WHERE jenis_pembayaran = 'kredit' AND status_pembayaran = 'lunas'
")['total'] ?? 0;
?>

<div class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-900">Hutang Usaha</h1>
        <p class="text-sm text-gray-600 mt-1">Kelola hutang dari pembelian kredit</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class='bx bx-credit-card text-2xl'></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Hutang</p>
                <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($total_hutang); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class='bx bx-time text-2xl'></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Belum Lunas</p>
                <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($total_hutang); ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class='bx bx-check-circle text-2xl'></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Sudah Lunas</p>
                <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($hutang_lunas); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filter dan Search -->
<div class="bg-white p-6 mt-6 rounded-lg shadow-sm">
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input id="searchInput" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Cari supplier atau ID pembelian" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="w-full md:w-48">
            <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">Semua Status</option>
                <option value="belum_lunas" <?php echo $status === 'belum_lunas' ? 'selected' : ''; ?>>Belum Lunas</option>
                <option value="lunas" <?php echo $status === 'lunas' ? 'selected' : ''; ?>>Lunas</option>
            </select>
        </div>
        <button onclick="performSearch()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class='bx bx-search mr-2'></i>Cari
        </button>
        <?php if ($search !== '' || $status !== ''): ?>
        <a href="?page=hutang" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            Reset
        </a>
        <?php endif; ?>
    </div>

    <!-- Tabel Hutang -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($hutang)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            Tidak ada hutang ditemukan
                        </td>
                    </tr>
                <?php else: foreach ($hutang as $h): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            #<?php echo $h['id']; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <?php echo date('d/m/Y H:i', strtotime($h['tanggal'])); ?>
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($h['supplier_nama']); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($h['supplier_alamat']); ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            Rp <?php echo number_format($h['total']); ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($h['status_pembayaran'] === 'lunas'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Lunas
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Belum Lunas
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button onclick="lihatDetail(<?php echo $h['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900">
                                    <i class='bx bx-show text-lg'></i>
                                </button>
                                <?php if ($h['status_pembayaran'] === 'belum_lunas'): ?>
                                <button onclick="bayarHutang(<?php echo $h['id']; ?>)" 
                                        class="text-green-600 hover:text-green-900">
                                    <i class='bx bx-check text-lg'></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function performSearch() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    let url = '?page=hutang';
    
    if (search) url += '&search=' + encodeURIComponent(search);
    if (status) url += '&status=' + encodeURIComponent(status);
    
    location.href = url;
}

function lihatDetail(id) {
    // Buka modal detail pembelian
    Swal.fire({
        title: 'Detail Pembelian #' + id,
        html: 'Loading...',
        width: '800px',
        showConfirmButton: false,
        didOpen: () => {
            // Fetch detail pembelian
            fetch(`api/pembelian.php?action=get&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.getHtmlContainer().innerHTML = `
                            <div class="text-left">
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="font-medium">ID Pembelian:</label>
                                        <p>#${data.data.id}</p>
                                    </div>
                                    <div>
                                        <label class="font-medium">Tanggal:</label>
                                        <p>${data.data.tanggal}</p>
                                    </div>
                                    <div>
                                        <label class="font-medium">Supplier:</label>
                                        <p>${data.data.supplier_nama}</p>
                                    </div>
                                    <div>
                                        <label class="font-medium">Total:</label>
                                        <p>Rp ${Number(data.data.total).toLocaleString()}</p>
                                    </div>
                                </div>
                                <div class="border-t pt-4">
                                    <h4 class="font-medium mb-2">Detail Item:</h4>
                                    <div class="bg-gray-50 p-3 rounded">
                                        ${data.data.items.map(item => 
                                            `<div class="flex justify-between py-1">
                                                <span>${item.nama}</span>
                                                <span>${item.qty} x Rp ${Number(item.harga).toLocaleString()}</span>
                                            </div>`
                                        ).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        Swal.getHtmlContainer().innerHTML = 'Gagal memuat data';
                    }
                })
                .catch(() => {
                    Swal.getHtmlContainer().innerHTML = 'Error memuat data';
                });
        }
    });
}

function bayarHutang(id) {
    Swal.fire({
        title: 'Bayar Hutang?',
        text: 'Apakah Anda yakin ingin menandai hutang ini sebagai lunas?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lunas',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'bayar_hutang');
            formData.append('id', id);
            
            fetch('api/pembelian.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Sukses', 'Hutang berhasil dilunasi', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Gagal melunasi hutang', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Terjadi kesalahan', 'error');
            });
        }
    });
}

// Enter key untuk search
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        performSearch();
    }
});
</script>
