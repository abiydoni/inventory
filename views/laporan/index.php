<!-- --- FILE: views/laporan/index.php --- -->
<h1 class="text-2xl font-bold mb-4">Laporan</h1>
<div class="bg-white p-4 shadow rounded">
  <h3 class="font-bold">Laporan Rugi/Laba (Sederhana)</h3>
  <?php
  // sederhana: total pendapatan - total HPP (kita asumsikan HPP = pembelian)
  $pend = $db->query('SELECT SUM(total) as t FROM penjualan')->fetch(PDO::FETCH_ASSOC);
  $biaya = $db->query('SELECT SUM(total) as t FROM pembelian')->fetch(PDO::FETCH_ASSOC);
  $laba = ($pend['t']??0) - ($biaya['t']??0);
  ?>
  <table class="w-full mt-3">
    <tr><td>Total Penjualan</td><td><?php echo Helper::formatMoney($pend['t']??0); ?></td></tr>
    <tr><td>Total Pembelian</td><td><?php echo Helper::formatMoney($biaya['t']??0); ?></td></tr>
    <tr class="font-bold"><td>Laba/Rugi</td><td><?php echo Helper::formatMoney($laba); ?></td></tr>
  </table>
</div>
