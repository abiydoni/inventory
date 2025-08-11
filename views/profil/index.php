<?php
// Ambil data profil perusahaan
$profil = $db->fetch("SELECT * FROM profil_perusahaan ORDER BY id DESC LIMIT 1");
$csrf_token = Helper::generateCSRF();
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Profil Perusahaan</h1>
            <p class="text-gray-600">Kelola identitas dan informasi perusahaan Anda</p>
        </div>

        <!-- Profile Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header Card -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-8 text-white">
                <div class="flex items-center space-x-6">
                    <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center">
                        <?php if (!empty($profil['logo'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($profil['logo']); ?>" 
                                 alt="Logo" class="w-20 h-20 rounded-full object-cover">
                        <?php else: ?>
                            <i class='bx bx-building text-4xl'></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($profil['nama_perusahaan'] ?? 'Nama Perusahaan'); ?></h2>
                        <p class="text-blue-100 text-lg"><?php echo htmlspecialchars($profil['alamat'] ?? 'Alamat belum diisi'); ?></p>
                    </div>
                    <button onclick="openEditProfile()" 
                            class="bg-white/20 hover:bg-white/30 px-6 py-3 rounded-lg font-semibold transition-all duration-200 backdrop-blur-sm">
                        <i class='bx bx-edit mr-2'></i>Edit Profil
                    </button>
                </div>
            </div>

            <!-- Info Details -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Contact Information -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class='bx bx-phone text-blue-600 mr-3'></i>
                            Informasi Kontak
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class='bx bx-phone text-blue-600'></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Telepon</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($profil['telepon'] ?? '-'); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class='bx bx-envelope text-green-600'></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($profil['email'] ?? '-'); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class='bx bx-globe text-purple-600'></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Website</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($profil['website'] ?? '-'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class='bx bx-briefcase text-blue-600 mr-3'></i>
                            Informasi Bisnis
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class='bx bx-id-card text-orange-600'></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">NPWP</p>
                                    <p class="font-medium"><?php echo htmlspecialchars($profil['npwp'] ?? '-'); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class='bx bx-calendar text-red-600'></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Dibuat</p>
                                    <p class="font-medium"><?php echo Helper::formatDateTime($profil['created_at'] ?? ''); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class='bx bx-time text-indigo-600'></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Terakhir Update</p>
                                    <p class="font-medium"><?php echo Helper::formatDateTime($profil['updated_at'] ?? ''); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class='bx bx-package text-2xl text-blue-600'></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Total Produk</h3>
                <?php $totalProduk = $db->fetch("SELECT COUNT(*) as total FROM stok")['total'] ?? 0; ?>
                <p class="text-3xl font-bold text-blue-600"><?php echo $totalProduk; ?></p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class='bx bx-shopping-bag text-2xl text-green-600'></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Penjualan Bulan Ini</h3>
                <?php 
                $bulanIni = date('Y-m');
                $penjualanBulan = $db->fetch("SELECT SUM(total) as total FROM penjualan WHERE substr(tanggal,1,7)=?", [$bulanIni])['total'] ?? 0;
                ?>
                <p class="text-3xl font-bold text-green-600"><?php echo Helper::formatMoney($penjualanBulan); ?></p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class='bx bx-cart text-2xl text-purple-600'></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Pembelian Bulan Ini</h3>
                <?php 
                $pembelianBulan = $db->fetch("SELECT SUM(total) as total FROM pembelian WHERE substr(tanggal,1,7)=?", [$bulanIni])['total'] ?? 0;
                ?>
                <p class="text-3xl font-bold text-purple-600"><?php echo Helper::formatMoney($pembelianBulan); ?></p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class='bx bx-dollar text-2xl text-orange-600'></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Nilai Persediaan</h3>
                <?php $nilaiPersediaan = $db->fetch("SELECT SUM(stok*harga) as total FROM stok")['total'] ?? 0; ?>
                <p class="text-3xl font-bold text-orange-600"><?php echo Helper::formatMoney($nilaiPersediaan); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<script>
function openEditProfile() {
    Swal.fire({
        title: 'Edit Profil Perusahaan',
        html: `
            <form id="profileForm" class="text-left space-y-4">
                <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan *</label>
                    <input type="text" id="nama_perusahaan" value="<?php echo htmlspecialchars($profil['nama_perusahaan'] ?? ''); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea id="alamat" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($profil['alamat'] ?? ''); ?></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text" id="telepon" value="<?php echo htmlspecialchars($profil['telepon'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($profil['email'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                        <input type="url" id="website" value="<?php echo htmlspecialchars($profil['website'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NPWP</label>
                        <input type="text" id="npwp" value="<?php echo htmlspecialchars($profil['npwp'] ?? ''); ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo Perusahaan</label>
                    <input type="file" id="logo" accept="image/*" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF. Maksimal 5MB</p>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Simpan Perubahan',
        cancelButtonText: 'Batal',
        width: '600px',
        preConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'update_profile');
            formData.append('csrf_token', document.getElementById('csrf_token').value);
            formData.append('nama_perusahaan', document.getElementById('nama_perusahaan').value);
            formData.append('alamat', document.getElementById('alamat').value);
            formData.append('telepon', document.getElementById('telepon').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('website', document.getElementById('website').value);
            formData.append('npwp', document.getElementById('npwp').value);
            
            const logoFile = document.getElementById('logo').files[0];
            if (logoFile) {
                formData.append('logo', logoFile);
            }
            
            return fetch('?page=profil&aksi=api', {
                method: 'POST',
                body: formData
            }).then(r => r.json()).catch(() => {
                Swal.showValidationMessage('Request error');
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value.status === 'success') {
                Swal.fire('Sukses!', result.value.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error!', result.value.message, 'error');
            }
        }
    });
}
</script>

<!-- API Handler -->
<?php
if (isset($_GET['aksi']) && $_GET['aksi'] == 'api') {
    try {
        if (!Helper::validateCSRF($_POST['csrf_token'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            // Validasi input
            $nama_perusahaan = $_POST['nama_perusahaan'] ?? '';
            if (!Helper::validateRequired($nama_perusahaan)) {
                throw new Exception('Nama perusahaan wajib diisi');
            }
            
            $email = $_POST['email'] ?? '';
            if (!empty($email) && !Helper::validateEmail($email)) {
                throw new Exception('Format email tidak valid');
            }
            
            $telepon = $_POST['telepon'] ?? '';
            if (!empty($telepon) && !Helper::validatePhone($telepon)) {
                throw new Exception('Format nomor telepon tidak valid');
            }
            
            // Handle logo upload
            $logo = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $logo = Helper::uploadFile($_FILES['logo'], $uploadDir);
            }
            
            // Update database
            $sql = "UPDATE profil_perusahaan SET 
                    nama_perusahaan = ?, alamat = ?, telepon = ?, email = ?, 
                    website = ?, npwp = ?, updated_at = CURRENT_TIMESTAMP";
            $params = [
                Helper::sanitize($nama_perusahaan),
                Helper::sanitize($_POST['alamat'] ?? ''),
                Helper::sanitize($telepon),
                Helper::sanitize($email),
                Helper::sanitize($_POST['website'] ?? ''),
                Helper::sanitize($_POST['npwp'] ?? '')
            ];
            
            if ($logo) {
                $sql .= ", logo = ?";
                $params[] = $logo;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $profil['id'];
            
            $db->execute($sql, $params);
            
            Helper::jsonResponse(Helper::successResponse('Profil berhasil diperbarui'));
            
        } else {
            throw new Exception('Action tidak valid');
        }
        
    } catch (Exception $e) {
        Helper::jsonResponse(Helper::errorResponse($e->getMessage()));
    }
}
?>
