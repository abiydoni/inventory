<?php
$csrf_token = Helper::generateCSRF();
$search = $_GET['search'] ?? '';
$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE kode LIKE ? OR nama LIKE ? OR tipe LIKE ? OR laporan LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
}
$rows = $db->fetchAll("SELECT * FROM coa $where ORDER BY kode ASC", $params);
?>
<div class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Chart of Accounts (COA)</h1>
            <p class="text-sm text-gray-600 mt-1">Daftar akun untuk klasifikasi laporan</p>
        </div>
        <button onclick="openTambah()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <i class='bx bx-plus text-xl'></i><span>Tambah Akun</span>
        </button>
    </div>
</div>

<div class="bg-white p-6 mt-4 rounded-lg shadow-sm">
    <div class="flex items-center space-x-2 mb-4">
        <input id="searchInput" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari kode/nama/tipe/laporan" class="flex-1 px-3 py-2 border rounded-lg" />
        <button onclick="performSearch()" class="px-4 py-2 bg-gray-700 text-white rounded-lg">Cari</button>
        <?php if ($search !== ''): ?>
        <a href="?page=coa" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Reset</a>
        <?php endif; ?>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Kode</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Nama</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Tipe</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Laporan</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Status</th>
                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada akun</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono text-sm"><?php echo htmlspecialchars($r['kode']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($r['nama']); ?></td>
                        <td class="px-4 py-2 capitalize"><?php echo htmlspecialchars($r['tipe']); ?></td>
                        <td class="px-4 py-2 capitalize"><?php echo htmlspecialchars($r['laporan']); ?></td>
                        <td class="px-4 py-2"><?php echo ((int)$r['aktif'] === 1) ? '<span class="text-green-600">Aktif</span>' : '<span class="text-gray-500">Nonaktif</span>'; ?></td>
                        <td class="px-4 py-2">
                            <div class="flex items-center space-x-2">
                                <button onclick="edit(<?php echo (int)$r['id']; ?>)" class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg"><i class='bx bx-edit'></i> Edit</button>
                                <button onclick="hapus(<?php echo (int)$r['id']; ?>)" class="px-3 py-1 bg-red-50 text-red-700 rounded-lg"><i class='bx bx-trash'></i> Hapus</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function performSearch(){
  const s = document.getElementById('searchInput').value;
  location.href = `?page=coa&search=${encodeURIComponent(s)}`;
}

function openTambah(){
  Swal.fire({
    title: 'Tambah Akun',
    html: `
      <form id="f" class="text-left space-y-3">
        <input type="hidden" id="csrf" value="<?php echo $csrf_token; ?>">
        <div>
          <label class="text-sm">Kode</label>
          <input id="kode" class="w-full px-3 py-2 border rounded-lg" required>
        </div>
        <div>
          <label class="text-sm">Nama</label>
          <input id="nama" class="w-full px-3 py-2 border rounded-lg" required>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-sm">Tipe</label>
            <select id="tipe" class="w-full px-3 py-2 border rounded-lg" required>
              <option value="pendapatan">Pendapatan</option>
              <option value="hpp">HPP</option>
              <option value="beban">Beban</option>
              <option value="aset">Aset</option>
              <option value="liabilitas">Liabilitas</option>
              <option value="ekuitas">Ekuitas</option>
            </select>
          </div>
          <div>
            <label class="text-sm">Laporan</label>
            <select id="laporan" class="w-full px-3 py-2 border rounded-lg" required>
              <option value="laba_rugi">Laba Rugi</option>
              <option value="neraca">Neraca</option>
            </select>
          </div>
        </div>
        <div>
          <label class="text-sm">Status</label>
          <select id="aktif" class="w-full px-3 py-2 border rounded-lg">
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </div>
      </form>
    `,
    showCancelButton: true,
    confirmButtonText: 'Simpan',
    cancelButtonText: 'Batal',
    width: '520px',
    preConfirm: () => {
      const fd = new FormData();
      fd.append('action','tambah');
      fd.append('csrf_token', document.getElementById('csrf').value);
      fd.append('kode', document.getElementById('kode').value);
      fd.append('nama', document.getElementById('nama').value);
      fd.append('tipe', document.getElementById('tipe').value);
      fd.append('laporan', document.getElementById('laporan').value);
      fd.append('aktif', document.getElementById('aktif').value);
      return fetch('api/coa.php', {method:'POST', body: fd}).then(r=>r.json()).catch(()=>{
        Swal.showValidationMessage('Request error');
      });
    }
  }).then(res=>{
    if(res.isConfirmed){
      if(res.value.status==='success'){
        Swal.fire('Sukses', res.value.message, 'success').then(()=>location.reload());
      }else{
        Swal.fire('Error', res.value.message || 'Gagal menyimpan', 'error');
      }
    }
  });
}

function edit(id){
  fetch(`api/coa.php?action=get&id=${id}&token=<?php echo $csrf_token; ?>`).then(r=>r.json()).then(d=>{
    if(!d || d.status==='error') return Swal.fire('Error', d.message||'Data tidak ditemukan', 'error');
    Swal.fire({
      title: 'Edit Akun',
      html: `
        <form id="f" class="text-left space-y-3">
          <input type="hidden" id="csrf" value="<?php echo $csrf_token; ?>">
          <input type="hidden" id="id" value="${id}">
          <div>
            <label class="text-sm">Kode</label>
            <input id="kode" class="w-full px-3 py-2 border rounded-lg" value="${d.kode}" required>
          </div>
          <div>
            <label class="text-sm">Nama</label>
            <input id="nama" class="w-full px-3 py-2 border rounded-lg" value="${d.nama}" required>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm">Tipe</label>
              <select id="tipe" class="w-full px-3 py-2 border rounded-lg" required>
                ${['pendapatan','hpp','beban','aset','liabilitas','ekuitas'].map(v=>`<option value="${v}" ${d.tipe===v?'selected':''}>${v.charAt(0).toUpperCase()+v.slice(1)}</option>`).join('')}
              </select>
            </div>
            <div>
              <label class="text-sm">Laporan</label>
              <select id="laporan" class="w-full px-3 py-2 border rounded-lg" required>
                <option value="laba_rugi" ${d.laporan==='laba_rugi'?'selected':''}>Laba Rugi</option>
                <option value="neraca" ${d.laporan==='neraca'?'selected':''}>Neraca</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-sm">Status</label>
            <select id="aktif" class="w-full px-3 py-2 border rounded-lg">
              <option value="1" ${d.aktif==1?'selected':''}>Aktif</option>
              <option value="0" ${d.aktif==0?'selected':''}>Nonaktif</option>
            </select>
          </div>
        </form>
      `,
      showCancelButton: true,
      confirmButtonText: 'Update',
      cancelButtonText: 'Batal',
      width: '520px',
      preConfirm: () => {
        const fd = new FormData();
        fd.append('action','edit');
        fd.append('csrf_token', document.getElementById('csrf').value);
        fd.append('id', document.getElementById('id').value);
        fd.append('kode', document.getElementById('kode').value);
        fd.append('nama', document.getElementById('nama').value);
        fd.append('tipe', document.getElementById('tipe').value);
        fd.append('laporan', document.getElementById('laporan').value);
        fd.append('aktif', document.getElementById('aktif').value);
        return fetch('api/coa.php', {method:'POST', body: fd}).then(r=>r.json()).catch(()=>{
          Swal.showValidationMessage('Request error');
        });
      }
    }).then(res=>{
      if(res.isConfirmed){
        if(res.value.status==='success'){
          Swal.fire('Sukses', res.value.message, 'success').then(()=>location.reload());
        }else{
          Swal.fire('Error', res.value.message || 'Gagal memperbarui', 'error');
        }
      }
    });
  }).catch(()=>Swal.fire('Error','Gagal memuat data','error'));
}

function hapus(id){
  Swal.fire({title:'Hapus Akun?', text:'Tindakan ini tidak dapat dibatalkan', icon:'warning', showCancelButton:true, confirmButtonText:'Hapus', cancelButtonText:'Batal'}).then(r=>{
    if(r.isConfirmed){
      const fd = new FormData();
      fd.append('action','hapus');
      fd.append('csrf_token','<?php echo $csrf_token; ?>');
      fd.append('id', id);
      fetch('api/coa.php', {method:'POST', body: fd}).then(r=>r.json()).then(d=>{
        if(d.status==='success'){
          Swal.fire('Sukses','Akun dihapus','success').then(()=>location.reload());
        }else{
          Swal.fire('Error', d.message||'Gagal menghapus', 'error');
        }
      }).catch(()=>Swal.fire('Error','Request gagal','error'));
    }
  });
}
</script>
