<!-- --- FILE: views/jurnal/index.php --- -->
<h1 class="text-2xl font-bold mb-4">Jurnal Umum</h1>
<div class="bg-white p-4 shadow rounded">
  <table class="w-full table-auto">
    <thead class="bg-gray-100"><tr><th>Tanggal</th><th>Akun</th><th>Debit</th><th>Kredit</th><th>Keterangan</th></tr></thead>
    <tbody>
      <?php $rows = $db->query('SELECT * FROM jurnal ORDER BY id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
      foreach($rows as $r) echo '<tr class="border-t"><td class="p-2">'.$r['tanggal'].'</td><td class="p-2">'.$r['akun'].'</td><td class="p-2">'.Helper::formatMoney($r['debit']).'</td><td class="p-2">'.Helper::formatMoney($r['kredit']).'</td><td class="p-2">'.$r['keterangan'].'</td></tr>';
      ?>
    </tbody>
  </table>
</div>
