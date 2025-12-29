<?php
require_once 'includes/db.php';

// ------- Cart Logic Merged Here -------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty_to_add = (int)($_POST['qty'] ?? 1);

    if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    // جلب مخزون المنتج للتحقق
    if($pid > 0) {
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$pid]);
        $stock_available = $stmt->fetchColumn();
    }

    if ($action === 'add' && $pid > 0) {
        $current_qty = $_SESSION['cart'][$pid] ?? 0;
        if (($current_qty + $qty_to_add) <= $stock_available) {
            $_SESSION['cart'][$pid] = $current_qty + $qty_to_add;
        } else {
            // يمكن إضافة رسالة خطأ هنا
        }
    } 
    elseif ($action === 'update') {
        // التحقق من أن الكمية المعدلة متوفرة
        if ($qty_to_add <= $stock_available && $qty_to_add > 0) {
            $_SESSION['cart'][$pid] = $qty_to_add;
        }
    }
    elseif ($action === 'remove') {
        unset($_SESSION['cart'][$pid]);
    }
    elseif ($action === 'clear') {
        unset($_SESSION['cart']);
    }

    header("Location: cart.php");
    exit;
}
// --------------------------------------

require_once 'includes/header.php';
?>

<div class="bg-white p-6 max-w-5xl mx-auto shadow-sm flex flex-col md:flex-row gap-8">
    <div class="flex-1">
        <h1 class="text-2xl font-bold mb-2">Shopping Cart</h1>
        <p class="text-right text-sm text-blue-600 mb-2 cursor-pointer">Deselect all items</p>
        <hr class="mb-4">

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="text-center py-10">
                <p class="text-xl mb-4">Your Nexus Cart is empty.</p>
                <a href="shop.php" class="text-blue-600 hover:underline">Shop today's deals</a>
            </div>
        <?php else:
            $ids = implode(',', array_keys($_SESSION['cart']));
            $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
            $total = 0;
            $count = 0;
        ?>
            <?php while ($item = $stmt->fetch(PDO::FETCH_ASSOC)):
                $qty = $_SESSION['cart'][$item['id']];
                $subtotal = $item['price'] * $qty;
                $total += $subtotal;
                $count += $qty;
            ?>
            <div class="flex gap-4 border-b py-6">
                <img src="<?php echo $item['image_main']; ?>" class="w-32 h-32 object-contain bg-gray-50 border">
                <div class="flex-1">
                    <h3 class="font-bold text-lg text-blue-600 hover:underline mb-1">
                        <a href="product.php?id=<?= $item['id'] ?>"><?php echo $item['name']; ?></a>
                    </h3>
                    <div class="text-green-700 text-xs font-bold mb-2">In Stock</div>
                    <div class="flex items-center gap-2 text-sm">
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                            <select name="qty" onchange="this.form.submit()" class="bg-gray-100 border rounded p-1">
                                <?php for($i=1; $i <= min(10, $item['stock']); $i++): ?>
                                    <option value="<?= $i ?>" <?= $i==$qty ? 'selected':'' ?>>Qty: <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </form>

                        <div class="border-l h-4 mx-2"></div>
                        <form method="POST">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                            <button class="text-blue-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="font-bold text-lg text-right w-24">$<?php echo $item['price']; ?></div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
    <div class="w-full md:w-72 h-fit bg-white p-4">
        <div class="text-lg mb-4">
            Subtotal (<?php echo $count; ?> items):
            <span class="font-bold block text-xl">$<?php echo number_format($total, 2); ?></span>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="checkout.php" class="block text-center bg-yellow-400 hover:bg-yellow-500 py-2 rounded shadow text-sm font-bold">
               Proceed to Checkout
            </a>
        <?php else: ?>
            <a href="auth.php?mode=login&redirect=checkout.php" class="block text-center bg-yellow-400 hover:bg-yellow-500 py-2 rounded shadow text-sm font-bold">
               Sign in to Checkout
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>