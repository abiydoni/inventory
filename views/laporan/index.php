<!-- --- FILE: views/laporan/index.php --- -->
<div class="bg-white shadow-sm border-b border-gray-200">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan</h1>
                <p class="text-sm text-gray-600 mt-1">Laporan keuangan dan analisis bisnis</p>
            </div>
        </div>
    </div>
</div>

<?php
// Filter periode
$bulan = isset($_GET['bulan']) && $_GET['bulan'] !== '' ? (int)$_GET['bulan'] : '';
$tahun = isset($_GET['tahun']) && $_GET['tahun'] !== '' ? (int)$_GET['tahun'] : '';

$where = '1=1';
$params = [];
if ($bulan !== '') {
    $where .= " AND strftime('%m', tanggal) = :bulan";
    $params[':bulan'] = str_pad((string)$bulan, 2, '0', STR_PAD_LEFT);
}
if ($tahun !== '') {
    $where .= " AND strftime('%Y', tanggal) = :tahun";
    $params[':tahun'] = (string)$tahun;
}

// Ambil daftar tahun dari jurnal untuk dropdown
$yearRows = [];
try {
    $yearRows = $db->fetchAll("SELECT DISTINCT strftime('%Y', tanggal) AS y FROM jurnal WHERE tanggal IS NOT NULL AND tanggal != '' ORDER BY y DESC");
} catch (Exception $e) {
    $yearRows = [];
}
?>

<div class="p-6 space-y-6">
    <!-- Filter Periode -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Filter Periode</h3>
        </div>
        <div class="p-6">
            <form method="get" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <input type="hidden" name="page" value="laporan"/>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Bulan</label>
                    <select name="bulan" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Semua</option>
                        <?php
                        $bulanNames = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                        for ($i=1;$i<=12;$i++) {
                            $sel = ($bulan === $i) ? 'selected' : '';
                            echo "<option value='$i' $sel>{$bulanNames[$i]}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tahun</label>
                    <select name="tahun" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Semua</option>
                        <?php
                        if (!empty($yearRows)) {
                            foreach ($yearRows as $yr) {
                                $y = (int)$yr['y'];
                                $sel = ($tahun === $y) ? 'selected' : '';
                                echo "<option value='{$y}' {$sel}>{$y}</option>";
                            }
                        } else {
                            $cy = (int)date('Y');
                            for ($y=$cy; $y>=$cy-5; $y--) {
                                $sel = ($tahun === $y) ? 'selected' : '';
                                echo "<option value='{$y}' {$sel}>{$y}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">Terapkan</button>
                </div>
                <div class="flex items-end">
                    <a href="?page=laporan" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg w-full text-center">Reset</a>
                </div>
            </form>
            <p class="text-sm text-gray-500 mt-4">Periode: 
                <?php
                if ($bulan === '' && $tahun === '') {
                    echo 'Semua Periode';
                } else {
                    $label = [];
                    if ($bulan !== '') { $label[] = $bulanNames[$bulan]; }
                    if ($tahun !== '') { $label[] = (string)$tahun; }
                    echo implode(' ', $label);
                }
                ?>
            </p>
        </div>
    </div>

    <!-- Laporan Rugi/Laba (berbasis Jurnal) -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Laporan Rugi/Laba</h3>
            <p class="text-sm text-gray-600 mt-1">Berbasis entri pada jurnal</p>
        </div>
        <div class="p-6">
            <?php
            try {
                // Komponen pendapatan: penjualan (kredit akun mengandung 'penjualan')
                $pendapatan_penjualan = (int)($db->fetch(
                    "SELECT COALESCE(SUM(kredit),0) AS total FROM jurnal WHERE $where AND (lower(akun) LIKE '%penjualan%')",
                    $params
                )['total'] ?? 0);

                // Pendapatan lain-lain: kredit akun mengandung 'pendapatan' tapi bukan 'penjualan'
                $pendapatan_lain = (int)($db->fetch(
                    "SELECT COALESCE(SUM(kredit),0) AS total FROM jurnal WHERE $where AND (lower(akun) LIKE '%pendapatan%') AND (lower(akun) NOT LIKE '%penjualan%')",
                    $params
                )['total'] ?? 0);

                // HPP: debit akun yang mengandung 'hpp'
                $hpp = (int)($db->fetch(
                    "SELECT COALESCE(SUM(debit),0) AS total FROM jurnal WHERE $where AND (lower(akun) LIKE '%hpp%')",
                    $params
                )['total'] ?? 0);

                // Biaya operasional: debit akun yang mengandung 'biaya' atau 'beban'
                $biaya_operasional = (int)($db->fetch(
                    "SELECT COALESCE(SUM(debit),0) AS total FROM jurnal WHERE $where AND (lower(akun) LIKE '%biaya%' OR lower(akun) LIKE '%beban%')",
                    $params
                )['total'] ?? 0);

                // Total
                $total_pendapatan = $pendapatan_penjualan + $pendapatan_lain;
                $total_biaya = $hpp + $biaya_operasional;
                $laba_rugi = $total_pendapatan - $total_biaya;

                $status = $laba_rugi >= 0 ? 'Laba' : 'Rugi';
                $status_color = $laba_rugi >= 0 ? 'text-green-600' : 'text-red-600';
            } catch (Exception $e) {
                $pendapatan_penjualan = $pendapatan_lain = $hpp = $biaya_operasional = $total_pendapatan = $total_biaya = $laba_rugi = 0;
                $status = 'Error';
                $status_color = 'text-gray-600';
            }
            ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Pendapatan -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-green-800 mb-2">Pendapatan</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-green-700">Penjualan</span>
                            <span class="font-medium text-green-800"><?php echo Helper::formatMoney($pendapatan_penjualan); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-green-700">Pendapatan Lain-lain</span>
                            <span class="font-medium text-green-800"><?php echo Helper::formatMoney($pendapatan_lain); ?></span>
                        </div>
                        <div class="border-t border-green-200 pt-2">
                            <div class="flex justify-between">
                                <span class="font-semibold text-green-800">Total Pendapatan</span>
                                <span class="font-bold text-green-800"><?php echo Helper::formatMoney($total_pendapatan); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biaya -->
                <div class="bg-red-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-red-800 mb-2">Biaya</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-red-700">HPP</span>
                            <span class="font-medium text-red-800"><?php echo Helper::formatMoney($hpp); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-red-700">Biaya Operasional</span>
                            <span class="font-medium text-red-800"><?php echo Helper::formatMoney($biaya_operasional); ?></span>
                        </div>
                        <div class="border-t border-red-200 pt-2">
                            <div class="flex justify-between">
                                <span class="font-semibold text-red-800">Total Biaya</span>
                                <span class="font-bold text-red-800"><?php echo Helper::formatMoney($total_biaya); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hasil Laba/Rugi -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-800"><?php echo $status; ?> Bersih</span>
                    <span class="text-2xl font-bold <?php echo $status_color; ?>">
                        <?php echo Helper::formatMoney(abs($laba_rugi)); ?>
                    </span>
                </div>
                <p class="text-sm text-gray-600 mt-1">
                    <?php if ($laba_rugi >= 0): ?>
                        Perusahaan mengalami keuntungan sebesar <?php echo Helper::formatMoney($laba_rugi); ?>
                    <?php else: ?>
                        Perusahaan mengalami kerugian sebesar <?php echo Helper::formatMoney(abs($laba_rugi)); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Ringkasan Transaksi Jurnal -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Ringkasan Entri Jurnal</h3>
        </div>
        <div class="p-6">
  <?php
            try {
                $total_jurnal = (int)($db->fetch("SELECT COUNT(*) as total FROM jurnal WHERE $where", $params)['total'] ?? 0);
                $total_debit = (int)($db->fetch("SELECT COALESCE(SUM(debit),0) as total FROM jurnal WHERE $where", $params)['total'] ?? 0);
                $total_kredit = (int)($db->fetch("SELECT COALESCE(SUM(kredit),0) as total FROM jurnal WHERE $where", $params)['total'] ?? 0);
            } catch (Exception $e) {
                $total_jurnal = $total_debit = $total_kredit = 0;
            }
            ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600"><?php echo $total_jurnal; ?></div>
                    <div class="text-sm text-blue-700">Total Entri Jurnal</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600"><?php echo Helper::formatMoney($total_debit); ?></div>
                    <div class="text-sm text-green-700">Total Debit</div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600"><?php echo Helper::formatMoney($total_kredit); ?></div>
                    <div class="text-sm text-purple-700">Total Kredit</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Debug Info -->
    <?php if (DEBUG_MODE): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="font-semibold text-yellow-800 mb-2">Debug Info</h4>
        <div class="text-sm text-yellow-700 space-y-1">
            <div>Pendapatan Penjualan: <?php echo $pendapatan_penjualan; ?></div>
            <div>Pendapatan Lain: <?php echo $pendapatan_lain; ?></div>
            <div>HPP: <?php echo $hpp; ?></div>
            <div>Biaya Operasional: <?php echo $biaya_operasional; ?></div>
            <div>Total Pendapatan: <?php echo $total_pendapatan; ?></div>
            <div>Total Biaya: <?php echo $total_biaya; ?></div>
            <div>Laba/Rugi: <?php echo $laba_rugi; ?></div>
            <div>Where: <?php echo htmlspecialchars($where); ?></div>
            <div>Params: <?php echo htmlspecialchars(json_encode($params)); ?></div>
        </div>
    </div>
    <?php endif; ?>
</div>
