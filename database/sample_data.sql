-- Sample Data untuk Testing Aplikasi Inventori
-- Jalankan file ini setelah aplikasi berjalan untuk menambahkan data sample

-- Sample data untuk tabel stok
INSERT INTO stok (kode, nama, stok, harga) VALUES
('PRD001', 'Laptop Asus ROG', 15, 15000000),
('PRD002', 'Mouse Gaming Logitech', 50, 500000),
('PRD003', 'Keyboard Mechanical', 25, 1200000),
('PRD004', 'Monitor LG 24"', 30, 2500000),
('PRD005', 'Headset Sony WH-1000XM4', 20, 3500000),
('PRD006', 'SSD Samsung 1TB', 40, 1200000),
('PRD007', 'RAM DDR4 16GB', 35, 800000),
('PRD008', 'Power Supply 650W', 18, 1500000),
('PRD009', 'Webcam Logitech C920', 22, 800000),
('PRD010', 'Microphone Blue Yeti', 12, 2500000);

-- Sample data untuk tabel pembelian
INSERT INTO pembelian (tanggal, supplier, subtotal, diskon, pajak, total) VALUES
('2024-01-15', 'PT Supplier Komputer', 50000000, 5, 11, 52250000),
('2024-01-20', 'CV Toko Elektronik', 25000000, 3, 11, 25925000),
('2024-01-25', 'PT Distributor IT', 35000000, 0, 11, 38850000);

-- Sample data untuk tabel pembelian_detail
INSERT INTO pembelian_detail (pembelian_id, stok_id, qty, harga) VALUES
(1, 1, 3, 15000000),
(1, 2, 20, 500000),
(1, 3, 10, 1200000),
(2, 4, 8, 2500000),
(2, 5, 5, 3500000),
(3, 6, 25, 1200000),
(3, 7, 20, 800000);

-- Sample data untuk tabel penjualan
INSERT INTO penjualan (tanggal, pelanggan, subtotal, diskon, pajak, total) VALUES
('2024-01-16', 'PT Client A', 20000000, 2, 11, 21780000),
('2024-01-18', 'CV Client B', 15000000, 0, 11, 16650000),
('2024-01-22', 'PT Client C', 18000000, 5, 11, 18810000);

-- Sample data untuk tabel penjualan_detail
INSERT INTO penjualan_detail (penjualan_id, stok_id, qty, harga) VALUES
(1, 1, 1, 15000000),
(1, 2, 10, 500000),
(2, 3, 5, 1200000),
(2, 4, 6, 2500000),
(3, 5, 4, 3500000),
(3, 6, 8, 1200000);

-- Sample data untuk tabel jurnal
INSERT INTO jurnal (tanggal, akun, debit, kredit, keterangan) VALUES
('2024-01-15', 'Kas', 0, 52250000, 'Pembelian dari PT Supplier Komputer'),
('2024-01-15', 'Persediaan', 52250000, 0, 'Pembelian dari PT Supplier Komputer'),
('2024-01-16', 'Kas', 21780000, 0, 'Penjualan ke PT Client A'),
('2024-01-16', 'Pendapatan Penjualan', 0, 20000000, 'Penjualan ke PT Client A'),
('2024-01-16', 'PPN Keluaran', 0, 1800000, 'PPN Penjualan'),
('2024-01-20', 'Kas', 0, 25925000, 'Pembelian dari CV Toko Elektronik'),
('2024-01-20', 'Persediaan', 25925000, 0, 'Pembelian dari CV Toko Elektronik');

-- Update stok berdasarkan transaksi
UPDATE stok SET stok = stok - 1 WHERE id = 1; -- Laptop terjual 1
UPDATE stok SET stok = stok - 10 WHERE id = 2; -- Mouse terjual 10
UPDATE stok SET stok = stok - 5 WHERE id = 3; -- Keyboard terjual 5
UPDATE stok SET stok = stok - 6 WHERE id = 4; -- Monitor terjual 6
UPDATE stok SET stok = stok - 4 WHERE id = 5; -- Headset terjual 4
UPDATE stok SET stok = stok - 8 WHERE id = 6; -- SSD terjual 8

-- Update stok berdasarkan pembelian
UPDATE stok SET stok = stok + 3 WHERE id = 1; -- Laptop dibeli 3
UPDATE stok SET stok = stok + 20 WHERE id = 2; -- Mouse dibeli 20
UPDATE stok SET stok = stok + 10 WHERE id = 3; -- Keyboard dibeli 10
UPDATE stok SET stok = stok + 8 WHERE id = 4; -- Monitor dibeli 8
UPDATE stok SET stok = stok + 5 WHERE id = 5; -- Headset dibeli 5
UPDATE stok SET stok = stok + 25 WHERE id = 6; -- SSD dibeli 25
UPDATE stok SET stok = stok + 20 WHERE id = 7; -- RAM dibeli 20
