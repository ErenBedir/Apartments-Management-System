<?php
// Hata raporlamayı etkinleştir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "";
$password = "";
$dbname = "";

// Veritabanı bağlantısı
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Bağlantı hatası: " . mysqli_connect_error());
}

// Süperadmin kullanıcısını kontrol et ve yoksa ekle
// Buraları kafanıza göre ekleyin. setup kurmadığım için gereksinim duymadım.

$check_superadmin_query = "SELECT * FROM users WHERE username='superadmin'";
$result = mysqli_query($conn, $check_superadmin_query);

if (mysqli_num_rows($result) == 0) {
    $superadmin_password = md5('superadmin_password');
    $insert_superadmin_query = "INSERT INTO users (username, password, role) VALUES ('superadmin', '$superadmin_password', 'superadmin')";
    mysqli_query($conn, $insert_superadmin_query);
}
?>
