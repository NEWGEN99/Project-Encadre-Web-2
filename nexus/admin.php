<?php
require_once 'includes/db.php';

// التحقق من الصلاحيات
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$msg = '';

// ---------------------------------------------------------
// 1. منطق الحذف (يجب أن يكون في البداية)
// ---------------------------------------------------------
if (isset($_GET['del'])) {
    $id_to_delete = (int)$_GET['del'];
    try {
        // حذف المنتج (قاعدة البيانات ستحذف المراجعات المرتبطة تلقائياً إذا كانت CASCADE)
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id_to_delete]);
        
        // إعادة تحميل الصفحة لإزالة متغير الحذف من الرابط
        header("Location: admin.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $msg = "Error deleting product: " . $e->getMessage();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $msg = "Product deleted successfully.";
}

// ---------------------------------------------------------
// 2. منطق الإضافة والتعديل
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // إدارة الفئات الجديدة
    if (isset($_POST['new_category_name']) && !empty($_POST['new_category_name'])) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        try {
            $stmt->execute([$_POST['new_category_name']]);
            $cat_id = $pdo->lastInsertId();
        } catch (PDOException $e) {
            $msg = "Category already exists.";
            $cat_id = $_POST['category_id']; // الرجوع للفئة المختارة
        }
    } else {
        $cat_id = $_POST['category_id'];
    }

    // رفع الصور (أو استخدام القديمة في حالة التعديل)
    $img1 = uploadImage($_FILES['img1']) ?? $_POST['existing_img1'] ?? 'https://via.placeholder.com/300';
    $img2 = uploadImage($_FILES['img2']) ?? $_POST['existing_img2'] ?? '';
    $img3 = uploadImage($_FILES['img3']) ?? $_POST['existing_img3'] ?? '';
    $img4 = uploadImage($_FILES['img4']) ?? $_POST['existing_img4'] ?? '';

    // الإضافة
    if (isset($_POST['add_product'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, category_id, short_description, full_description, image_main, image_2, image_3, image_4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'], $_POST['price'], $_POST['stock'], $cat_id, 
                substr($_POST['short_desc'], 0, 255), $_POST['full_desc'], 
                $img1, $img2, $img3, $img4
            ]);
            $msg = "Product Added Successfully!";
        } catch (PDOException $e) {
            $msg = "Error adding product: " . $e->getMessage();
        }
    } 
    // التعديل
    elseif (isset($_POST['edit_product'])) {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, stock=?, category_id=?, short_description=?, full_description=?, image_main=?, image_2=?, image_3=?, image_4=? WHERE id=?");
            $stmt->execute([
                $_POST['name'], $_POST['price'], $_POST['stock'], $cat_id, 
                substr($_POST['short_desc'], 0, 255), $_POST['full_desc'], 
                $img1, $img2, $img3, $img4, $_POST['id']
            ]);
            $msg = "Product Updated Successfully!";
        } catch (PDOException $e) {
            $msg = "Error updating product: " . $e->getMessage();
        }
    }
}

// جلب البيانات للعرض
$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$categories = $pdo->query("SELECT * FROM categories");

require_once 'includes/header.php';
?>

<div class="max-w-6xl mx-auto pb-10">
    <h1 class="text-3xl font-bold mb-6 mt-4">Admin Dashboard</h1>
    
    <?php if($msg): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded shadow-sm">
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 h-fit sticky top-24">
            <h2 id="formTitle" class="text-xl font-bold mb-4 text-gray-800 border-b pb-2">Add New Product</h2>
            
            <form id="productForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" id="p_id">
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Product Name</label>
                    <input type="text" name="name" id="p_name" placeholder="e.g. RTX 4090" class="w-full border p-2 rounded focus:ring-2 focus:ring-yellow-400 outline-none transition" required>
                </div>
                
                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Price ($)</label>
                        <input type="number" step="0.01" name="price" id="p_price" class="w-full border p-2 rounded focus:ring-2 focus:ring-yellow-400 outline-none" required>
                    </div>
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Stock</label>
                        <input type="number" name="stock" id="p_stock" class="w-full border p-2 rounded focus:ring-2 focus:ring-yellow-400 outline-none" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                    <select name="category_id" id="p_cat" class="w-full border p-2 rounded bg-white" onchange="checkCat(this)">
                        <option value="">Select Category...</option>
                        <?php while($cat = $categories->fetch()): ?>
                            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                        <?php endwhile; ?>
                        <option value="new" class="font-bold text-blue-600">+ Add New Category</option>
                    </select>
                    <input type="text" name="new_category_name" id="new_cat_input" placeholder="Enter New Category Name" class="w-full border p-2 rounded mt-2 hidden bg-yellow-50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Short Description</label>
                    <textarea name="short_desc" id="p_short" maxlength="255" placeholder="Brief summary for cards..." class="w-full border p-2 rounded h-20 focus:ring-2 focus:ring-yellow-400 outline-none" required></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Full Description</label>
                    <textarea name="full_desc" id="p_full" placeholder="Detailed specs and features..." class="w-full border p-2 rounded h-32 focus:ring-2 focus:ring-yellow-400 outline-none" required></textarea>
                </div>

                <div class="space-y-3 pt-2 border-t">
                    <label class="block text-sm font-bold">Product Images</label>
                    
                    <div class="text-xs text-gray-500 mb-1">Main Image (Required)</div>
                    <input type="file" name="img1" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                    <input type="hidden" name="existing_img1" id="ex_img1">

                    <div class="text-xs text-gray-500 mb-1 mt-2">Gallery Images (Optional)</div>
                    <input type="file" name="img2" class="w-full text-sm mb-1">
                    <input type="hidden" name="existing_img2" id="ex_img2">
                    
                    <input type="file" name="img3" class="w-full text-sm mb-1">
                    <input type="hidden" name="existing_img3" id="ex_img3">
                    
                    <input type="file" name="img4" class="w-full text-sm">
                    <input type="hidden" name="existing_img4" id="ex_img4">
                </div>

                <div class="flex flex-col gap-2 pt-4">
                    <button type="submit" name="add_product" id="btn_add" class="w-full bg-gray-900 text-white py-3 rounded font-bold hover:bg-black transition">Add Product</button>
                    <button type="submit" name="edit_product" id="btn_update" class="w-full bg-green-600 text-white py-3 rounded font-bold hover:bg-green-700 transition hidden">Update Product</button>
                    
                    <button type="button" onclick="resetForm()" class="text-sm text-gray-500 hover:text-red-500 underline py-1">
                        Clear / Reset Form
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
            <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                <h2 class="font-bold text-gray-700">Product Inventory</h2>
                <span class="text-xs bg-gray-200 px-2 py-1 rounded">Total: <?= $products->rowCount() ?></span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 border-b text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="p-4">Product</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Stock</th>
                            <th class="p-4">Category</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while($p = $products->fetch()): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded bg-gray-100 border flex-shrink-0">
                                        <img src="<?= htmlspecialchars($p['image_main']) ?>" class="w-full h-full object-contain">
                                    </div>
                                    <span class="font-bold text-gray-900 line-clamp-1"><?= htmlspecialchars($p['name']) ?></span>
                                </div>
                            </td>
                            <td class="p-4 font-mono">$<?= number_format($p['price'], 2) ?></td>
                            <td class="p-4">
                                <?php if($p['stock'] < 5): ?>
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-bold">Low: <?= $p['stock'] ?></span>
                                <?php else: ?>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-bold"><?= $p['stock'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-gray-500"><?= htmlspecialchars($p['cat_name'] ?? 'Uncategorized') ?></td>
                            <td class="p-4">
                                <div class="flex justify-center gap-3">
                                    <button onclick='editProduct(<?= json_encode($p) ?>)' class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-wide">
                                        Edit
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <a href="?del=<?= $p['id'] ?>" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-wide" onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.')">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($products->rowCount() === 0): ?>
                <div class="p-10 text-center text-gray-500">No products found. Add your first product!</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// دالة لإظهار حقل كتابة فئة جديدة
function checkCat(select) {
    const input = document.getElementById('new_cat_input');
    if(select.value === 'new') {
        input.classList.remove('hidden');
        input.required = true;
    } else {
        input.classList.add('hidden');
        input.required = false;
    }
}

// دالة تفعيل وضع التعديل
function editProduct(p) {
    // 1. تعبئة الحقول النصية
    document.getElementById('p_id').value = p.id;
    document.getElementById('p_name').value = p.name;
    document.getElementById('p_price').value = p.price;
    document.getElementById('p_stock').value = p.stock;
    document.getElementById('p_cat').value = p.category_id;
    document.getElementById('p_short').value = p.short_description;
    document.getElementById('p_full').value = p.full_description;
    
    // 2. تعبئة الصور القديمة (مخفية) للحفاظ عليها إذا لم يرفع جديد
    document.getElementById('ex_img1').value = p.image_main;
    document.getElementById('ex_img2').value = p.image_2;
    document.getElementById('ex_img3').value = p.image_3;
    document.getElementById('ex_img4').value = p.image_4;
    
    // 3. تبديل الأزرار
    document.getElementById('btn_add').classList.add('hidden');
    document.getElementById('btn_update').classList.remove('hidden');
    
    // 4. تغيير العنوان لإشعار المستخدم
    const title = document.getElementById('formTitle');
    title.innerText = "Edit Product: " + p.name;
    title.classList.add('text-blue-600');
    
    // 5. الصعود لأعلى النموذج
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// دالة تصفير النموذج
function resetForm() {
    // تحديد النموذج بواسطة ID لضمان عدم إفراغ شريط البحث في الهيدر
    const form = document.getElementById('productForm');
    form.reset();
    
    // تصفير الحقول المخفية يدوياً لأن reset لا يؤثر عليها
    document.getElementById('p_id').value = '';
    document.getElementById('ex_img1').value = '';
    document.getElementById('ex_img2').value = '';
    document.getElementById('ex_img3').value = '';
    document.getElementById('ex_img4').value = '';
    
    // إعادة إخفاء حقل الفئة الجديدة
    document.getElementById('new_cat_input').classList.add('hidden');
    
    // إعادة الأزرار لوضع الإضافة
    document.getElementById('btn_add').classList.remove('hidden');
    document.getElementById('btn_update').classList.add('hidden');
    
    // إعادة العنوان للوضع الطبيعي
    const title = document.getElementById('formTitle');
    title.innerText = "Add New Product";
    title.classList.remove('text-blue-600');
}
</script>

<?php require_once 'includes/footer.php'; ?>