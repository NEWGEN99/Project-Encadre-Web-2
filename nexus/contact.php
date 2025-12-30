<?php 
require_once 'includes/db.php';
require_once 'includes/header.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $msg = trim($_POST['message']);

    if($name && $email && $msg) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $msg]);
            $success = true;
        } catch (PDOException $e) {
            $error = "Failed to send message.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<div class="max-w-xl mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Contact Us</h1>
    
    <?php if($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 text-center">
            Message sent successfully! We will get back to you soon.
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 text-center">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-8 rounded shadow-md space-y-4">
        <div>
            <label class="block font-bold mb-1">Name</label>
            <input type="text" name="name" required class="w-full border p-2 rounded focus:ring-2 focus:ring-yellow-400 outline-none">
        </div>
        <div>
            <label class="block font-bold mb-1">Email</label>
            <input type="email" name="email" required class="w-full border p-2 rounded focus:ring-2 focus:ring-yellow-400 outline-none">
        </div>
        <div>
            <label class="block font-bold mb-1">Message</label>
            <textarea name="message" rows="4" required class="w-full border p-2 rounded focus:ring-2 focus:ring-yellow-400 outline-none"></textarea>
        </div>
        <button type="submit" class="w-full bg-yellow-400 font-bold py-2 rounded hover:bg-yellow-500 transition">Send Message</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>