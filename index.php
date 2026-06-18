<?php
session_start();
/**
 * Script Demonstrasi LAMP Stack (PHP + MySQL)
 * Versi Interaktif: Dinamis Login Database, Tambah dan Hapus Komentar
 */

// Menangani aksi Putus Koneksi
if (isset($_GET['aksi']) && $_GET['aksi'] === 'keluar') {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Menangani pengiriman form login database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'login') {
    $_SESSION['db_user'] = trim($_POST['db_user']);
    $_SESSION['db_pass'] = $_POST['db_pass'];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 1. Konfigurasi Koneksi Database
$host = "localhost";
$status_koneksi = "";
$data_komentar = [];
$is_connected = false;

mysqli_report(MYSQLI_REPORT_OFF); 

// Mengecek apakah sudah ada data login di sesi
if (isset($_SESSION['db_user'])) {
    $user = $_SESSION['db_user'];
    $pass = $_SESSION['db_pass'];
    
    $conn = new mysqli($host, $user, $pass);

    if ($conn->connect_error) {
        $status_koneksi = "<div class='alert error'><strong>Koneksi Gagal:</strong> " . $conn->connect_error . "</div>";
        session_destroy(); // Hapus sesi jika gagal agar form login muncul lagi
    } else {
        $is_connected = true;
        $status_koneksi = "<div class='alert success'>
            <strong>Koneksi ke Database Berhasil!</strong> Terhubung sebagai <b>".htmlspecialchars($user)."</b>. 
            <a href='?aksi=keluar' style='color:#155724; float:right; font-weight:bold;'>[ Putus Koneksi ]</a>
        </div>";

        // 2. Persiapan Database & Tabel (Dijalankan otomatis)
        $conn->query("CREATE DATABASE IF NOT EXISTS demo_lamp");
        $conn->select_db("demo_lamp");
        
        $query_buat_tabel = "
            CREATE TABLE IF NOT EXISTS komentar (
                id INT AUTO_INCREMENT PRIMARY KEY,
                teks TEXT NOT NULL,
                waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $conn->query($query_buat_tabel);

        // --- PERBAIKAN BUG REDIRECT ---
        // Semua logika manipulasi data dan redirect WAJIB dibungkus dalam blok POST ini
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
            
            // Aksi: Tambah Komentar
            if ($_POST['aksi'] === 'tambah' && !empty(trim($_POST['teks_komentar']))) {
                $teks = trim($_POST['teks_komentar']);
                $stmt = $conn->prepare("INSERT INTO komentar (teks) VALUES (?)");
                $stmt->bind_param("s", $teks);
                $stmt->execute();
                $stmt->close();
            }
            
            // Aksi: Hapus Komentar
            if ($_POST['aksi'] === 'hapus' && isset($_POST['id'])) {
                $id_hapus = (int)$_POST['id'];
                $stmt = $conn->prepare("DELETE FROM komentar WHERE id = ?");
                $stmt->bind_param("i", $id_hapus);
                $stmt->execute();
                $stmt->close();
            }

            // REDIRECT SEKARANG TERKUNCI DI SINI
            // Hanya akan tereksekusi ketika pengguna mengklik "Kirim Data" atau "Hapus"
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        // 3. Mengambil Data dari Database untuk ditampilkan
        $hasil = $conn->query("SELECT * FROM komentar ORDER BY waktu DESC"); 
        
        if ($hasil && $hasil->num_rows > 0) {
            while($baris = $hasil->fetch_assoc()) {
                $data_komentar[] = $baris;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Komentar MySQL</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .form-group {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
        }
        input[type="text"], input[type="password"] {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            color: white;
            transition: background 0.3s;
        }
        .btn-primary { background-color: #3498db; }
        .btn-primary:hover { background-color: #2980b9; }
        .btn-danger { background-color: #e74c3c; padding: 5px 10px; font-size: 14px;}
        .btn-danger:hover { background-color: #c0392b; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Berhasil Terhubung ke Server!</h1>
    
    <?= $status_koneksi ?>

    <?php if (!$is_connected): ?>
        <!-- FORM LOGIN DATABASE -->
        <div style="background: #fdfdfd; border: 1px solid #eee; padding: 20px; border-radius: 5px;">
            <h3>Masuk ke MySQL</h3>
            <p style="color:#666; font-size: 14px;">untuk melanjutkan menghubungkan ke database, silahkan masukkan username dan password anda yang telah dibuat di mysql sebelumnya.</p>
            <form method="POST" action="">
                <input type="hidden" name="aksi" value="login">
                <div style="margin-bottom: 10px;">
                    <label style="display:block; margin-bottom:5px;">Username Database:</label>
                    <input type="text" name="db_user" placeholder="Contoh: root" required style="width: 100%; box-sizing: border-box;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px;">Password Database:</label>
                    <input type="password" name="db_pass" placeholder="Biarkan kosong jika tidak ada password" style="width: 100%; box-sizing: border-box;">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Hubungkan ke Database</button>
            </form>
        </div>

    <?php else: ?>
        <!-- FORM TAMBAH KOMENTAR -->
        <div style="background: #f9fbfd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #e1e8ed;">
            <form method="POST" action="" class="form-group" style="margin: 0;">
                <input type="hidden" name="aksi" value="tambah">
                <input type="text" name="teks_komentar" placeholder="Tulis komentar baru Anda di sini..." required>
                <button type="submit" class="btn btn-primary">Kirim</button>
            </form>
        </div>

        <!-- TABEL DATA KOMENTAR -->
        <?php if (count($data_komentar) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Teks Komentar</th>
                        <th width="150">Waktu</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_komentar as $kom): ?>
                        <tr>
                            <!-- Mencegah XSS (Cross-Site Scripting) dengan htmlspecialchars -->
                            <td><?= htmlspecialchars($kom['teks']) ?></td>
                            <td style="font-size: 0.9em; color: #7f8c8d;">
                                <?= date('d M Y, H:i', strtotime($kom['waktu'])) ?>
                            </td>
                            <td>
                                <!-- FORM HAPUS KOMENTAR (Kecil di setiap baris) -->
                                <form method="POST" action="" style="margin: 0;">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id" value="<?= $kom['id'] ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus komentar ini?');">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                Belum ada komentar. Jadilah yang pertama!
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
<?php
// Tutup koneksi database
if (isset($conn) && !$conn->connect_error) {
    $conn->close();
}
?>