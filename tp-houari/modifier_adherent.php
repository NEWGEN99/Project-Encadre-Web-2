<?php
include "includes/db.php";
include "includes/header.php";

$id = $_GET['id'];
$sql = "SELECT * FROM adherents WHERE id=$id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];

    $update = "UPDATE adherents SET nom='$nom', prenom='$prenom' WHERE id=$id";
    if (mysqli_query($conn, $update)) {
        header("Location: adherent.php");
        exit();
    }
}
?>
<main>
    <h2>Modifier l'Adhérent</h2>
    <form method="post">
        <label>Nom</label><input type="text" name="nom" value="<?php echo $row['nom']; ?>" required>
        <label>Prénom</label><input type="text" name="prenom" value="<?php echo $row['prenom']; ?>" required>
        <input type="submit" value="Modifier">
    </form>
</main>
<?php include "includes/footer.php"; ?>