<!-- --- FILE: views/pembelian/index.php --- -->
<?php
// CSRF Protection
$csrf_token = Helper::generateCSRF();

// Halaman pembelian: buat transaksi pembelian dgn detail
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    
    // Test endpoint
    if ($aksi == 'test') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'message' => 'Server working']);
        exit;
    }
    
    if ($aksi=='simpan') {
        // Validate CSRF
        if (!Helper::validateCSRF($_POST['csrf_token'] ?? '')) {
            echo json_encode(['status'=>'error', 'message'=>'Invalid CSRF token']);
            exit;
        }
        
        $tanggal = $_POST['tanggal'] ?: date('Y-m-d H:i:s');
        $supplier_id = (int)($_POST['supplier_id'] ?? 0);
        $jenis_pembayaran = $_POST['jenis_pembayaran'] ?? 'cash';
        $diskon = (float)$_POST['diskon']; // percent
        $pajak = (float)$_POST['pajak'];
        $items = json_decode($_POST['items'], true);
        
        if (!$supplier_id) {
            echo json_encode(['status'=>'error', 'message'=>'Supplier harus dipilih']);
            exit;
        }
        
        if (empty($items)) {
            echo json_encode(['status'=>'error', 'message'=>'Item harus ditambahkan']);
            exit;
        }
        
        // hitung subtotal
        $subtotal = 0;
        foreach ($items as $it) $subtotal += intval($it['qty'])*intval($it['harga']);
        $afterDisk = $subtotal - ($subtotal * $diskon/100);
        $afterTax = $afterDisk + ($afterDisk * $pajak/100);
        $total = round($afterTax);
        
        // Set status pembayaran otomatis
        if ($jenis_pembayaran == 'cash' || $jenis_pembayaran == 'bank') {
            $status_pembayaran = 'lunas';
        } else {
            $status_pembayaran = 'belum_lunas';
        }
        
        try {
            $db->getConnection()->beginTransaction();
            
            // simpan pembelian
            $s = $db->getConnection()->prepare('INSERT INTO pembelian (tanggal,supplier_id,jenis_pembayaran,status_pembayaran,subtotal,diskon,pajak,total) VALUES(?,?,?,?,?,?,?,?)');
            $s->execute([$tanggal, $supplier_id, $jenis_pembayaran, $status_pembayaran, $subtotal, $diskon, $pajak, $total]);
            $pid = $db->lastInsertId();
            
            $ins = $db->getConnection()->prepare('INSERT INTO pembelian_detail (pembelian_id,stok_id,qty,harga) VALUES(?,?,?,?)');
            foreach ($items as $it) {
                $ins->execute([$pid, $it['id'], $it['qty'], $it['harga']]);
                // update stok
                $db->getConnection()->prepare('UPDATE stok SET stok = stok + ? WHERE id=?')->execute([$it['qty'], $it['id']]);
            }
            
            // Get COA settings (now using kode directly)
            $akunPersediaan = $db->fetch('SELECT value FROM settings WHERE key = ?', ['pembelian_persediaan'])['value'] ?? 
                             $db->fetch('SELECT value FROM settings WHERE key = ?', ['akun_persediaan'])['value'];
            $akunHutang = $db->fetch('SELECT value FROM settings WHERE key = ?', ['pembelian_hutang'])['value'] ?? 
                         $db->fetch('SELECT value FROM settings WHERE key = ?', ['akun_hutang'])['value'];
            $akunKas = $db->fetch('SELECT value FROM settings WHERE key = ?', ['akun_kas'])['value'];
            $akunBank = $db->fetch('SELECT value FROM settings WHERE key = ?', ['akun_bank'])['value'];
            
            // Get COA names and codes - handles both ID and kode
            $getCoaInfo = function($value) use ($db) {
                if (!$value) return ['nama' => null, 'kode' => null];
                
                // Try to find by kode first
                $row = $db->fetch('SELECT nama, kode FROM coa WHERE kode = ?', [$value]);
                if ($row) return ['nama' => $row['nama'], 'kode' => $row['kode']];
                
                // If not found, try by ID
                $row = $db->fetch('SELECT nama, kode FROM coa WHERE id = ?', [$value]);
                return $row ? ['nama' => $row['nama'], 'kode' => $row['kode']] : ['nama' => null, 'kode' => null];
            };
            
            // jurnal berdasarkan jenis pembayaran
            
            if ($jenis_pembayaran == 'cash') {
                // Debit Persediaan, Kredit Kas
                $debetInfo = $getCoaInfo($akunPersediaan);
                $kreditInfo = $getCoaInfo($akunKas);
                $debetAkun = $debetInfo['nama'] ?: 'Persediaan Barang Dagang';
                $kreditAkun = $kreditInfo['nama'] ?: 'Kas';
                $debetKode = $debetInfo['kode'] ?: 'PERSEDIAAN';
                $kreditKode = $kreditInfo['kode'] ?: 'KAS';
                
                $db->execute('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan,coa_kode) VALUES(?,?,?,0,?,?)', 
                           [$tanggal, $debetAkun, $total, 'Pembelian #'.$pid, $debetKode]);
                
                $db->execute('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan,coa_kode) VALUES(?,?,0,?,?,?)', 
                           [$tanggal, $kreditAkun, $total, 'Pembelian #'.$pid, $kreditKode]);
                
            } elseif ($jenis_pembayaran == 'bank') {
                // Debit Persediaan, Kredit Bank
                $debetInfo = $getCoaInfo($akunPersediaan);
                $kreditInfo = $getCoaInfo($akunBank);
                $debetAkun = $debetInfo['nama'] ?: 'Persediaan Barang Dagang';
                $kreditAkun = $kreditInfo['nama'] ?: 'Bank';
                $debetKode = $debetInfo['kode'] ?: 'PERSEDIAAN';
                $kreditKode = $kreditInfo['kode'] ?: 'BANK';
                
                $db->execute('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan,coa_kode) VALUES(?,?,?,0,?,?)', 
                           [$tanggal, $debetAkun, $total, 'Pembelian #'.$pid, $debetKode]);
                
                $db->execute('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan,coa_kode) VALUES(?,?,0,?,?,?)', 
                           [$tanggal, $kreditAkun, $total, 'Pembelian #'.$pid, $kreditKode]);
                
            } else {
                // Debit Persediaan, Kredit Hutang Usaha
                $debetInfo = $getCoaInfo($akunPersediaan);
                $kreditInfo = $getCoaInfo($akunHutang);
                $debetAkun = $debetInfo['nama'] ?: 'Persediaan Barang Dagang';
                $kreditAkun = $kreditInfo['nama'] ?: 'Hutang Usaha';
                $debetKode = $debetInfo['kode'] ?: 'PERSEDIAAN';
                $kreditKode = $kreditInfo['kode'] ?: 'HUTANG';
                
                $db->execute('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan,coa_kode) VALUES(?,?,?,0,?,?)', 
                           [$tanggal, $debetAkun, $total, 'Pembelian #'.$pid, $debetKode]);
                
                $db->execute('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan,coa_kode) VALUES(?,?,0,?,?,?)', 
                           [$tanggal, $kreditAkun, $total, 'Pembelian #'.$pid, $kreditKode]);
            }
            
            $db->getConnection()->commit();
            echo json_encode(['status'=>'ok', 'message'=>'Pembelian berhasil disimpan']);
            
        } catch (Exception $e) {
            $db->getConnection()->rollBack();
            echo json_encode(['status'=>'error', 'message'=>'Gagal menyimpan: ' . $e->getMessage()]);
        }
        exit;
    }
}
// Tampilkan formulir pembelian
$barang = $db->fetchAll('SELECT id,kode,nama,stok,harga FROM stok ORDER BY nama');
$suppliers = $db->fetchAll('SELECT id,kode,nama,alamat FROM supplier ORDER BY nama');
?>

<!-- Header Section -->
<div class="mb-8">
  <div class="flex items-center mb-6">
    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mr-6 shadow-lg">
      <i class='bx bx-cart text-white text-3xl'></i>
    </div>
    <div>
      <h1 class="text-4xl font-bold text-gray-900 mb-2">Pembelian</h1>
      <p class="text-gray-600 text-lg">Buat transaksi pembelian baru dengan mudah</p>
    </div>
  </div>
  
  <!-- Quick Stats -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-lg">
      <div class="flex items-center">
        <i class='bx bx-store text-2xl mr-3'></i>
        <div>
          <p class="text-blue-100 text-sm">Total Supplier</p>
          <p class="text-2xl font-bold"><?php echo count($suppliers); ?></p>
        </div>
      </div>
    </div>
    
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
      <div class="flex items-center">
        <i class='bx bx-package text-2xl mr-3'></i>
        <div>
          <p class="text-green-100 text-sm">Total Barang</p>
          <p class="text-2xl font-bold"><?php echo count($barang); ?></p>
        </div>
      </div>
    </div>
    
    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-4 text-white shadow-lg">
      <div class="flex items-center">
        <i class='bx bx-calendar text-2xl mr-3'></i>
        <div>
          <p class="text-purple-100 text-sm">Tanggal</p>
          <p class="text-lg font-bold"><?php echo date('d M Y'); ?></p>
        </div>
      </div>
    </div>
    
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-4 text-white shadow-lg">
      <div class="flex items-center">
        <i class='bx bx-time text-2xl mr-3'></i>
        <div>
          <p class="text-orange-100 text-sm">Waktu</p>
          <p class="text-lg font-bold"><?php echo date('H:i'); ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Form -->
<div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
  <form id="pembelianForm" class="p-8">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    
    <!-- Header Form -->
    <div class="flex items-center mb-8 pb-6 border-b border-gray-100">
      <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
        <i class='bx bx-edit text-green-600 text-xl'></i>
      </div>
      <div class="flex-1">
        <h2 class="text-2xl font-bold text-gray-900">Form Pembelian</h2>
        <p class="text-gray-600">Isi detail transaksi pembelian</p>
      </div>
      
      <!-- Debug Button -->
      <button type="button" onclick="debugForm()" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-medium transition-colors">
        🐛 Debug Form
      </button>
    </div>
    
    <!-- Basic Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-3">Supplier</label>
          <select id="supplier_id" name="supplier_id" class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-lg py-3 px-4" required>
            <option value="">-- Pilih Supplier --</option>
            <?php foreach($suppliers as $s): ?>
              <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['kode'] . ' - ' . $s['nama']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-3">Jenis Pembayaran</label>
          <select id="jenis_pembayaran" name="jenis_pembayaran" class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-lg py-3 px-4" onchange="hitungTotal()" required>
            <option value="cash">💵 Cash</option>
            <option value="bank">🏦 Bank Transfer</option>
            <option value="kredit">📋 Kredit</option>
          </select>
        </div>
      </div>
      
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-3">Tanggal Transaksi</label>
          <input id="tanggal" name="tanggal" type="datetime-local" class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-lg py-3 px-4" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
        </div>
        
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-3">Status Pembayaran</label>
          <div id="jenis_pembayaran_display" class="w-full border-gray-200 rounded-xl bg-gray-50 text-lg py-3 px-4 font-medium text-center">
            💵 Cash
          </div>
        </div>
      </div>
    </div>

    <!-- Item Selection -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 mb-8">
      <div class="flex items-center mb-6">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
          <i class='bx bx-plus text-blue-600 text-lg'></i>
        </div>
        <div>
          <h3 class="text-xl font-bold text-gray-900">Tambah Item</h3>
          <p class="text-gray-600">Pilih barang yang akan dibeli</p>
        </div>
      </div>
      
      <div class="flex gap-4">
        <select id="pilih_barang" class="flex-1 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-lg py-3 px-4">
          <option value="">-- Pilih Barang --</option>
          <?php foreach($barang as $b): ?>
            <option value="<?php echo $b['id']; ?>" data-harga="<?php echo $b['harga']; ?>">
              <?php echo htmlspecialchars($b['kode'] . ' - ' . $b['nama'] . ' (Stok: ' . $b['stok'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input id="qty" type="number" value="1" min="1" class="w-32 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-lg py-3 px-4 text-center">
        <button type="button" onclick="tambahItem()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg font-medium">
          <i class='bx bx-plus mr-2'></i>Tambah
        </button>
      </div>
    </div>
    
    <!-- Items Table -->
    <div class="mb-8">
      <div class="flex items-center mb-4">
        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
          <i class='bx bx-list-ul text-green-600'></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900">Daftar Item</h3>
      </div>
      
      <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full" id="tblItems">
          <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nama Barang</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Qty</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Harga</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Subtotal</th>
              <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
    
    <!-- Calculation Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-3">Diskon (%)</label>
          <input id="diskon" name="diskon" type="number" value="0" min="0" max="100" class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-lg py-3 px-4" onchange="hitungTotal()">
        </div>
        
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-3">Pajak (%)</label>
          <input id="pajak" name="pajak" type="number" value="0" min="0" max="100" class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-lg py-3 px-4" onchange="hitungTotal()">
        </div>
      </div>
      
      <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl p-6">
        <div class="flex items-center mb-4">
          <i class='bx bx-calculator text-green-600 text-xl mr-3'></i>
          <h3 class="text-lg font-bold text-gray-900">Ringkasan Biaya</h3>
        </div>
        
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Subtotal:</span>
            <span id="subtotal_display" class="font-semibold text-gray-900">Rp 0</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Diskon:</span>
            <span id="diskon_display" class="font-semibold text-red-600">Rp 0</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Pajak:</span>
            <span id="pajak_display" class="font-semibold text-blue-600">Rp 0</span>
          </div>
          <div class="border-t border-gray-200 pt-3 mt-3">
            <div class="flex justify-between items-center">
              <span class="text-lg font-bold text-gray-900">Total:</span>
              <span id="total_display" class="text-2xl font-bold text-green-600">Rp 0</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Submit Button -->
    <div class="text-center pt-6 border-t border-gray-100">
      <button type="submit" class="px-12 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-2xl text-xl font-bold hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-xl hover:shadow-2xl transform hover:-translate-y-1">
        <i class='bx bx-save mr-3'></i>Simpan Pembelian
      </button>
    </div>
  </form>
</div>

<script>
let items = [];

function tambahItem(){
  const sel = document.getElementById('pilih_barang');
  const id = sel.value; 
  if(!id) {
    Swal.fire('Peringatan', 'Pilih barang dulu', 'warning');
    return;
  }
  
  const txt = sel.options[sel.selectedIndex].text;
  const harga = Number(sel.options[sel.selectedIndex].dataset.harga || 0);
  const qty = Number(document.getElementById('qty').value || 0);
  
  if(qty <= 0) {
    Swal.fire('Peringatan', 'Quantity harus lebih dari 0', 'warning');
    return;
  }
  
  // Check if item already exists
  const existingIndex = items.findIndex(item => item.id === id);
  if (existingIndex !== -1) {
    items[existingIndex].qty += qty;
  } else {
    items.push({id: id, nama: txt, qty: qty, harga: harga});
  }
  
  renderItems();
  hitungTotal();
  document.getElementById('qty').value = 1;
  sel.value = '';
}

function renderItems(){
  const tbody = document.querySelector('#tblItems tbody'); 
  tbody.innerHTML = '';
  
  if (items.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="px-6 py-12 text-center">
          <div class="flex flex-col items-center">
            <i class='bx bx-package text-4xl text-gray-300 mb-4'></i>
            <p class="text-gray-500 text-lg">Belum ada item ditambahkan</p>
            <p class="text-gray-400 text-sm">Pilih barang di atas untuk menambahkan item</p>
          </div>
        </td>
      </tr>
    `;
    return;
  }
  
  items.forEach((it, i) => {
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';
    tr.innerHTML = `
      <td class='px-6 py-4'>
        <div class="flex items-center">
          <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
            <i class='bx bx-package text-blue-600 text-sm'></i>
          </div>
          <span class="font-medium text-gray-900">${it.nama}</span>
        </div>
      </td>
      <td class='px-6 py-4'>
        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">${it.qty}</span>
      </td>
      <td class='px-6 py-4 font-medium text-gray-900'>Rp ${it.harga.toLocaleString()}</td>
      <td class='px-6 py-4 font-bold text-green-600'>Rp ${(it.qty * it.harga).toLocaleString()}</td>
      <td class='px-6 py-4'>
        <button type="button" onclick='hapusItem(${i})' class='bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors font-medium'>
          <i class='bx bx-trash mr-1'></i>Hapus
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function hapusItem(i){ 
  items.splice(i, 1); 
  renderItems(); 
  hitungTotal();
}

function hitungTotal(){
  const jenisPembayaran = document.getElementById('jenis_pembayaran').value;
  const paymentIcons = {
    'cash': '💵 Cash',
    'bank': '🏦 Bank Transfer', 
    'kredit': '📋 Kredit'
  };
  document.getElementById('jenis_pembayaran_display').innerHTML = paymentIcons[jenisPembayaran] || '💵 Cash';
  
  let subtotal = 0;
  items.forEach(item => {
    subtotal += item.qty * item.harga;
  });
  
  const diskonPercent = Number(document.getElementById('diskon').value || 0);
  const pajakPercent = Number(document.getElementById('pajak').value || 0);
  
  const diskonAmount = subtotal * (diskonPercent / 100);
  const afterDisk = subtotal - diskonAmount;
  const pajakAmount = afterDisk * (pajakPercent / 100);
  const total = afterDisk + pajakAmount;
  
  document.getElementById('subtotal_display').textContent = `Rp ${subtotal.toLocaleString()}`;
  document.getElementById('diskon_display').textContent = `Rp ${diskonAmount.toLocaleString()}`;
  document.getElementById('pajak_display').textContent = `Rp ${pajakAmount.toLocaleString()}`;
  document.getElementById('total_display').textContent = `Rp ${total.toLocaleString()}`;
}

// Form submission dengan debug logging
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PEMBELIAN PAGE LOADED ===');
    
    const form = document.getElementById('pembelianForm');
    if (form) {
        console.log('✅ Form pembelian found');
        
        // Add event listener langsung tanpa cloning
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('✅ Form submitted! Event listener working');
            
            if(items.length == 0) {
                console.log('❌ No items added');
                Swal.fire('Peringatan', 'Tambah item dulu', 'warning');
                return;
            }
            
            const supplierId = document.getElementById('supplier_id').value;
            if(!supplierId) {
                console.log('❌ No supplier selected');
                Swal.fire('Peringatan', 'Pilih supplier dulu', 'warning');
                return;
            }
            
            console.log('✅ Validation passed, showing confirmation...');
            
            // Konfirmasi sebelum simpan
            Swal.fire({
                title: 'Konfirmasi Simpan',
                text: 'Apakah Anda yakin ingin menyimpan transaksi pembelian ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('✅ User confirmed, preparing data...');
                    
                    // Show loading state
                    const submitBtn = e.target.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin mr-3"></i>Menyimpan...';
                    submitBtn.disabled = true;
                    
                    const data = new FormData();
                    data.append('aksi', 'simpan');
                    data.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                    data.append('tanggal', document.getElementById('tanggal').value || '');
                    data.append('supplier_id', supplierId);
                    data.append('jenis_pembayaran', document.getElementById('jenis_pembayaran').value);
                    data.append('diskon', document.getElementById('diskon').value || 0);
                    data.append('pajak', document.getElementById('pajak').value || 0);
                    data.append('items', JSON.stringify(items));
                    
                    console.log('✅ FormData prepared:', Object.fromEntries(data));
                    console.log('✅ Sending fetch request...');
                    
                    fetch('', {method: 'POST', body: data})
                        .then(r => {
                            console.log('✅ Response received, status:', r.status);
                            return r.json();
                        })
                        .then(j => {
                            console.log('✅ JSON parsed:', j);
                            
                            if(j.status == 'ok') {
                                console.log('✅ Success! Showing notifications...');
                                
                                // Show success notification
                                showNotification('success', 'Pembelian berhasil disimpan!', 'check-circle');
                                
                                // Reset form
                                resetForm();
                                
                                // Show success modal
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: j.message || 'Pembelian tersimpan',
                                    confirmButtonColor: '#10b981'
                                });
                            } else {
                                console.log('❌ Error response:', j.message);
                                
                                // Show error notification
                                showNotification('error', j.message || 'Gagal menyimpan pembelian', 'x-circle');
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: j.message || 'Gagal menyimpan pembelian',
                                    confirmButtonColor: '#ef4444'
                                });
                            }
                        })
                        .catch((error) => {
                            console.log('❌ Fetch error:', error);
                            
                            // Show error notification
                            showNotification('error', 'Kesalahan sistem', 'x-circle');
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan Sistem',
                                text: 'Tidak dapat terhubung ke server: ' + error.message,
                                confirmButtonColor: '#ef4444'
                            });
                        })
                        .finally(() => {
                            console.log('✅ Restoring button state...');
                            // Restore button state
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        });
                } else {
                    console.log('ℹ️ User cancelled confirmation');
                }
            });
        });
        
        console.log('✅ Event listener added successfully');
    } else {
        console.log('❌ Form pembelian not found!');
    }
    
    // Test SweetAlert2
    if (typeof Swal !== 'undefined') {
        console.log('✅ SweetAlert2 loaded');
    } else {
        console.log('❌ SweetAlert2 not loaded');
    }
    
    console.log('=== PEMBELIAN PAGE INITIALIZED ===');
});

// Debug function untuk test form
function debugForm() {
    console.log('=== DEBUG FORM PEMBELIAN ===');
    
    // Check form element
    const form = document.getElementById('pembelianForm');
    if (form) {
        console.log('✅ Form found:', form);
        console.log('✅ Form action:', form.action);
        console.log('✅ Form method:', form.method);
        console.log('✅ Form elements count:', form.elements.length);
    } else {
        console.log('❌ Form not found!');
        return;
    }
    
    // Check submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        console.log('✅ Submit button found:', submitBtn);
        console.log('✅ Submit button text:', submitBtn.textContent);
        console.log('✅ Submit button disabled:', submitBtn.disabled);
    } else {
        console.log('❌ Submit button not found!');
    }
    
    // Check CSRF token
    const csrfToken = document.querySelector('input[name="csrf_token"]');
    if (csrfToken) {
        console.log('✅ CSRF token found:', csrfToken.value);
    } else {
        console.log('❌ CSRF token not found!');
    }
    
    // Check items
    console.log('✅ Items array:', items);
    console.log('✅ Items count:', items.length);
    
    // Check supplier
    const supplierId = document.getElementById('supplier_id').value;
    console.log('✅ Supplier ID:', supplierId);
    
    // Check SweetAlert2
    if (typeof Swal !== 'undefined') {
        console.log('✅ SweetAlert2 loaded');
        
        // Test SweetAlert2
        Swal.fire({
            title: 'Debug Test',
            text: 'SweetAlert2 berfungsi! Sekarang test form submission...',
            icon: 'info',
            confirmButtonColor: '#10b981'
        }).then(() => {
            // Test form submission
            console.log('✅ Testing form submission...');
            
            // Add test items if empty
            if (items.length === 0) {
                console.log('✅ Adding test item...');
                items.push({id: '1', nama: 'Test Item', qty: 1, harga: 1000});
                renderItems();
            }
            
            // Set test supplier if empty
            if (!supplierId) {
                console.log('✅ Setting test supplier...');
                const supplierSelect = document.getElementById('supplier_id');
                if (supplierSelect.options.length > 1) {
                    supplierSelect.value = supplierSelect.options[1].value;
                }
            }
            
            // Trigger form submission
            form.dispatchEvent(new Event('submit'));
        });
    } else {
        console.log('❌ SweetAlert2 not loaded');
    }
    
    console.log('=== DEBUG COMPLETED ===');
}

// Function to show notification
function showNotification(type, message, icon) {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-out translate-x-full`;
  
  const bgColor = type === 'success' ? 'bg-green-50' : 'bg-red-50';
  const iconColor = type === 'success' ? 'text-green-400' : 'text-red-400';
  const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
  
  notification.innerHTML = `
    <div class="p-4 ${bgColor}">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <i class="bx bx-${icon} ${iconColor} text-xl"></i>
        </div>
        <div class="ml-3 w-0 flex-1 pt-0.5">
          <p class="text-sm font-medium ${textColor}">${message}</p>
        </div>
        <div class="ml-4 flex-shrink-0 flex">
          <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
            <i class="bx bx-x text-lg"></i>
          </button>
        </div>
      </div>
    </div>
  `;
  
  // Add to page
  document.body.appendChild(notification);
  
  // Animate in
  setTimeout(() => {
    notification.classList.remove('translate-x-full');
  }, 100);
  
  // Auto remove after 3 seconds
  setTimeout(() => {
    notification.classList.add('translate-x-full');
    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove();
      }
    }, 300);
  }, 3000);
}

// Function to reset form
function resetForm() {
  // Reset form fields
  document.getElementById('pembelianForm').reset();
  document.getElementById('tanggal').value = '<?php echo date('Y-m-d\TH:i'); ?>';
  
  // Reset items array
  items = [];
  
  // Re-render items table
  renderItems();
  
  // Recalculate total
  hitungTotal();
  
  // Reset payment display
  document.getElementById('jenis_pembayaran_display').innerHTML = '💵 Cash';
}

// Hitung total saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
  renderItems();
  hitungTotal();
});
</script>
