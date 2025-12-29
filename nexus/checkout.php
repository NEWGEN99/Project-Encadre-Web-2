<?php
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?mode=login&redirect=checkout.php");
    exit;
}
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// معالجة الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $pdo->beginTransaction();

        // 1. حساب الإجمالي والتحقق من المخزون مرة أخيرة
        $total = 0;
        $order_items = [];
        
        foreach ($_SESSION['cart'] as $pid => $qty) {
            $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$pid]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product['stock'] < $qty) {
                throw new Exception("Sorry, some items are out of stock.");
            }

            $total += $product['price'] * $qty;
            $order_items[] = [
                'id' => $pid,
                'qty' => $qty,
                'price' => $product['price']
            ];

            // 2. إنقاص المخزون
            $update = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $update->execute([$qty, $pid]);
        }

        // 3. إنشاء الطلب
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, full_name, address, wilaya, card_number, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'], 
            $_POST['full_name'], 
            $_POST['address'], 
            $_POST['wilaya'], 
            substr($_POST['card'], -4), // حفظ آخر 4 أرقام فقط
            $total
        ]);
        $order_id = $pdo->lastInsertId();

        // 4. إدراج العناصر
        foreach ($order_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['id'], $item['qty'], $item['price']]);
        }

        $pdo->commit();
        $_SESSION['last_order_id'] = $order_id;
        unset($_SESSION['cart']);
        header("Location: order_success.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto flex flex-col md:flex-row gap-8 py-8">
    <div class="flex-1">
        <h1 class="text-2xl font-bold mb-4 text-orange-700">Checkout Securely</h1>
        <?php if(isset($error)): ?><div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $error ?></div><?php endif; ?>

        <form method="POST" class="space-y-4">
            <h3 class="font-bold border-b pb-2">1. Shipping Address</h3>
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="full_name" placeholder="Full Name" required class="border p-2 rounded col-span-2">
                <input type="text" name="address" placeholder="Street Address" required class="border p-2 rounded col-span-2">
                
                <select name="wilaya" required class="border p-2 rounded">
                    <option value="">Select Wilaya...</option>
                    <option value="Algiers">Algiers</option>
                    <option value="Oran">Oran</option>
                    <option value="Blida">Blida</option>
                    </select>
                <input type="text" name="zip" placeholder="Zip Code" class="border p-2 rounded">
            </div>

            <h3 class="font-bold border-b pb-2 mt-6">2. Payment Method</h3>
            <div class="bg-gray-50 p-4 rounded border">
                <div class="mb-3">
                    <label class="font-bold text-sm">Card Number</label>
                    <input type="text" name="card" placeholder="0000 0000 0000 0000" maxlength="19" required class="w-full border p-2 rounded">
                </div>
                <div class="flex gap-4">
                    <input type="text" placeholder="MM/YY" class="w-1/2 border p-2 rounded">
                    <input type="text" placeholder="CVC" maxlength="3" class="w-1/2 border p-2 rounded">
                </div>
            </div>

            <button class="w-full bg-yellow-400 hover:bg-yellow-500 py-3 rounded font-bold shadow-md mt-4">
                Place Your Order
            </button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>