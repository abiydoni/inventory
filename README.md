# 🏢 Sistem Inventori Modern

Aplikasi sistem inventori yang dibangun dengan PHP, SQLite, dan Tailwind CSS dengan design modern dan fitur lengkap.

## ✨ Fitur Utama

### 🎯 Dashboard

- Statistik real-time dengan charts interaktif
- Overview penjualan dan pembelian bulanan
- Peringatan stok rendah
- Transaksi terbaru
- Quick actions untuk akses cepat

### 📦 Manajemen Stok

- CRUD produk dengan validasi lengkap
- Pencarian dan filter produk
- Pagination untuk data besar
- Status stok otomatis (Tersedia/Terbatas/Habis)
- Upload logo produk

### 🛒 Pembelian & Penjualan

- Transaksi pembelian dari supplier
- Transaksi penjualan ke pelanggan
- Perhitungan otomatis (subtotal, diskon, pajak, total)
- Update stok otomatis
- Jurnal otomatis

### 📊 Laporan & Jurnal

- Laporan stok, pembelian, dan penjualan
- Jurnal umum otomatis
- Export data (dalam pengembangan)
- Filter berdasarkan periode

### 🏢 Profil Perusahaan

- Manajemen identitas perusahaan
- Upload logo perusahaan
- Informasi kontak lengkap
- Data bisnis (NPWP, website, dll)

## 🚀 Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: SQLite 3
- **Frontend**: Tailwind CSS, JavaScript ES6+
- **Icons**: Boxicons
- **Charts**: Chart.js
- **Notifications**: SweetAlert2

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- SQLite extension
- Web server (Apache/Nginx)
- Browser modern dengan JavaScript enabled

## 🛠️ Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/sistem-inventori.git
cd sistem-inventori
```

### 2. Setup Web Server

- Letakkan folder di direktori web server (htdocs untuk XAMPP)
- Pastikan folder memiliki permission yang tepat

### 3. Konfigurasi

- Edit file `config.php` sesuai kebutuhan
- Set `DEBUG_MODE = false` untuk production

### 4. Akses Aplikasi

- Buka browser dan akses `http://localhost/inventory`
- Aplikasi akan otomatis membuat database dan tabel

## 🗄️ Struktur Database

### Tabel Utama

- `stok` - Data produk dan inventori
- `pembelian` - Header transaksi pembelian
- `pembelian_detail` - Detail item pembelian
- `penjualan` - Header transaksi penjualan
- `penjualan_detail` - Detail item penjualan
- `jurnal` - Jurnal umum
- `profil_perusahaan` - Data perusahaan

### Relasi

- Foreign key constraints untuk integritas data
- Cascade delete untuk detail transaksi
- Set NULL untuk referensi produk yang dihapus

## 🔒 Keamanan

### Fitur Keamanan

- CSRF protection untuk semua form
- Prepared statements untuk mencegah SQL injection
- Validasi input yang ketat
- Sanitasi output dengan htmlspecialchars
- Session security dengan regenerate ID

### Validasi Input

- Email format validation
- Phone number validation (format Indonesia)
- Date format validation
- Number validation (positive numbers)
- Required field validation

## 🎨 Design System

### Color Palette

- **Primary**: Blue (#3B82F6)
- **Success**: Green (#10B981)
- **Warning**: Yellow (#F59E0B)
- **Error**: Red (#EF4444)
- **Info**: Indigo (#6366F1)

### Components

- Modern card design dengan shadow dan rounded corners
- Gradient backgrounds untuk visual appeal
- Hover effects dan transitions
- Responsive design untuk semua device
- Dark mode support (dalam pengembangan)

## 📱 Responsive Design

- Mobile-first approach
- Sidebar yang dapat di-collapse
- Grid system yang adaptif
- Touch-friendly interface
- Optimized untuk berbagai ukuran layar

## 🔧 Konfigurasi

### Environment Variables

```php
// config.php
define('APP_NAME', 'Sistem Inventori');
define('APP_VERSION', '2.0.0');
define('DEBUG_MODE', true); // Set false untuk production
define('DB_FILE', __DIR__ . '/database/app.db');
```

### Database Configuration

- SQLite database otomatis dibuat
- Tabel otomatis di-generate
- Sample data dapat ditambahkan manual

## 📈 Performance

### Optimizations

- Database indexing untuk query cepat
- Lazy loading untuk data besar
- Pagination untuk mengurangi memory usage
- Caching untuk data statis (dalam pengembangan)

### Monitoring

- Error logging dan handling
- Performance metrics (dalam pengembangan)
- Database query optimization

## 🚀 Deployment

### Production Checklist

- [ ] Set `DEBUG_MODE = false`
- [ ] Konfigurasi HTTPS
- [ ] Backup database regular
- [ ] Monitor error logs
- [ ] Optimize database queries
- [ ] Enable caching

### Backup Strategy

- Backup database SQLite secara regular
- Backup file uploads
- Version control untuk source code
- Documentation updates

## 🤝 Contributing

### Development Guidelines

- Follow PSR-12 coding standards
- Use meaningful commit messages
- Test thoroughly before commit
- Update documentation for new features

### Code Structure

```
inventory/
├── classes/          # PHP Classes
├── database/         # Database files
├── uploads/          # File uploads
├── views/            # View templates
├── config.php        # Configuration
├── index.php         # Main router
└── README.md         # Documentation
```

## 📞 Support

### Getting Help

- Check documentation ini terlebih dahulu
- Review error logs
- Test dengan data sample
- Contact developer untuk issues

### Common Issues

- **Database error**: Check file permissions
- **Upload failed**: Check uploads folder permissions
- **Page not found**: Check .htaccess configuration
- **Session issues**: Check PHP session configuration

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- Tailwind CSS team untuk framework CSS
- Chart.js untuk library charts
- SweetAlert2 untuk notifications
- Boxicons untuk icon set
- SQLite untuk database engine

---

**Version**: 2.0.0  
**Last Updated**: <?php echo date('F Y'); ?>  
**Developer**: Sistem Inventori Team
