<?php
session_start();
require 'config.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Yetkilendirme kontrolü
if ($_SESSION['role'] != 'superadmin' && $_SESSION['role'] != 'admin') {
    echo "Yetkisiz erişim.";
    exit();
}

// Veritabanı bağlantısını kontrol etme
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Post isteklerini kontrol etme ve işlemleri gerçekleştirme
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_building'])) {
        $building_name = $_POST['building_name'];
        $query = "INSERT INTO buildings (name) VALUES ('$building_name')";
        if ($conn->query($query) === TRUE) {
            echo "Yeni bina başarıyla eklendi.";
        } else {
            echo "Hata: " . $query . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['add_apartment']) && $_SESSION['role'] == 'superadmin') {
        $building_id = $_POST['building_id'];
        $apartment_name = $_POST['apartment_name'];
        $electricity_subscription = $_POST['electricity_subscription'];
        $water_subscription = $_POST['water_subscription'];
        $gas_subscription = $_POST['gas_subscription'];
        $tenant_name = $_POST['tenant_name'];
        $tenant_phone = $_POST['tenant_phone'];
        $query = "INSERT INTO apartments (building_id, name, electricity_subscription, water_subscription, gas_subscription, tenant_name, tenant_phone) VALUES ('$building_id', '$apartment_name', '$electricity_subscription', '$water_subscription', '$gas_subscription', '$tenant_name', '$tenant_phone')";
        if ($conn->query($query) === TRUE) {
            echo "Yeni daire başarıyla eklendi.";
        } else {
            echo "Hata: " . $query . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        $query = "DELETE FROM users WHERE id='$user_id'";
        if ($conn->query($query) === TRUE) {
            echo "Kullanıcı başarıyla silindi.";
        } else {
            echo "Hata: " . $query . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['add_admin'])) {
        $admin_username = $_POST['admin_username'];
        $admin_password = md5($_POST['admin_password']); // Güvenlik için şifreleme
        $query = "INSERT INTO users (username, password, role) VALUES ('$admin_username', '$admin_password', 'admin')";
        if ($conn->query($query) === TRUE) {
            echo "Yeni admin kullanıcı başarıyla eklendi.";
        } else {
            echo "Hata: " . $query . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['edit_apartment']) && $_SESSION['role'] == 'superadmin') {
        $apartment_id = $_POST['apartment_id'];
        $apartment_name = $_POST['apartment_name'];
        $electricity_subscription = $_POST['electricity_subscription'];
        $water_subscription = $_POST['water_subscription'];
        $gas_subscription = $_POST['gas_subscription'];
        $tenant_name = $_POST['tenant_name'];
        $tenant_phone = $_POST['tenant_phone'];
        $query = "UPDATE apartments SET name='$apartment_name', electricity_subscription='$electricity_subscription', water_subscription='$water_subscription', gas_subscription='$gas_subscription', tenant_name='$tenant_name', tenant_phone='$tenant_phone' WHERE id='$apartment_id'";
        if ($conn->query($query) === TRUE) {
            echo "Daire başarıyla güncellendi.";
        } else {
            echo "Hata: " . $query . "<br>" . $conn->error;
        }
    }
}

// Binaları getirme
$buildings = mysqli_query($conn, "SELECT * FROM buildings");
$apartments = mysqli_query($conn, "SELECT * FROM apartments");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetim Paneli</title>
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

    <div class="form-container">
        <h1>Yönetim Paneli</h1>
        
        <div class="accordion">
            <!-- Bina Ekle -->
            <div class="accordion-item">
                <button class="accordion-button">BİNA EKLE</button>
                <div class="accordion-content">
                    <form method="post">
                        <label for="building_name">Bina Adı:</label>
                        <input type="text" id="building_name" name="building_name" required>
                        <button type="submit" name="add_building">Ekle</button>
                    </form>
                </div>
            </div>

            <!-- Daire Ekle -->
            <div class="accordion-item">
                <button class="accordion-button">DAİRE EKLE</button>
                <div class="accordion-content">
                    <form method="post">
                        <label for="building">Bina Seç:</label>
                        <select id="building" name="building_id">
                            <?php while ($building = mysqli_fetch_assoc($buildings)): ?>
                                <option value="<?= $building['id'] ?>"><?= $building['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <label for="apartment_name">Daire Adı:</label>
                        <input type="text" id="apartment_name" name="apartment_name" required>
                        <label for="electricity_subscription">Elektrik Abonelik No:</label>
                        <input type="text" id="electricity_subscription" name="electricity_subscription" required>
                        <label for="water_subscription">Su Abonelik No:</label>
                        <input type="text" id="water_subscription" name="water_subscription" required>
                        <label for="gas_subscription">Doğalgaz Abonelik No:</label>
                        <input type="text" id="gas_subscription" name="gas_subscription" required>
                        <label for="tenant_name">Kiracı Adı:</label>
                        <input type="text" id="tenant_name" name="tenant_name" required>
                        <label for="tenant_phone">Kiracı Telefon No:</label>
                        <input type="text" id="tenant_phone" name="tenant_phone" required>
                        <button type="submit" name="add_apartment">Ekle</button>
                    </form>
                </div>
            </div>

            <!-- Daire Düzenle -->
            <div class="accordion-item">
                <button class="accordion-button">DAİRE DÜZENLE</button>
                <div class="accordion-content">
                    <form method="post">
                        <label for="apartment">Daire Seç:</label>
                        <select id="apartment" name="apartment_id">
                            <?php while ($apartment = mysqli_fetch_assoc($apartments)): ?>
                                <option value="<?= $apartment['id'] ?>"><?= $apartment['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                        <label for="apartment_name">Daire Adı:</label>
                        <input type="text" id="apartment_name" name="apartment_name" required>
                        <label for="electricity_subscription">Elektrik Abonelik No:</label>
                        <input type="text" id="electricity_subscription" name="electricity_subscription" required>
                        <label for="water_subscription">Su Abonelik No:</label>
                        <input type="text" id="water_subscription" name="water_subscription" required>
                        <label for="gas_subscription">Doğalgaz Abonelik No:</label>
                        <input type="text" id="gas_subscription" name="gas_subscription" required>
                        <label for="tenant_name">Kiracı Adı:</label>
                        <input type="text" id="tenant_name" name="tenant_name" required>
                        <label for="tenant_phone">Kiracı Telefon No:</label>
                        <input type="text" id="tenant_phone" name="tenant_phone" required>
                        <button type="submit" name="edit_apartment">Güncelle</button>
                    </form>
                </div>
            </div>

            <!-- Kullanıcıları Yönet -->
            <div class="accordion-item">
                <button class="accordion-button">KULLANICILARI YÖNET</button>
                <div class="accordion-content">
                    <!-- Mevcut Kullanıcılar Tablosu -->
                    <h2>Mevcut Kullanıcılar</h2>
                    <table>
                        <tr>
                            <th>Kullanıcı Adı</th>
                            <th>Rol</th>
                            <th>İşlem</th>
                        </tr>
                        <?php
                        $users = mysqli_query($conn, "SELECT * FROM users");
                        while ($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?= $user['username'] ?></td>
                                <td><?= $user['role'] ?></td>
                                <td>
                                    <?php if ($user['role'] != 'superadmin'): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="delete_user">Sil</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>

                    <!-- Admin Ekle -->
                    <h2>Admin Kullanıcı Ekle</h2>
                    <form method="post">
                        <label for="admin_username">Kullanıcı Adı:</label>
                        <input type="text" id="admin_username" name="admin_username" required>
                        <label for="admin_password">Şifre:</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                        <button type="submit" name="add_admin">Ekle</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        const accItems = document.querySelectorAll('.accordion-item');
        accItems.forEach(item => {
            const button = item.querySelector('.accordion-button');
            button.addEventListener('click', () => {
                accItems.forEach(i => {
                    if (i !== item) {
                        i.classList.remove('active');
                        i.querySelector('.accordion-content').style.maxHeight = null;
                    }
                });
                item.classList.toggle('active');
                const content = item.querySelector('.accordion-content');
                if (item.classList.contains('active')) {
                    content.style.maxHeight = content.scrollHeight + 'px';
                } else {
                    content.style.maxHeight = null;
                }
            });
        });
    </script>
</body>
</html>
