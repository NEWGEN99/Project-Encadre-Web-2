<?php 
require_once 'includes/db.php';
require_once 'includes/header.php';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['email'], $_POST['message']]);
    $success = true;
}
?>

<div class="max-w-xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6 text-center">Contact Us</h1>
    
    <?php if($success): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded mb-6 text-center">
            Message sent! Admin will see it in the dashboard.
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-8 rounded shadow-md space-y-4">
        <div>
            <label class="block font-bold mb-1">Name</label>
            <input type="text" name="name" required class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block font-bold mb-1">Email</label>
            <input type="email" name="email" required class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block font-bold mb-1">Message</label>
            <textarea name="message" rows="4" required class="w-full border p-2 rounded"></textarea>
        </div>
        <button type="submit" class="w-full bg-yellow-400 font-bold py-2 rounded hover:bg-yellow-500">Send Message</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>