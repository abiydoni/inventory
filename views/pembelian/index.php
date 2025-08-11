<!-- --- FILE: views/pembelian/index.php --- -->
<?php
// Halaman pembelian: buat transaksi pembelian dgn detail
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['aksi'])) {
    $aksi = $_POST['aksi'];
    if ($aksi=='simpan') {
        $tanggal = $_POST['tanggal'] ?: date('Y-m-d H:i:s');
        $supplier = $_POST['supplier'] ?: 'Umum';
        $diskon = (float)$_POST['diskon']; // percent
        $pajak = (float)$_POST['pajak'];
        $items = json_decode($_POST['items'], true);
        // hitung subtotal
        $subtotal = 0;
        foreach ($items as $it) $subtotal += intval($it['qty'])*intval($it['harga']);
        $afterDisk = $subtotal - ($subtotal * $diskon/100);
        $afterTax = $afterDisk + ($afterDisk * $pajak/100);
        $total = round($afterTax);
        // simpan pembelian
        $s = $db->prepare('INSERT INTO pembelian (tanggal,supplier,subtotal,diskon,pajak,total) VALUES(:t,:sup,:sub,:disc,:pajak,:tot)');
        $s->execute([':t'=>$tanggal,':sup'=>$supplier,':sub'=>$subtotal,':disc'=>$diskon,':pajak'=>$pajak,':tot'=>$total]);
        $pid = $db->lastInsertId();
        $ins = $db->prepare('INSERT INTO pembelian_detail (pembelian_id,stok_id,qty,harga) VALUES(:pid,:sid,:qty,:harga)');
        foreach ($items as $it) {
            $ins->execute([':pid'=>$pid,':sid'=>$it['id'],':qty'=>$it['qty'],':harga'=>$it['harga']]);
            // update stok
            $db->prepare('UPDATE stok SET stok = stok + :q WHERE id=:id')->execute([':q'=>$it['qty'],':id'=>$it['id']]);
        }
        // jurnal - debit persediaan, kredit kas (sederhana)
        $db->prepare('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan) VALUES(:t,:akun,:d,0,:ket)')
           ->execute([':t'=>$tanggal,':akun'=>'Persediaan',':d'=>$total,':ket'=>'Pembelian #'.$pid]);
        $db->prepare('INSERT INTO jurnal (tanggal,akun,debit,kredit,keterangan) VALUES(:t,:akun,0,:k,:ket)')
           ->execute([':t'=>$tanggal,':akun'=>'Kas',':k'=>$total,':ket'=>'Pembelian #'.$pid]);
        echo json_encode(['status'=>'ok']); exit;
    }
}
// Tampilkan formulir pembelian
$barang = $db->query('SELECT id,kode,nama,stok,harga FROM stok')->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-2xl font-bold mb-4">Pembelian</h1>
<div class="bg-white p-4 shadow rounded">
  <div class="mb-3">
    <label class="block">Supplier</label>
    <input id="supplier" class="border p-2 w-full" placeholder="Nama supplier">
  </div>
  <div class="mb-3">
    <label class="block">Tanggal</label>
    <input id="tanggal" type="datetime-local" class="border p-2 w-full">
  </div>
  <div>
    <label class="block font-bold">Tambah Item</label>
    <div class="flex gap-2">
      <select id="pilih_barang" class="border p-2 flex-1">
        <option value="">-- pilih barang --</option>
        <?php foreach($barang as $b) echo "<option value='{$b['id']}' data-harga='{$b['harga']}'>{$b['kode']} - {$b['nama']}</option>"; ?>
      </select>
      <input id="qty" type="number" value="1" class="w-24 border p-2">
      <button onclick="tambahItem()" class="bg-blue-500 text-white px-3 rounded">Tambah</button>
    </div>
  </div>
  <div class="mt-4">
    <table class="w-full bg-white" id="tblItems">
      <thead class="bg-gray-100"><tr><th>Nama</th><th>Qty</th><th>Harga</th><th>Aksi</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
  <div class="mt-4">
    <label>Diskon (%)</label>
    <input id="diskon" type="number" value="0" class="border p-2 w-32">
    <label class="ml-4">Pajak (%)</label>
    <input id="pajak" type="number" value="0" class="border p-2 w-32">
  </div>
  <div class="mt-4">
    <button onclick="simpanPembelian()" class="bg-green-500 text-white px-4 py-2 rounded">Simpan Pembelian</button>
  </div>
</div>
<script>
let items = [];
function tambahItem(){
  const sel = document.getElementById('pilih_barang');
  const id = sel.value; if(!id) return Swal.fire('Pilih barang dulu');
  const txt = sel.options[sel.selectedIndex].text;
  const harga = Number(sel.options[sel.selectedIndex].dataset.harga || 0);
  const qty = Number(document.getElementById('qty').value || 0);
  items.push({id:id, nama:txt, qty:qty, harga:harga});
  renderItems();
}
function renderItems(){
  const tbody = document.querySelector('#tblItems tbody'); tbody.innerHTML='';
  items.forEach((it,i)=>{
    const tr = document.createElement('tr');
    tr.innerHTML=`<td class='p-2'>${it.nama}</td><td class='p-2'>${it.qty}</td><td class='p-2'>${it.harga}</td><td class='p-2'><button onclick='hapusItem(${i})' class='bg-red-500 text-white px-2 rounded'>Hapus</button></td>`;
    tbody.appendChild(tr);
  });
}
function hapusItem(i){ items.splice(i,1); renderItems(); }
function simpanPembelian(){
  if(items.length==0) return Swal.fire('Tambah item dulu');
  const data = new FormData();
  data.append('aksi','simpan');
  data.append('tanggal', document.getElementById('tanggal').value || '');
  data.append('supplier', document.getElementById('supplier').value || '');
  data.append('diskon', document.getElementById('diskon').value||0);
  data.append('pajak', document.getElementById('pajak').value||0);
  data.append('items', JSON.stringify(items));
  fetch('', {method:'POST', body:data}).then(r=>r.json()).then(j=>{
    if(j.status=='ok') Swal.fire('Sukses','Pembelian tersimpan','success').then(()=>location.reload()); else Swal.fire('Error','Gagal','error');
  });
}
</script>
