<?php
include "includes/db.php";
include "includes/header.php";

$adherent_id = isset($_GET['adherent_id']) ? (int)$_GET['adherent_id'] : 0;
$msg = "";

if (isset($_POST['save_emprunt'])) {
    $adh_id = (int)$_POST['adh_id'];
    $ex_id = (int)$_POST['exemplaire_id'];
    $date_emprunt = $_POST['date_emprunt'];

    $sql_check = "SELECT COUNT(*) as c FROM emprunts WHERE adherent_id=$adh_id AND date_retour_reelle IS NULL";
    $cnt = mysqli_fetch_assoc(mysqli_query($conn, $sql_check))['c'];

    if ($cnt >= 5) {
        $msg = "<div class='alert'>Cet adhérent a atteint la limite de 5 livres.</div>";
    } else {
        $sql_ins = "INSERT INTO emprunts (exemplaire_id, adherent_id, date_emprunt) VALUES ($ex_id, $adh_id, '$date_emprunt')";
        if (mysqli_query($conn, $sql_ins)) {
            $msg = "<div class='alert' style='background:#d4edda; color:green'>Emprunt ajouté avec succès.</div>";
        } else {
            $msg = "<div class='alert'>Erreur: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<h2>Nouvel Emprunt</h2>
<?php echo $msg; ?>

<div style="background:#f8f9fa; padding:15px; border:1px solid #ddd; margin-bottom:20px;">
    <form method="get">
        <label>1. Sélectionner un Adhérent :</label>
        <select name="adherent_id" onchange="this.form.submit()">
            <option value="">-- Choisir --</option>
            <?php
            $res = mysqli_query($conn, "SELECT * FROM adherents");
            while ($a = mysqli_fetch_assoc($res)) {
                $sel = ($adherent_id == $a['id']) ? 'selected' : '';
                echo "<option value='{$a['id']}' $sel>{$a['nom']} {$a['prenom']}</option>";
            }
            ?>
        </select>
    </form>
</div>

<?php if ($adherent_id): ?>
    <div style="background:#fff; padding:15px; border:1px solid #ddd;">
        <h3>2. Choisir un Livre (Exemplaire disponible)</h3>
        <form method="post">
            <input type="hidden" name="adh_id" value="<?php echo $adherent_id; ?>">

            <label>Livre disponible :</label>
            <select name="exemplaire_id" required>
                <option value="">-- Sélectionner un titre --</option>
                <?php
                $sql_dispo = "SELECT e.id, o.titre, e.etat 
                          FROM exemplaires e 
                          JOIN oeuvres o ON e.oeuvre_id = o.id
                          WHERE e.id NOT IN (SELECT exemplaire_id FROM emprunts WHERE date_retour_reelle IS NULL)
                          ORDER BY o.titre";
                $res_dispo = mysqli_query($conn, $sql_dispo);
                while ($row = mysqli_fetch_assoc($res_dispo)) {
                    echo "<option value='{$row['id']}'>{$row['titre']} (ID: {$row['id']} - {$row['etat']})</option>";
                }
                ?>
            </select>

            <label>Date Emprunt :</label>
            <input type="date" name="date_emprunt" value="<?php echo date('Y-m-d'); ?>" required>

            <input type="submit" name="save_emprunt" value="Valider l'Emprunt" class="btn btn-add">
        </form>
    </div>
<?php endif; ?>

<?php include "includes/footer.php"; ?>