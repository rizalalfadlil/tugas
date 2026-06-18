# Troubleshoot penginstalan

## Masalah Terkait File System

### 1. Error `permission denied`
coba jalankan kembali perintah sebelumnya dengan menambahkan `sudo` di depannya.

contoh:
|sebelum|sesudah|
|---|---|
|`apt update`|`sudo apt update`|

### 2. Gagal ketika menginstall package
- pastikan perangkat anda terhubung dengan internet
- coba jalankan perintah `apt update` terlebih dahulu, lalu coba install package yang ingin diinstall.

### 3. `File` atau `Folder` tidak ditemukan
- pastikan file yang ingin diakses ada di direktori yang benar **(perlu diingat bahwa nama file dan folder di Linux bersifat case-sensitive)**
- coba jalankan perintah `ls` untuk melihat daftar file yang ada di direktori saat ini

## Masalah Terkait PHP

### 1. Halaman Web Kosong
Terjadi Fatal Error (kesalahan sintaks/kode) pada file PHP Anda, pengaturan bawaan server Linux menyembunyikan pesan error tersebut demi alasan keamanan agar struktur direktori server tidak bocor ke publik.

### 2. Halaman Web Menampilkan Teks Kode Mentah (`<?php...`)
- Server Apache tidak mengenali file PHP Anda sebagai file PHP, sehingga mengirimkan konten mentah (kode sumber) ke browser sebagai teks biasa.

- coba install kembali package yang dibutuhkan
```bash
sudo apt install libapache2-mod-php
```
