<?php
require_once 'includes/db.php';

// التحقق من الصلاحيات (Admin فقط)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$msg = '';

// ---------------------------------------------------------
// 1. منطق حذف المنتج (مع حذف الصور)
// ---------------------------------------------------------
if (isset($_GET['del_product'])) {
    $id_to_delete = (int)$_GET['del_product'];
    try {
        // جلب بيانات المنتج أولاً لمعرفة مسار الصور
        $stmt = $pdo->prepare("SELECT p.id, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id_to_delete]);
        $prod = $stmt->fetch();

        if ($prod) {
            // حذف المجلد الفيزيائي
            if ($prod['cat_name']) {
                deleteProductFolder($prod['cat_name'], $prod['id']);
            }
            
            // حذف من قاعدة البيانات
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id_to_delete]);
            $msg = "Product and its files deleted successfully.";
        }
    } catch (PDOException $e) {
        $msg = "Error: " . $e->getMessage();
    }
}

// ---------------------------------------------------------
// 2. منطق حذف الفئة
// ---------------------------------------------------------
if (isset($_GET['del_cat'])) {
    $cat_id = (int)$_GET['del_cat'];
    try {
        // المنتجات المرتبطة ستصبح category_id = NULL بسبب القيود في قاعدة البيانات
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$cat_id]);
        $msg = "Category deleted successfully.";
    } catch (PDOException $e) {
        $msg = "Error deleting category: " . $e->getMessage();
    }
}

// ---------------------------------------------------------
// 3. منطق الإضافة والتعديل
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // التعامل مع الفئة (جديدة أو موجودة)
    $cat_name_for_folder = "Uncategorized"; // افتراضي
    
    if (isset($_POST['new_category_name']) && !empty($_POST['new_category_name'])) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        try {
            $stmt->execute([$_POST['new_category_name']]);
            $cat_id = $pdo->lastInsertId();
            $cat_name_for_folder = $_POST['new_category_name'];
        } catch (PDOException $e) {
            $msg = "Category already exists.";
            $cat_id = $_POST['category_id'];
        }
    } else {
        $cat_id = $_POST['category_id'];
        if($cat_id) {
            $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
            $stmt->execute([$cat_id]);
            $cat_name_for_folder = $stmt->fetchColumn();
        }
    }

    // --- إضافة منتج جديد ---
    if (isset($_POST['add_product'])) {
        try {
            // 1. إدخال البيانات أولاً للحصول على ID
            $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, category_id, full_description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['name'], $_POST['price'], $_POST['stock'], $cat_id, $_POST['full_desc']
            ]);
            $new_product_id = $pdo->lastInsertId();

            // 2. رفع الصور الآن بعد الحصول على الـ ID
            $img1 = uploadImage($_FILES['img1'], $cat_name_for_folder, $new_product_id) ?? 'https://via.placeholder.com/300';
            $img2 = uploadImage($_FILES['img2'], $cat_name_for_folder, $new_product_id);
            $img3 = uploadImage($_FILES['img3'], $cat_name_for_folder, $new_product_id);
            $img4 = uploadImage($_FILES['img4'], $cat_name_for_folder, $new_product_id);

            // 3. تحديث مسارات الصور
            $update = $pdo->prepare("UPDATE products SET image_main=?, image_2=?, image_3=?, image_4=? WHERE id=?");
            $update->execute([$img1, $img2, $img3, $img4, $new_product_id]);

            $msg = "Product Added Successfully (ID: $new_product_id)";
        } catch (PDOException $e) {
            $msg = "Error adding product: " . $e->getMessage();
        }
    } 
    // --- تعديل منتج ---
    elseif (isset($_POST['edit_product'])) {
        $pid = $_POST['id'];
        try {
            // رفع صور جديدة إذا وجدت، أو استخدام القديمة
            $img1 = uploadImage($_FILES['img1'], $cat_name_for_folder, $pid) ?? $_POST['existing_img1'];
            $img2 = uploadImage($_FILES['img2'], $cat_name_for_folder, $pid) ?? $_POST['existing_img2'];
            $img3 = uploadImage($_FILES['img3'], $cat_name_for_folder, $pid) ?? $_POST['existing_img3'];
            $img4 = uploadImage($_FILES['img4'], $cat_name_for_folder, $pid) ?? $_POST['existing_img4'];

            $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, stock=?, category_id=?, full_description=?, image_main=?, image_2=?, image_3=?, image_4=? WHERE id=?");
            $stmt->execute([
                $_POST['name'], $_POST['price'], $_POST['stock'], $cat_id, 
                $_POST['full_desc'], $img1, $img2, $img3, $img4, $pid
            ]);
            $msg = "Product Updated Successfully!";
        } catch (PDOException $e) {
            $msg = "Error updating product: " . $e->getMessage();
        }
    }
}

// جلب البيانات
$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
$categories = $pdo->query("SELECT * FROM categories");
$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");

require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto pb-10 px-4">
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
                    <input type="text" name="name" id="p_name" class="w-full border p-2 rounded focus:ring-2 focus:ring-yellow-400 outline-none" required>
                </div>
                
                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Price ($)</label>
                        <input type="number" step="0.01" name="price" id="p_price" class="w-full border p-2 rounded" required>
                    </div>
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Stock</label>
                        <input type="number" name="stock" id="p_stock" class="w-full border p-2 rounded" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                    <div class="flex gap-2">
                        <select name="category_id" id="p_cat" class="w-full border p-2 rounded bg-white" onchange="checkCat(this)">
                            <option value="">Select Category...</option>
                            <?php while($cat = $categories->fetch()): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endwhile; ?>
                            <option value="new" class="font-bold text-blue-600">+ New Category</option>
                        </select>
                        <a id="del_cat_btn" href="#" onclick="return confirm('Delete this category?')" class="hidden bg-red-100 text-red-600 px-3 py-2 rounded hover:bg-red-200" title="Delete Selected Category">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </a>
                    </div>
                    <input type="text" name="new_category_name" id="new_cat_input" placeholder="Enter New Category Name" class="w-full border p-2 rounded mt-2 hidden bg-yellow-50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                    <textarea name="full_desc" id="p_full" placeholder="Product details..." class="w-full border p-2 rounded h-32" required></textarea>
                </div>

                <div class="space-y-3 pt-2 border-t">
                    <label class="block text-sm font-bold">Images</label>
                    <input type="file" name="img1" class="w-full text-xs">
                    <input type="hidden" name="existing_img1" id="ex_img1">
                    
                    <input type="file" name="img2" class="w-full text-xs">
                    <input type="hidden" name="existing_img2" id="ex_img2">
                    
                    <input type="file" name="img3" class="w-full text-xs">
                    <input type="hidden" name="existing_img3" id="ex_img3">
                    
                    <input type="file" name="img4" class="w-full text-xs">
                    <input type="hidden" name="existing_img4" id="ex_img4">
                </div>

                <div class="flex flex-col gap-2 pt-4">
                    <button type="submit" name="add_product" id="btn_add" class="w-full bg-gray-900 text-white py-3 rounded font-bold hover:bg-black transition">Add Product</button>
                    <button type="submit" name="edit_product" id="btn_update" class="w-full bg-green-600 text-white py-3 rounded font-bold hover:bg-green-700 transition hidden">Update Product</button>
                    <button type="button" onclick="resetForm()" class="text-sm text-gray-500 underline py-1">Reset Form</button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="p-4 border-b bg-gray-50 font-bold text-gray-700">Product Inventory</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-100 border-b text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="p-3">Img</th>
                                <th class="p-3">Name</th>
                                <th class="p-3">Price</th>
                                <th class="p-3">Stock</th>
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while($p = $products->fetch()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3"><img src="<?= htmlspecialchars($p['image_main']) ?>" class="w-10 h-10 object-contain border bg-white"></td>
                                <td class="p-3 font-bold"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="p-3">$<?= $p['price'] ?></td>
                                <td class="p-3"><?= $p['stock'] ?></td>
                                <td class="p-3 flex gap-2">
                                    <button onclick='editProduct(<?= json_encode($p) ?>)' class="text-blue-600 font-bold text-xs uppercase">Edit</button>
                                    <span class="text-gray-300">|</span>
                                    <a href="?del_product=<?= $p['id'] ?>" class="text-red-500 font-bold text-xs uppercase" onclick="return confirm('Delete product and its files?')">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="p-4 border-b bg-yellow-50 font-bold text-gray-800 flex items-center gap-2">
                    <i data-lucide="mail" class="w-5 h-5"></i> Customer Messages
                </div>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-100 border-b text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="p-3">Date</th>
                                <th class="p-3">From</th>
                                <th class="p-3">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php while($msg_row = $messages->fetch()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 text-xs text-gray-500 whitespace-nowrap"><?= date('M d, Y', strtotime($msg_row['created_at'])) ?></td>
                                <td class="p-3">
                                    <div class="font-bold"><?= htmlspecialchars($msg_row['name']) ?></div>
                                    <div class="text-xs text-blue-600"><?= htmlspecialchars($msg_row['email']) ?></div>
                                </td>
                                <td class="p-3 text-gray-700"><?= nl2br(htmlspecialchars($msg_row['message'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($messages->rowCount() === 0): ?>
                                <tr><td colspan="3" class="p-4 text-center text-gray-400">No messages yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function checkCat(select) {
    const input = document.getElementById('new_cat_input');
    const delBtn = document.getElementById('del_cat_btn');
    
    if(select.value === 'new') {
        input.classList.remove('hidden');
        input.required = true;
        delBtn.classList.add('hidden');
    } else if (select.value === '') {
        input.classList.add('hidden');
        input.required = false;
        delBtn.classList.add('hidden');
    } else {
        input.classList.add('hidden');
        input.required = false;
        // إظهار زر الحذف وتحديث الرابط بالـ ID
        delBtn.classList.remove('hidden');
        delBtn.href = "?del_cat=" + select.value;
    }
}

function editProduct(p) {
    document.getElementById('p_id').value = p.id;
    document.getElementById('p_name').value = p.name;
    document.getElementById('p_price').value = p.price;
    document.getElementById('p_stock').value = p.stock;
    document.getElementById('p_cat').value = p.category_id;
    // لم يعد هناك short_desc، نستخدم full_desc فقط
    document.getElementById('p_full').value = p.full_description;
    
    document.getElementById('ex_img1').value = p.image_main;
    document.getElementById('ex_img2').value = p.image_2;
    document.getElementById('ex_img3').value = p.image_3;
    document.getElementById('ex_img4').value = p.image_4;
    
    document.getElementById('btn_add').classList.add('hidden');
    document.getElementById('btn_update').classList.remove('hidden');
    
    document.getElementById('formTitle').innerText = "Edit: " + p.name;
    
    // تحديث حالة زر حذف الفئة عند التعديل
    const catSelect = document.getElementById('p_cat');
    checkCat(catSelect);
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('productForm').reset();
    document.getElementById('p_id').value = '';
    document.getElementById('ex_img1').value = '';
    document.getElementById('ex_img2').value = '';
    document.getElementById('ex_img3').value = '';
    document.getElementById('ex_img4').value = '';
    document.getElementById('new_cat_input').classList.add('hidden');
    document.getElementById('del_cat_btn').classList.add('hidden');
    
    document.getElementById('btn_add').classList.remove('hidden');
    document.getElementById('btn_update').classList.add('hidden');
    document.getElementById('formTitle').innerText = "Add New Product";
}
</script>

<?php require_once 'includes/footer.php'; ?>