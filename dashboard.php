<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] != 'superadmin' && $_SESSION['role'] != 'admin') {
    echo "Yetkisiz erişim.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_building'])) {
        $building_name = $_POST['building_name'];
        $query = "INSERT INTO buildings (name) VALUES ('$building_name')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['add_apartment']) && $_SESSION['role'] == 'superadmin') {
        $building_id = $_POST['building_id'];
        $apartment_name = $_POST['apartment_name'];
        $electricity_subscription = $_POST['electricity_subscription'];
        $water_subscription = $_POST['water_subscription'];
        $gas_subscription = $_POST['gas_subscription'];
        $tenant_name = $_POST['tenant_name'];
        $tenant_phone = $_POST['tenant_phone'];
        $query = "INSERT INTO apartments (building_id, name, electricity_subscription, water_subscription, gas_subscription, tenant_name, tenant_phone) VALUES ('$building_id', '$apartment_name', '$electricity_subscription', '$water_subscription', '$gas_subscription', '$tenant_name', '$tenant_phone')";
        mysqli_query($conn, $query);
    } elseif (isset($_POST['select_building'])) {
        $_SESSION['building_id'] = $_POST['building_id'];
    }
}

$buildings = mysqli_query($conn, "SELECT * FROM buildings");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <link rel="icon" href="https://apart.nirvanahost.xyz/favicon.ico" type="image/x-icon" />
    <meta charset="UTF-8">
    <title>Bedir Grup</title>
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .button {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
        }
        .admin-panel {
            background-color: #007bff;
            color: white;
        }
        .logout {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h1>Bedir Grup</h1>
        <form method="post">
            <label for="building">Bina Seç:</label>
            <select id="building" name="building_id">
                <?php while ($building = mysqli_fetch_assoc($buildings)): ?>
                    <option value="<?= $building['id'] ?>"><?= $building['name'] ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="select_building">Seç</button>
        </form>
        <?php if ($_SESSION['role'] == 'superadmin'): ?>
            <a href="admin_panel.php" class="button admin-panel">Yönetim Paneli</a>
        <?php endif; ?>
        <a href="logout.php" class="button logout">Çıkış Yap</a>
        <?php if (isset($_SESSION['building_id'])): ?>
            <?php
            $building_id = $_SESSION['building_id'];
            $apartments = mysqli_query($conn, "SELECT * FROM apartments WHERE building_id='$building_id'");
            ?>
            <div class="table-container">
                <h2>Daireler</h2>
                <table>
                    <tr>
                        <th>Daire Adı</th>
                        <th>Kiracı Adı</th>
                        <th>Ödeme Durumu</th>
                    </tr>
                    <?php while ($apartment = mysqli_fetch_assoc($apartments)): ?>
                        <tr>
                            <td><a href="apartment_details.php?id=<?= $apartment['id'] ?>"><?= $apartment['name'] ?></a></td>
                            <td><?= $apartment['tenant_name'] ?></td>
                            <td>
                                <?php
                                $payment_status = mysqli_query($conn, "SELECT * FROM payments WHERE apartment_id='{$apartment['id']}' AND payment_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
                                if (mysqli_num_rows($payment_status) > 0) {
                                    $payment = mysqli_fetch_assoc($payment_status);
                                    echo "<span class='paid'>Ödendi ({$payment['amount']} TL, {$payment['created_at']})</span>";
                                } else {
                                    echo "<span class='unpaid'>Ödenmedi</span>";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
