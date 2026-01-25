<?php
include "includes/db.php";
include "includes/header.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $annee = (int)$_POST['annee'];
    $auteur_id = (int)$_POST['auteur_id'];

    if (strlen($titre) < 2) {
        $error = "Le titre doit contenir au moins 2 caractères.";
    } else {
        $sql = "INSERT INTO oeuvres (titre, date_parution, auteur_id) VALUES ('$titre', '$annee', '$auteur_id')";
        if (mysqli_query($conn, $sql)) {
            header("Location: oeuvres.php");
            exit();
        } else {
            $error = "Erreur SQL: " . mysqli_error($conn);
        }
    }
}
?>

<h2>Ajouter une Nouvelle Oeuvre</h2>
<?php if ($error) echo "<div class='alert'>$error</div>"; ?>

<form method="post">
    <label>Titre du livre :</label>
    <input type="text" name="titre" required minlength="2">

    <label>Année de parution :</label>
    <input type="number" name="annee" required>

    <label>Auteur :</label>
    <select name="auteur_id" required>
        <option value="">-- Choisir un auteur --</option>
        <?php
        $res = mysqli_query($conn, "SELECT * FROM auteurs");
        while ($a = mysqli_fetch_assoc($res)) {
            echo "<option value='{$a['id']}'>{$a['nom']} {$a['prenom']}</option>";
        }
        ?>
    </select>

    <input type="submit" value="Enregistrer" class="btn btn-add">
</form>

<?php include "includes/footer.php"; ?>