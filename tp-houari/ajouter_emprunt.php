<?php
include "includes/db.php";
include "includes/header.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oeuvre = $_POST['oeuvre_id'];
    $adherent = $_POST['adherent_id'];
    $date_e = $_POST['date_emprunt'];
    $date_r = $_POST['date_retour'];

    $sql = "INSERT INTO emprunts (oeuvre_id, adherent_id, date_emprunt, date_retour) 
            VALUES ('$oeuvre', '$adherent', '$date_e', '$date_r')";
    mysqli_query($conn, $sql);
    header("Location: emprunts.php");
}
?>
<main>
    <h2>Nouvel Emprunt</h2>
    <form method="post">
        <label>Livre</label>
        <select name="oeuvre_id">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM oeuvres");
            while ($r = mysqli_fetch_assoc($res)) echo "<option value='{$r['id']}'>{$r['titre']}</option>";
            ?>
        </select>

        <label>Adhérent</label>
        <select name="adherent_id">
            <?php
            $res = mysqli_query($conn, "SELECT * FROM adherents");
            while ($r = mysqli_fetch_assoc($res)) echo "<option value='{$r['id']}'>{$r['nom']} {$r['prenom']}</option>";
            ?>
        </select>

        <label>Date Emprunt</label><input type="date" name="date_emprunt" required>
        <label>Date Retour</label><input type="date" name="date_retour">

        <input type="submit" value="Enregistrer">
    </form>
</main>
<?php include "includes/footer.php"; ?>