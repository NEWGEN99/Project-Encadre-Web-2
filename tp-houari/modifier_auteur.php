<?php
include "includes/db.php";
include "includes/header.php";
$id = $_GET['id'];
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM auteurs WHERE id=$id"));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    mysqli_query($conn, "UPDATE auteurs SET nom='$nom', prenom='$prenom' WHERE id=$id");
    header("Location: auteur.php");
}
?>
<main>
    <form method="post">
        <label>Nom</label><input type="text" name="nom" value="<?php echo $row['nom']; ?>">
        <label>Prénom</label><input type="text" name="prenom" value="<?php echo $row['prenom']; ?>">
        <input type="submit" value="Modifier">
    </form>
</main>
<?php include "includes/footer.php"; ?>