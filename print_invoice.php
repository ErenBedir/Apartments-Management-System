<?php
require 'config.php';

$apartment_id = $_GET['id'];
$apartment_query = "SELECT * FROM apartments WHERE id='$apartment_id'";
$apartment_result = mysqli_query($conn, $apartment_query);
$apartment = mysqli_fetch_assoc($apartment_result);

$payments_query = "SELECT * FROM payments WHERE apartment_id='$apartment_id' ORDER BY payment_date DESC";
$payments_result = mysqli_query($conn, $payments_query);

// Start output buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Fatura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .invoice-container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <h1>Fatura</h1>
        <p><strong>Daire Adı:</strong> <?= $apartment['name'] ?></p>
        <p><strong>Kiracı Adı:</strong> <?= $apartment['tenant_name'] ?></p>
        <p><strong>Kiracı Telefon:</strong> <?= $apartment['tenant_phone'] ?></p>
        <p><strong>Elektrik Abonelik No:</strong> <?= $apartment['electricity_subscription'] ?></p>
        <p><strong>Su Abonelik No:</strong> <?= $apartment['water_subscription'] ?></p>
        <p><strong>Doğalgaz Abonelik No:</strong> <?= $apartment['gas_subscription'] ?></p>

        <h2>Geçmiş Ödemeler</h2>
        <table>
            <tr>
                <th>Ödeme Tarihi</th>
                <th>Tutar</th>
                <th>Ödeme Alınma Tarihi</th>
            </tr>
            <?php while ($payment = mysqli_fetch_assoc($payments_result)): ?>
                <tr>
                    <td><?= $payment['payment_date'] ?></td>
                    <td><?= $payment['amount'] ?></td>
                    <td><?= $payment['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

<?php
// Get the content of the buffer and put it into a variable
$html = ob_get_clean();

// Load the dompdf library
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("fatura_$apartment_id.pdf", array("Attachment" => 0));
