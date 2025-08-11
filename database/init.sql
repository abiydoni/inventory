--- FILE: database/init.sql ---
-- Run this SQL to create initial tables (or let app create automatically)
PRAGMA foreign_keys = ON;

-- Hapus tabel lama yang bermasalah (jika ada)
DROP TABLE IF EXISTS profil_perusahaan;
DROP TABLE IF EXISTS pembelian_detail;
DROP TABLE IF EXISTS pembelian;
DROP TABLE IF EXISTS penjualan_detail;
DROP TABLE IF EXISTS penjualan;

-- Tabel COA (Chart of Accounts)
CREATE TABLE IF NOT EXISTS coa (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode TEXT UNIQUE NOT NULL,
    nama TEXT NOT NULL,
    tipe TEXT NOT NULL,       -- aset | liabilitas | ekuitas | pendapatan | beban | hpp
    laporan TEXT NOT NULL,    -- neraca | laba_rugi
    aktif INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed COA umum
INSERT OR IGNORE INTO coa (kode, nama, tipe, laporan) VALUES
('1-100', 'Kas', 'aset', 'neraca'),
('1-110', 'Bank', 'aset', 'neraca'),
('1-120', 'Piutang Usaha', 'aset', 'neraca'),
('1-130', 'Persediaan Barang Dagang', 'aset', 'neraca'),
('1-140', 'Perlengkapan', 'aset', 'neraca'),
('1-150', 'Uang Muka', 'aset', 'neraca'),
('1-200', 'Aset Tetap - Peralatan', 'aset', 'neraca'),
('1-210', 'Aset Tetap - Kendaraan', 'aset', 'neraca'),
('1-220', 'Aset Tetap - Gedung', 'aset', 'neraca'),
('1-300', 'Akumulasi Penyusutan', 'aset', 'neraca'),
('2-100', 'Hutang Usaha', 'liabilitas', 'neraca'),
('2-110', 'Hutang Gaji', 'liabilitas', 'neraca'),
('2-120', 'Hutang Pajak', 'liabilitas', 'neraca'),
('2-130', 'Pendapatan Diterima Dimuka', 'liabilitas', 'neraca'),
('3-100', 'Modal', 'ekuitas', 'neraca'),
('3-110', 'Laba Ditahan', 'ekuitas', 'neraca'),
('3-120', 'Prive', 'ekuitas', 'neraca'),
('4-100', 'Pendapatan Penjualan', 'pendapatan', 'laba_rugi'),
('4-110', 'Pendapatan Jasa', 'pendapatan', 'laba_rugi'),
('5-100', 'Harga Pokok Penjualan', 'hpp', 'laba_rugi'),
('6-100', 'Beban Gaji', 'beban', 'laba_rugi'),
('6-110', 'Beban Sewa', 'beban', 'laba_rugi'),
('6-120', 'Beban Listrik & Air', 'beban', 'laba_rugi'),
('6-130', 'Beban Telepon & Internet', 'beban', 'laba_rugi'),
('6-140', 'Beban Perlengkapan', 'beban', 'laba_rugi'),
('6-150', 'Beban Transportasi/Distribusi', 'beban', 'laba_rugi'),
('6-160', 'Beban Penyusutan', 'beban', 'laba_rugi'),
('6-170', 'Beban Iklan & Promosi', 'beban', 'laba_rugi'),
('6-180', 'Beban Administrasi & Umum', 'beban', 'laba_rugi'),
('6-190', 'Beban Bank & Lainnya', 'beban', 'laba_rugi'),
('7-100', 'Pendapatan Lain-lain', 'pendapatan', 'laba_rugi'),
('8-100', 'Beban Lain-lain', 'beban', 'laba_rugi');

-- Tabel stok
CREATE TABLE IF NOT EXISTS stok (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode TEXT UNIQUE NOT NULL,
    nama TEXT NOT NULL,
    stok INTEGER DEFAULT 0,
    harga INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel customer
CREATE TABLE IF NOT EXISTS customer (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode TEXT UNIQUE NOT NULL,
    nama TEXT NOT NULL,
    alamat TEXT,
    telepon TEXT,
    email TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel supplier
CREATE TABLE IF NOT EXISTS supplier (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kode TEXT UNIQUE NOT NULL,
    nama TEXT NOT NULL,
    alamat TEXT,
    telepon TEXT,
    email TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabel profil perusahaan (dibuat ulang dengan struktur yang benar)
CREATE TABLE profil_perusahaan (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nama TEXT NOT NULL,
    alamat TEXT,
    telepon TEXT,
    email TEXT,
    logo TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default company profile
INSERT INTO profil_perusahaan (id, nama, alamat, telepon, email) 
VALUES (1, 'Nama Perusahaan Anda', 'Alamat Perusahaan', '08123456789', 'info@perusahaan.com');

-- Insert sample data
INSERT OR IGNORE INTO stok (kode, nama, stok, harga) VALUES 
('PRD001', 'Produk Sample 1', 100, 50000),
('PRD002', 'Produk Sample 2', 75, 75000);

INSERT OR IGNORE INTO customer (kode, nama, alamat, telepon, email) VALUES 
('CUST001', 'Customer Sample 1', 'Jl. Contoh No. 1', '08111111111', 'customer1@email.com'),
('CUST002', 'Customer Sample 2', 'Jl. Contoh No. 2', '08222222222', 'customer2@email.com');

INSERT OR IGNORE INTO supplier (kode, nama, alamat, telepon, email) VALUES 
('SUPP001', 'Supplier Sample 1', 'Jl. Supplier No. 1', '08333333333', 'supplier1@email.com'),
('SUPP002', 'Supplier Sample 2', 'Jl. Supplier No. 2', '08444444444', 'supplier2@email.com');

-- Tabel pembelian dengan struktur yang benar
CREATE TABLE pembelian (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  tanggal TEXT,
  supplier_id INTEGER,
  subtotal INTEGER,
  diskon INTEGER DEFAULT 0, -- percent or fixed? we'll use percent
  pajak INTEGER DEFAULT 0, -- percent
  total INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(supplier_id) REFERENCES supplier(id) ON DELETE SET NULL
);

CREATE TABLE pembelian_detail (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  pembelian_id INTEGER,
  stok_id INTEGER,
  qty INTEGER,
  harga INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(pembelian_id) REFERENCES pembelian(id) ON DELETE CASCADE,
  FOREIGN KEY(stok_id) REFERENCES stok(id) ON DELETE SET NULL
);

-- Tabel penjualan dengan struktur yang benar
CREATE TABLE penjualan (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  tanggal TEXT,
  customer_id INTEGER,
  subtotal INTEGER,
  diskon INTEGER DEFAULT 0, -- percent
  pajak INTEGER DEFAULT 0, -- percent
  total INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(customer_id) REFERENCES customer(id) ON DELETE SET NULL
);

CREATE TABLE penjualan_detail (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  penjualan_id INTEGER,
  stok_id INTEGER,
  qty INTEGER,
  harga INTEGER,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE,
  FOREIGN KEY(stok_id) REFERENCES stok(id) ON DELETE SET NULL
);

-- Tabel jurnal (tambahkan coa_id agar entri bisa diklasifikasikan)
CREATE TABLE IF NOT EXISTS jurnal (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  tanggal TEXT,
  akun TEXT,
  debit INTEGER DEFAULT 0,
  kredit INTEGER DEFAULT 0,
  keterangan TEXT,
  coa_id INTEGER NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(coa_id) REFERENCES coa(id) ON DELETE SET NULL
);
