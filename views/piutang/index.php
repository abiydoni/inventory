<?php
// Halaman piutang: daftar piutang usaha dari penjualan kredit
$csrf_token = Helper::generateCSRF();
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE p.jenis_pembayaran = 'kredit'";
$params = [];

if ($search !== '') {
    $where .= " AND (c.nama LIKE ? OR p.id LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}

if ($status !== '') {
    $where .= " AND p.status_pembayaran = ?";
    $params[] = $status;
}

$piutang = $db->fetchAll("
    SELECT p.*, c.nama as customer_nama, c.alamat as customer_alamat
    FROM penjualan p
    LEFT JOIN customer c ON p.customer_id = c.id
    $where
    ORDER BY p.tanggal DESC
", $params);

// Hitung total piutang
$total_piutang = $db->fetch("
    SELECT SUM(total) as total FROM penjualan 
    WHERE jenis_pembayaran = 'kredit' AND status_pembayaran = 'belum_lunas'
")['total'] ?? 0;

// Hitung piutang lunas
$piutang_lunas = $db->fetch("
    SELECT SUM(total) as total FROM penjualan 
    WHERE jenis_pembayaran = 'kredit' AND status_pembayaran = 'lunas'
")['total'] ?? 0;
?>

<div class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-900">Piutang Usaha</h1>
        <p class="text-sm text-gray-600 mt-1">Kelola piutang dari penjualan kredit</p>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class='bx bx-credit-card text-2xl'></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Piutang</p>
                <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($total_piutang); ?></p>
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
                <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($total_piutang); ?></p>
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
                <p class="text-2xl font-bold text-gray-900">Rp <?php echo number_format($piutang_lunas); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filter dan Search -->
<div class="bg-white p-6 mt-6 rounded-lg shadow-sm">
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input id="searchInput" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Cari customer atau ID penjualan" 
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
        <a href="?page=piutang" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
            Reset
        </a>
        <?php endif; ?>
    </div>

    <!-- Tabel Piutang -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($piutang)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            Tidak ada piutang ditemukan
                        </td>
                    </tr>
                <?php else: foreach ($piutang as $p): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            #<?php echo $p['id']; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <?php echo date('d/m/Y H:i', strtotime($p['tanggal'])); ?>
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($p['customer_nama']); ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($p['customer_alamat']); ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            Rp <?php echo number_format($p['total']); ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($p['status_pembayaran'] === 'lunas'): ?>
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
                                <button onclick="lihatDetail(<?php echo $p['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-900">
                                    <i class='bx bx-show text-lg'></i>
                                </button>
                                <?php if ($p['status_pembayaran'] === 'belum_lunas'): ?>
                                <button onclick="terimaPembayaran(<?php echo $p['id']; ?>)" 
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
    let url = '?page=piutang';
    
    if (search) url += '&search=' + encodeURIComponent(search);
    if (status) url += '&status=' + encodeURIComponent(status);
    
    location.href = url;
}

function lihatDetail(id) {
    // Buka modal detail penjualan
    Swal.fire({
        title: 'Detail Penjualan #' + id,
        html: 'Loading...',
        width: '800px',
        showConfirmButton: false,
        didOpen: () => {
            // Fetch detail penjualan
            fetch(`views/penjualan/api.php?action=get&id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.getHtmlContainer().innerHTML = `
                            <div class="text-left">
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="font-medium">ID Penjualan:</label>
                                        <p>#${data.data.id}</p>
                                    </div>
                                    <div>
                                        <label class="font-medium">Tanggal:</label>
                                        <p>${data.data.tanggal}</p>
                                    </div>
                                    <div>
                                        <label class="font-medium">Customer:</label>
                                        <p>${data.data.customer_nama}</p>
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

function terimaPembayaran(id) {
    Swal.fire({
        title: 'Terima Pembayaran',
        html: `
            <div class="text-left space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nominal</label>
                <input type="number" id="nominal" min="1" class="w-full px-3 py-2 border rounded-lg" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Metode</label>
                <select id="metode" class="w-full px-3 py-2 border rounded-lg">
                  <option value="kas">Kas</option>
                  <option value="bank">Bank</option>
                </select>
              </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Terima',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const nominal = parseInt(document.getElementById('nominal').value || '0', 10);
            const metode = document.getElementById('metode').value;
            if (!nominal || nominal <= 0) {
              Swal.showValidationMessage('Nominal tidak valid');
              return false;
            }
            const formData = new FormData();
            formData.append('action', 'terima_pembayaran');
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            formData.append('id', id);
            formData.append('nominal', nominal);
            formData.append('metode', metode);
            return fetch('views/penjualan/api.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .catch(() => { Swal.showValidationMessage('Request error'); });
        }
    }).then((res) => {
        if (res.isConfirmed) {
            if (res.value && res.value.status === 'success') {
                Swal.fire('Sukses', res.value.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', (res.value && res.value.message) || 'Gagal menerima pembayaran', 'error');
            }
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
