<?php
include "includes/db.php";
include "includes/header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    mysqli_query($conn, "INSERT INTO auteurs (nom, prenom) VALUES ('$nom', '$prenom')");
    header("Location: auteur.php");
}
?>
<main>
    <h2>Ajouter un Auteur</h2>
    <form method="post">
        <label>Nom</label><input type="text" name="nom" required>
        <label>Prénom</label><input type="text" name="prenom" required>
        <input type="submit" value="Ajouter">
    </form>
</main>
<?php include "includes/footer.php"; ?>