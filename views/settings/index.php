<?php
// CSRF
$csrf_token = Helper::generateCSRF();

// Ambil semua COA untuk dropdown
$allAkun = $db->fetchAll('SELECT id, kode, nama FROM coa WHERE aktif = 1 ORDER BY kode');

// Ambil settings saat ini
$currentSettings = $db->fetchAll('SELECT key, value FROM settings');
$settingsMap = [];
foreach ($currentSettings as $s) { $settingsMap[$s['key']] = $s['value']; }

function selectedOption($current, $value) {
    return ($current !== null && (string)$current === (string)$value) ? 'selected' : '';
}
?>

<!-- Header Section -->
<div class="mb-8">
  <div class="flex items-center mb-4">
    <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
      <i class='bx bx-cog text-white text-2xl'></i>
    </div>
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Pengaturan COA</h1>
      <p class="text-gray-600 mt-1">Konfigurasi akun untuk jurnal otomatis sistem</p>
    </div>
  </div>
  
  <!-- Info Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-center">
        <i class='bx bx-info-circle text-blue-600 text-xl mr-3'></i>
        <div>
          <h3 class="font-semibold text-blue-900 text-sm">Pengaturan Fleksibel</h3>
          <p class="text-blue-700 text-xs">Dapat diubah kapan saja tanpa mempengaruhi data historis</p>
        </div>
      </div>
    </div>
    
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
      <div class="flex items-center">
        <i class='bx bx-check-circle text-green-600 text-xl mr-3'></i>
        <div>
          <h3 class="font-semibold text-green-900 text-sm">Jurnal Otomatis</h3>
          <p class="text-green-700 text-xs">Sistem akan mencatat jurnal sesuai pengaturan ini</p>
        </div>
      </div>
    </div>
    
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
      <div class="flex items-center">
        <i class='bx bx-lightbulb text-purple-600 text-xl mr-3'></i>
        <div>
          <h3 class="font-semibold text-purple-900 text-sm">Template Kustom</h3>
          <p class="text-purple-700 text-xs">Override debit/kredit sesuai kebutuhan bisnis</p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="bg-white rounded-xl shadow-lg border border-gray-200">
  <form id="settingsForm" class="space-y-6">
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />

    <!-- Akun Utama Section -->
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center mb-4">
        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
          <i class='bx bx-wallet text-blue-600'></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-800">Akun Utama</h2>
      </div>
      <p class="text-gray-600 mb-4">Akun-akun dasar yang digunakan dalam transaksi</p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php
          $fields = [
            'akun_kas' => 'Akun Kas',
            'akun_bank' => 'Akun Bank',
            'akun_persediaan' => 'Akun Persediaan',
            'akun_pendapatan_penjualan' => 'Pendapatan Penjualan',
            'akun_hpp' => 'Harga Pokok Penjualan (HPP)',
            'akun_beban_administrasi' => 'Beban Administrasi & Umum',
            'akun_piutang' => 'Akun Piutang Usaha',
            'akun_hutang' => 'Akun Hutang Usaha',
          ];
          foreach ($fields as $key => $label):
        ?>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $label; ?></label>
          <select name="<?php echo $key; ?>" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">- Pilih Akun -</option>
            <?php foreach ($allAkun as $akun): ?>
              <option value="<?php echo $akun['id']; ?>" <?php echo selectedOption($settingsMap[$key] ?? null, $akun['id']); ?>>
                <?php echo htmlspecialchars($akun['kode'] . ' - ' . $akun['nama']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Mapping Transaksi Section -->
    <div class="p-6 border-b border-gray-100">
      <div class="flex items-center mb-4">
        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
          <i class='bx bx-transfer text-green-600'></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-800">Mapping Transaksi</h2>
      </div>
      <p class="text-gray-600 mb-4">Akun yang digunakan saat pencatatan transaksi pembelian/penjualan</p>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php
          $transFields = [
            'penjualan_persediaan' => 'Penjualan: Akun Persediaan',
            'penjualan_piutang' => 'Penjualan: Akun Piutang',
            'pembelian_persediaan' => 'Pembelian: Akun Persediaan',
            'pembelian_hutang' => 'Pembelian: Akun Hutang',
          ];
          foreach ($transFields as $key => $label):
        ?>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $label; ?></label>
          <select name="<?php echo $key; ?>" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">- Pilih Akun -</option>
            <?php foreach ($allAkun as $akun): ?>
              <option value="<?php echo $akun['id']; ?>" <?php echo selectedOption($settingsMap[$key] ?? null, $akun['id']); ?>>
                <?php echo htmlspecialchars($akun['kode'] . ' - ' . $akun['nama']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Template Jurnal Section -->
    <div class="p-6">
      <div class="flex items-center mb-4">
        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
          <i class='bx bx-book-open text-purple-600'></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-800">Template Jurnal (Opsional)</h2>
      </div>
      <p class="text-gray-600 mb-4">Override debit/kredit untuk jurnal pembayaran. Kosongkan untuk menggunakan aturan standar.</p>
      
      <!-- Pembayaran Hutang -->
      <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
          <i class='bx bx-credit-card text-red-500 mr-2'></i>
          Pembayaran Hutang
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Akun Debet</label>
            <select name="pembayaran_hutang_debet" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">- Gunakan Default (Hutang Usaha) -</option>
              <?php foreach ($allAkun as $akun): ?>
                <option value="<?php echo $akun['id']; ?>" <?php echo selectedOption($settingsMap['pembayaran_hutang_debet'] ?? null, $akun['id']); ?>>
                  <?php echo htmlspecialchars($akun['kode'] . ' - ' . $akun['nama']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Akun Kredit</label>
            <select name="pembayaran_hutang_kredit" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">- Gunakan Default (Kas/Bank) -</option>
              <?php foreach ($allAkun as $akun): ?>
                <option value="<?php echo $akun['id']; ?>" <?php echo selectedOption($settingsMap['pembayaran_hutang_kredit'] ?? null, $akun['id']); ?>>
                  <?php echo htmlspecialchars($akun['kode'] . ' - ' . $akun['nama']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      
      <!-- Penerimaan Piutang -->
      <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-800 mb-3 flex items-center">
          <i class='bx bx-money text-green-500 mr-2'></i>
          Penerimaan Piutang
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Akun Debet</label>
            <select name="pembayaran_piutang_debet" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">- Gunakan Default (Kas/Bank) -</option>
              <?php foreach ($allAkun as $akun): ?>
                <option value="<?php echo $akun['id']; ?>" <?php echo selectedOption($settingsMap['pembayaran_piutang_debet'] ?? null, $akun['id']); ?>>
                  <?php echo htmlspecialchars($akun['kode'] . ' - ' . $akun['nama']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Akun Kredit</label>
            <select name="pembayaran_piutang_kredit" class="w-full border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">- Gunakan Default (Piutang Usaha) -</option>
              <?php foreach ($allAkun as $akun): ?>
                <option value="<?php echo $akun['id']; ?>" <?php echo selectedOption($settingsMap['pembayaran_piutang_kredit'] ?? null, $akun['id']); ?>>
                  <?php echo htmlspecialchars($akun['kode'] . ' - ' . $akun['nama']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      
      <!-- Info Box -->
      <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <div class="flex items-start">
          <i class='bx bx-info-circle text-gray-500 mt-0.5 mr-3'></i>
          <div>
            <h4 class="font-medium text-gray-800 mb-1">Cara Kerja Template Jurnal</h4>
            <ul class="text-sm text-gray-600 space-y-1">
              <li>• <strong>Kosong:</strong> Sistem menggunakan aturan standar (Hutang→Kas, Kas→Piutang)</li>
              <li>• <strong>Diisi:</strong> Sistem menggunakan akun yang Anda pilih</li>
              <li>• <strong>Fleksibel:</strong> Dapat diubah kapan saja tanpa mempengaruhi data historis</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">
          <i class='bx bx-info-circle mr-1'></i>
          Pengaturan akan diterapkan untuk transaksi selanjutnya
        </div>
        <div class="flex items-center gap-3">
          <a href="?page=dashboard" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
            <i class='bx bx-x mr-1'></i>Batal
          </a>
          <button type="submit" class="px-6 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg">
            <i class='bx bx-save mr-2'></i>Simpan Pengaturan
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  // Show loading state
  const submitBtn = e.target.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin mr-2"></i>Menyimpan...';
  submitBtn.disabled = true;
  
  const form = e.target;
  const data = new FormData(form);
  
  fetch('views/settings/api.php', { method: 'POST', body: data })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil Disimpan!',
          text: res.message,
          confirmButtonColor: '#3b82f6'
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal Menyimpan',
          text: res.message || 'Terjadi kesalahan saat menyimpan pengaturan',
          confirmButtonColor: '#ef4444'
        });
      }
    })
    .catch(() => {
      Swal.fire({
        icon: 'error',
        title: 'Kesalahan Sistem',
        text: 'Tidak dapat terhubung ke server. Silakan coba lagi.',
        confirmButtonColor: '#ef4444'
      });
    })
    .finally(() => {
      // Restore button state
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    });
});
</script>


