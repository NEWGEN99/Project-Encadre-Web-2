<?php
include "includes/db.php";
include "includes/header.php";

$id = (int)$_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM oeuvres WHERE id=$id");
$oeuvre = mysqli_fetch_assoc($query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $annee = (int)$_POST['annee'];
    $auteur_id = (int)$_POST['auteur_id'];

    $sql = "UPDATE oeuvres SET titre='$titre', date_parution='$annee', auteur_id='$auteur_id' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        header("Location: oeuvres.php");
        exit();
    }
}
?>

<h2>Modifier l'Oeuvre</h2>
<form method="post">
    <label>Titre</label>
    <input type="text" name="titre" value="<?php echo htmlspecialchars($oeuvre['titre']); ?>" required>

    <label>Année de parution</label>
    <input type="number" name="annee" value="<?php echo $oeuvre['date_parution']; ?>" required>

    <label>Auteur</label>
    <select name="auteur_id">
        <?php
        $auteurs = mysqli_query($conn, "SELECT * FROM auteurs");
        while ($a = mysqli_fetch_assoc($auteurs)) {
            $selected = ($a['id'] == $oeuvre['auteur_id']) ? "selected" : "";
            echo "<option value='{$a['id']}' $selected>{$a['nom']} {$a['prenom']}</option>";
        }
        ?>
    </select>
    <input type="submit" value="Mettre à jour" class="btn btn-edit">
</form>

<?php include "includes/footer.php"; ?>