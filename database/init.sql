--- FILE: database/init.sql ---
-- Run this SQL to create initial tables (or let app create automatically)
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS stok (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  kode TEXT UNIQUE,
  nama TEXT,
  stok INTEGER DEFAULT 0,
  harga INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS pembelian (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  tanggal TEXT,
  supplier TEXT,
  subtotal INTEGER,
  diskon INTEGER DEFAULT 0, -- percent or fixed? we'll use percent
  pajak INTEGER DEFAULT 0, -- percent
  total INTEGER
);

CREATE TABLE IF NOT EXISTS pembelian_detail (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  pembelian_id INTEGER,
  stok_id INTEGER,
  qty INTEGER,
  harga INTEGER,
  FOREIGN KEY(pembelian_id) REFERENCES pembelian(id) ON DELETE CASCADE,
  FOREIGN KEY(stok_id) REFERENCES stok(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS penjualan (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  tanggal TEXT,
  pelanggan TEXT,
  subtotal INTEGER,
  diskon INTEGER DEFAULT 0, -- percent
  pajak INTEGER DEFAULT 0, -- percent
  total INTEGER
);

CREATE TABLE IF NOT EXISTS penjualan_detail (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  penjualan_id INTEGER,
  stok_id INTEGER,
  qty INTEGER,
  harga INTEGER,
  FOREIGN KEY(penjualan_id) REFERENCES penjualan(id) ON DELETE CASCADE,
  FOREIGN KEY(stok_id) REFERENCES stok(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS jurnal (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  tanggal TEXT,
  akun TEXT,
  debit INTEGER DEFAULT 0,
  kredit INTEGER DEFAULT 0,
  keterangan TEXT
);
