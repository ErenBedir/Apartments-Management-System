<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$apartment_id = $_GET['id'];
$apartment_query = "SELECT * FROM apartments WHERE id='$apartment_id'";
$apartment_result = mysqli_query($conn, $apartment_query);
$apartment = mysqli_fetch_assoc($apartment_result);

$payments_query = "SELECT * FROM payments WHERE apartment_id='$apartment_id' ORDER BY payment_date DESC";
$payments_result = mysqli_query($conn, $payments_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_payment']) && $_SESSION['role'] == 'superadmin') {
        $payment_id = $_POST['payment_id'];
        $delete_payment_query = "DELETE FROM payments WHERE id='$payment_id'";
        mysqli_query($conn, $delete_payment_query);
        header("Location: apartment_details.php?id=$apartment_id");
        exit();
    } elseif (isset($_POST['update_apartment']) && $_SESSION['role'] == 'superadmin') {
        $electricity_subscription = $_POST['electricity_subscription'];
        $water_subscription = $_POST['water_subscription'];
        $gas_subscription = $_POST['gas_subscription'];
        $tenant_name = $_POST['tenant_name'];
        $tenant_phone = $_POST['tenant_phone'];
        $update_apartment_query = "UPDATE apartments SET electricity_subscription='$electricity_subscription', water_subscription='$water_subscription', gas_subscription='$gas_subscription', tenant_name='$tenant_name', tenant_phone='$tenant_phone' WHERE id='$apartment_id'";
        mysqli_query($conn, $update_apartment_query);
        header("Location: apartment_details.php?id=$apartment_id");
        exit();
    } elseif (isset($_POST['update_tenant']) && $_SESSION['role'] == 'admin') {
        $tenant_name = $_POST['tenant_name'];
        $tenant_phone = $_POST['tenant_phone'];
        $update_tenant_query = "UPDATE apartments SET tenant_name='$tenant_name', tenant_phone='$tenant_phone' WHERE id='$apartment_id'";
        mysqli_query($conn, $update_tenant_query);
        header("Location: apartment_details.php?id=$apartment_id");
        exit();
    } elseif (isset($_POST['add_payment']) && $_SESSION['role'] == 'admin') {
        $amount = $_POST['amount'];
        $payment_date = $_POST['payment_date'];
        $insert_payment_query = "INSERT INTO payments (apartment_id, amount, payment_date) VALUES ('$apartment_id', '$amount', '$payment_date')";
        mysqli_query($conn, $insert_payment_query);
        header("Location: apartment_details.php?id=$apartment_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Daire Detayları</title>
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .nav-buttons a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }
        .nav-buttons a:hover {
            background-color: #0056b3;
        }
        .center-button {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .center-button a {
            padding: 10px 50px;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }
        .center-button a:hover {
            background-color: #0056b3;
        }
        h1 {
            color: #333; /* H1 rengi */
            font-size: 2em; /* H1 font boyutu */
        }
        .table-container {
            margin-top: 20px;
        }
        .invoice-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }
        .invoice-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Daire Detayları: <?= $apartment['name'] ?></h1>
        <p><strong>Kiracı Adı:</strong> <?= $apartment['tenant_name'] ?></p>
        <p><strong>Kiracı Telefon:</strong> <?= $apartment['tenant_phone'] ?></p>
        <p><strong>Elektrik Abonelik No:</strong> <?= $apartment['electricity_subscription'] ?></p>
        <p><strong>Su Abonelik No:</strong> <?= $apartment['water_subscription'] ?></p>
        <p><strong>Doğalgaz Abonelik No:</strong> <?= $apartment['gas_subscription'] ?></p>

        <div class="table-container">
            <h2>Geçmiş Ödemeler</h2>
            <table>
                <tr>
                    <th>Ödeme Tarihi</th>
                    <th>Tutar</th>
                    <th>Ödeme Alınma Tarihi</th>
                    <?php if ($_SESSION['role'] == 'superadmin'): ?>
                        <th>İşlem</th>
                    <?php endif; ?>
                </tr>
                <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                    <tr>
                        <td><?= $payment['payment_date'] ?></td>
                        <td><?= $payment['amount'] ?></td>
                        <td><?= $payment['created_at'] ?></td>
                        <?php if ($_SESSION['role'] == 'superadmin'): ?>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                    <button type="submit" name="delete_payment">Sil</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <a href="print_invoice.php?id=<?= $apartment_id ?>" class="invoice-button" target="_blank">Fatura Yazdır</a>

        <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin'): ?>
            <h2>Kira Ödemesi Girişi</h2>
            <form method="post">
                <label for="amount">Tutar:</label>
                <input type="number" id="amount" name="amount" required>
                <label for="payment_date">Ödeme Tarihi:</label>
                <input type="date" id="payment_date" name="payment_date" required>
                <button type="submit" name="add_payment">Ödeme Ekle</button>
            </form>
        <?php endif; ?>

        <div class="nav-buttons">
            <a href="apartment_details.php?id=<?= $apartment_id - 1 ?>">← Önceki</a>
            <a href="apartment_details.php?id=<?= $apartment_id + 1 ?>">Sonraki →</a>
        </div>

        <?php if ($_SESSION['role'] == 'superadmin'): ?>
            <h2>Daire Bilgilerini Güncelle</h2>
            <form method="post">
                <label for="electricity_subscription">Elektrik Abonelik No:</label>
                <input type="text" id="electricity_subscription" name="electricity_subscription" value="<?= $apartment['electricity_subscription'] ?>" required>
                <label for="water_subscription">Su Abonelik No:</label>
                <input type="text" id="water_subscription" name="water_subscription" value="<?= $apartment['water_subscription'] ?>" required>
                <label for="gas_subscription">Doğalgaz Abonelik No:</label>
                <input type="text" id="gas_subscription" name="gas_subscription" value="<?= $apartment['gas_subscription'] ?>" required>
                <label for="tenant_name">Kiracı Adı:</label>
                <input type="text" id="tenant_name" name="tenant_name" value="<?= $apartment['tenant_name'] ?>" required>
                <label for="tenant_phone">Kiracı Telefon No:</label>
                <input type="text" id="tenant_phone" name="tenant_phone" value="<?= $apartment['tenant_phone'] ?>" required>
                <button type="submit" name="update_apartment">Güncelle</button>
            </form>
        <?php elseif ($_SESSION['role'] == 'admin'): ?>
            <h2>Kiracı Bilgilerini Güncelle</h2>
            <form method="post">
                <label for="tenant_name">Kiracı Adı:</label>
                <input type="text" id="tenant_name" name="tenant_name" value="<?= $apartment['tenant_name'] ?>" required>
                <label for="tenant_phone">Kiracı Telefon No:</label>
                <input type="text" id="tenant_phone" name="tenant_phone" value="<?= $apartment['tenant_phone'] ?>" required>
                <button type="submit" name="update_tenant">Güncelle</button>
            </form>
        <?php endif; ?>

        <div class="center-button">
            <a href="dashboard.php" class="button">Geri Dön</a>
        </div>
    </div>
</body>
</html>
