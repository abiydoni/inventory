<!-- --- FILE: views/penjualan/index.php --- -->
<?php
// mirip pembelian, namun mengurangi stok dan mencatat jurnal penjualan
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    if ($aksi=='simpan') {
        $tanggal = $_POST['tanggal'] ?: date('Y-m-d H:i:s');
        $jenis_pembayaran = $_POST['jenis_pembayaran'] ?? 'cash';
        $customer_id = (int)($_POST['customer_id'] ?? 0);
        $diskon = (float)$_POST['diskon']; // percent
        $pajak = (float)$_POST['pajak'];
        $items = json_decode($_POST['items'], true);
        
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
        
        // simpan penjualan
        $s = $db->prepare('INSERT INTO penjualan (tanggal,customer_id,jenis_pembayaran,status_pembayaran,subtotal,diskon,pajak,total) VALUES(:t,:cust,:jp,:sp,:sub,:disc,:pajak,:tot)');
        $s->execute([':t'=>$tanggal,':cust'=>$customer_id,':jp'=>$jenis_pembayaran,':sp'=>$status_pembayaran,':sub'=>$subtotal,':disc'=>$diskon,':pajak'=>$pajak,':tot'=>$total]);
        $pid = $db->lastInsertId();
        
        $ins = $db->prepare('INSERT INTO penjualan_detail (penjualan_id,stok_id,qty,harga) VALUES(:pid,:sid,:qty,:harga)');
        foreach ($items as $it) {
            $ins->execute([':pid'=>$pid,':sid'=>$it['id'],':qty'=>$it['qty'],':harga'=>$it['harga']]);
            // update stok
            $db->prepare('UPDATE stok SET stok = stok - :q WHERE id=:id')->execute([':q'=>$it['qty'],':id'=>$it['id']]);
        }
        
        // jurnal berdasarkan jenis pembayaran
        if ($jenis_pembayaran == 'cash') {
            // Debit Kas, Kredit Pendapatan Penjualan
            $db->prepare('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan) VALUES(:t,:akun,:d,0,:ket)')
               ->execute([':t'=>$tanggal,':akun'=>'Kas',':d'=>$total,':ket'=>'Penjualan #'.$pid]);
            $db->prepare('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan) VALUES(:t,:akun,0,:k,:ket)')
               ->execute([':t'=>$tanggal,':akun'=>'Pendapatan Penjualan',':k'=>$total,':ket'=>'Penjualan #'.$pid]);
        } elseif ($jenis_pembayaran == 'bank') {
            // Debit Bank, Kredit Pendapatan Penjualan
            $db->prepare('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan) VALUES(:t,:akun,:d,0,:ket)')
               ->execute([':t'=>$tanggal,':akun'=>'Bank',':d'=>$total,':ket'=>'Penjualan #'.$pid]);
            $db->prepare('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan) VALUES(:t,:akun,0,:k,:ket)')
               ->execute([':t'=>$tanggal,':akun'=>'Pendapatan Penjualan',':k'=>$total,':ket'=>'Penjualan #'.$pid]);
        } else {
            // Debit Piutang Usaha, Kredit Pendapatan Penjualan
            $db->prepare('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan) VALUES(:t,:akun,:d,0,:ket)')
               ->execute([':t'=>$tanggal,':akun'=>'Piutang Usaha',':d'=>$total,':ket'=>'Penjualan #'.$pid]);
            $db->prepare('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan) VALUES(:t,:akun,0,:k,:ket)')
               ->execute([':t'=>$tanggal,':akun'=>'Pendapatan Penjualan',':k'=>$total,':ket'=>'Penjualan #'.$pid]);
        }
        
        echo json_encode(['status'=>'ok']); exit;
    }
}
$barang = $db->query('SELECT id,kode,nama,stok,harga FROM stok')->fetchAll(PDO::FETCH_ASSOC);
$customers = $db->query('SELECT id,kode,nama,alamat FROM customer ORDER BY nama')->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-4">Penjualan</h1>
<div class="bg-white p-4 shadow rounded">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Pelanggan</label>
      <div x-data="customerDropdown()" class="relative">
        <input 
          x-ref="input"
          @input="search = $event.target.value; showDropdown = true"
          @click="showDropdown = true"
          @click.away="showDropdown = false"
          :value="selectedCustomer ? selectedCustomer.nama : search"
          placeholder="Cari pelanggan..."
          class="border p-2 w-full rounded-lg"
        >
        <div x-show="showDropdown" x-transition class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
          <template x-for="customer in filteredCustomers" :key="customer.id">
            <div 
              @click="selectCustomer(customer)"
              class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100"
            >
              <div class="font-medium" x-text="customer.nama"></div>
              <div class="text-sm text-gray-600" x-text="customer.alamat"></div>
            </div>
          </template>
          <div x-show="filteredCustomers.length === 0" class="px-3 py-2 text-gray-500">
            Tidak ada pelanggan ditemukan
          </div>
        </div>
        <input type="hidden" id="customer_id" x-model="selectedCustomerId">
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
      <input id="tanggal" type="datetime-local" class="border p-2 w-full rounded-lg" value="<?php echo date('Y-m-d\TH:i'); ?>">
    </div>
  </div>
  
  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pembayaran</label>
    <select id="jenis_pembayaran" class="border p-2 w-full rounded-lg" onchange="hitungTotal()">
      <option value="cash">Cash</option>
      <option value="bank">Bank</option>
      <option value="kredit">Kredit</option>
    </select>
  </div>

  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Tambah Item</label>
    <div class="flex gap-2">
      <select id="pilih_barang" class="border p-2 flex-1 rounded-lg">
        <option value="">-- pilih barang --</option>
        <?php foreach($barang as $b) echo "<option value='{$b['id']}' data-harga='{$b['harga']}' data-stok='{$b['stok']}'>{$b['kode']} - {$b['nama']} (Stok: {$b['stok']})</option>"; ?>
      </select>
      <input id="qty" type="number" value="1" min="1" class="w-24 border p-2 rounded-lg">
      <button onclick="tambahItem()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Tambah</button>
    </div>
  </div>
  
  <div class="mb-4">
    <table class="w-full bg-white border rounded-lg" id="tblItems">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Nama Barang</th>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Qty</th>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Harga</th>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Subtotal</th>
          <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
  
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Diskon (%)</label>
      <input id="diskon" type="number" value="0" min="0" max="100" class="border p-2 w-full rounded-lg" onchange="hitungTotal()">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Pajak (%)</label>
      <input id="pajak" type="number" value="0" min="0" max="100" class="border p-2 w-full rounded-lg" onchange="hitungTotal()">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pembayaran</label>
      <div id="jenis_pembayaran_display" class="p-2 bg-gray-100 rounded-lg text-center font-medium">
        Cash
      </div>
    </div>
  </div>
  
  <div class="bg-gray-50 p-4 rounded-lg mb-4">
    <div class="grid grid-cols-2 gap-4 text-right">
      <div>
        <span class="text-gray-600">Subtotal:</span>
        <span id="subtotal_display" class="ml-2 font-medium">Rp 0</span>
      </div>
      <div>
        <span class="text-gray-600">Diskon:</span>
        <span id="diskon_display" class="ml-2 font-medium">Rp 0</span>
      </div>
      <div>
        <span class="text-gray-600">Pajak:</span>
        <span id="pajak_display" class="ml-2 font-medium">Rp 0</span>
      </div>
      <div class="text-lg font-bold text-blue-600">
        <span>Total:</span>
        <span id="total_display" class="ml-2">Rp 0</span>
      </div>
    </div>
  </div>
  
  <div class="text-center">
    <button onclick="simpanPenjualan()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg text-lg font-medium">
      <i class='bx bx-save mr-2'></i>Simpan Penjualan
    </button>
  </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function customerDropdown() {
  return {
    customers: <?php echo json_encode($customers); ?>,
    search: '',
    showDropdown: false,
    selectedCustomer: null,
    selectedCustomerId: null,
    get filteredCustomers() {
      if (!this.search) return this.customers;
      const searchLower = this.search.toLowerCase();
      return this.customers.filter(c => 
        c.nama.toLowerCase().includes(searchLower) || 
        c.kode.toLowerCase().includes(searchLower)
      );
    },
    selectCustomer(customer) {
      this.selectedCustomer = customer;
      this.selectedCustomerId = customer.id;
      this.showDropdown = false;
      this.search = '';
    }
  }
}

let items = [];

function tambahItem(){
  const sel = document.getElementById('pilih_barang');
  const id = sel.value; if(!id) return Swal.fire('Pilih barang dulu');
  const txt = sel.options[sel.selectedIndex].text;
  const harga = Number(sel.options[sel.selectedIndex].dataset.harga || 0);
  const stok = Number(sel.options[sel.selectedIndex].dataset.stok || 0);
  const qty = Number(document.getElementById('qty').value || 0);
  
  if(qty <= 0) return Swal.fire('Quantity harus lebih dari 0');
  if(qty > stok) return Swal.fire(`Stok tidak mencukupi. Stok tersedia: ${stok}`);
  
  items.push({id:id, nama:txt, qty:qty, harga:harga});
  renderItems();
  hitungTotal();
  document.getElementById('qty').value = 1;
}

function renderItems(){
  const tbody = document.querySelector('#tblItems tbody'); 
  tbody.innerHTML='';
  items.forEach((it,i)=>{
    const tr = document.createElement('tr');
    tr.innerHTML=`
      <td class='px-4 py-2'>${it.nama}</td>
      <td class='px-4 py-2'>${it.qty}</td>
      <td class='px-4 py-2'>Rp ${it.harga.toLocaleString()}</td>
      <td class='px-4 py-2'>Rp ${(it.qty * it.harga).toLocaleString()}</td>
      <td class='px-4 py-2'>
        <button onclick='hapusItem(${i})' class='bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm'>
          <i class='bx bx-trash'></i> Hapus
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function hapusItem(i){ 
  items.splice(i,1); 
  renderItems(); 
  hitungTotal();
}

function hitungTotal(){
  const jenisPembayaran = document.getElementById('jenis_pembayaran').value;
  document.getElementById('jenis_pembayaran_display').textContent = jenisPembayaran.charAt(0).toUpperCase() + jenisPembayaran.slice(1);
  
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

function simpanPenjualan(){
  if(items.length==0) return Swal.fire('Tambah item dulu');
  const customerId = document.getElementById('customer_id').value;
  if(!customerId) return Swal.fire('Pilih pelanggan dulu');
  
  const data = new FormData();
  data.append('aksi','simpan');
  data.append('tanggal', document.getElementById('tanggal').value || '');
  data.append('customer_id', customerId);
  data.append('jenis_pembayaran', document.getElementById('jenis_pembayaran').value);
  data.append('diskon', document.getElementById('diskon').value||0);
  data.append('pajak', document.getElementById('pajak').value||0);
  data.append('items', JSON.stringify(items));
  
  fetch('', {method:'POST', body:data}).then(r=>r.json()).then(j=>{
    if(j.status=='ok') {
      Swal.fire('Sukses','Penjualan tersimpan','success').then(()=>location.reload());
    } else {
      Swal.fire('Error','Gagal menyimpan','error');
    }
  });
}

// Hitung total saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
  hitungTotal();
});
</script>
