<?php
require_once 'includes/db.php';

if (!isset($_SESSION['last_order_id'])) {
    header("Location: index.php");
    exit;
}

$oid = $_SESSION['last_order_id'];

// جلب تفاصيل الطلب
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$oid]);
$order = $stmt->fetch();

$items = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE order_id = ?");
$items->execute([$oid]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?= $oid ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-100 py-10">

<div class="max-w-2xl mx-auto bg-white p-10 rounded shadow-lg">
    <div class="flex justify-between items-center border-b pb-6 mb-6">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">NEXUS <span class="text-yellow-500">COMPONENTS</span></h1>
            <p class="text-gray-500 text-sm">123 Tech Street, Blida, Algeria</p>
        </div>
        <div class="text-right">
            <h2 class="text-xl font-bold text-gray-700">RECEIPT</h2>
            <p class="text-sm">Order #: <?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></p>
            <p class="text-sm">Date: <?= date('M d, Y', strtotime($order['order_date'])) ?></p>
        </div>
    </div>

    <div class="mb-8">
        <h3 class="font-bold text-gray-700 mb-2">Bill To:</h3>
        <p><?= htmlspecialchars($order['full_name']) ?></p>
        <p><?= htmlspecialchars($order['address']) ?></p>
        <p><?= htmlspecialchars($order['wilaya']) ?></p>
    </div>

    <table class="w-full mb-8">
        <thead class="bg-gray-50 border-y">
            <tr class="text-left text-sm text-gray-600">
                <th class="py-3">Item</th>
                <th class="py-3 text-center">Qty</th>
                <th class="py-3 text-right">Price</th>
                <th class="py-3 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="text-sm">
            <?php while($item = $items->fetch()): ?>
            <tr class="border-b">
                <td class="py-3"><?= $item['name'] ?></td>
                <td class="py-3 text-center"><?= $item['quantity'] ?></td>
                <td class="py-3 text-right">$<?= number_format($item['price_at_purchase'], 2) ?></td>
                <td class="py-3 text-right font-bold">$<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="flex justify-end mb-8">
        <div class="w-1/2">
            <div class="flex justify-between py-2 border-t border-black font-bold text-lg">
                <span>Total Amount:</span>
                <span>$<?= number_format($order['total_amount'], 2) ?></span>
            </div>
            <div class="text-xs text-gray-500 text-right mt-1">Paid via Card ending in **** <?= $order['card_number'] ?></div>
        </div>
    </div>

    <div class="text-center text-gray-400 text-xs border-t pt-4">
        <p>Thank you for shopping at Nexus Components!</p>
        <p>For support, email support@nexus.com</p>
    </div>

    <div class="mt-8 text-center space-x-4 no-print">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Print Receipt</button>
        <a href="index.php" class="text-blue-600 hover:underline">Continue Shopping</a>
    </div>
</div>

</body>
</html>