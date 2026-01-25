<?php
include "includes/db.php";
include "includes/header.php";

$adherent_id = isset($_GET['idAdherent']) ? (int)$_GET['idAdherent'] : 0;

if (isset($_POST['validerDeleteAll']) && isset($_POST['ids'])) {
    $ids_to_delete = $_POST['ids'];
    if (!empty($ids_to_delete)) {
        $ids_safe = array_map('intval', $ids_to_delete);
        $ids_string = implode(",", $ids_safe);

        mysqli_query($conn, "DELETE FROM emprunts WHERE id IN ($ids_string)");

        $redirect_adh = isset($_POST['adherent_hidden']) ? $_POST['adherent_hidden'] : 0;
        echo "<script>window.location.href='supprimer_emprunt_mass.php?idAdherent=$redirect_adh';</script>";
    }
}
?>

<h2>Supprimer des Emprunts (Administration)</h2>

<form method="get" style="border:none; padding:0; margin-bottom:20px;">
    <label>Sélectionner un Adhérent :</label>
    <select name="idAdherent" onchange="window.location.href='supprimer_emprunt_mass.php?idAdherent='+this.value">
        <option value="">-- Choisir --</option>
        <?php
        $sql = "SELECT DISTINCT a.id, a.nom, a.prenom FROM adherents a JOIN emprunts e ON a.id = e.adherent_id";
        $res = mysqli_query($conn, $sql);
        while ($r = mysqli_fetch_assoc($res)) {
            $sel = ($adherent_id == $r['id']) ? 'selected' : '';
            echo "<option value='{$r['id']}' $sel>{$r['nom']} {$r['prenom']}</option>";
        }
        ?>
    </select>
</form>

<?php if ($adherent_id > 0): ?>
    <form method="post">
        <input type="hidden" name="adherent_hidden" value="<?php echo $adherent_id; ?>">

        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" onclick="toggle(this);"> Tout</th>
                    <th>Livre</th>
                    <th>Date Emprunt</th>
                    <th>Date Retour</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_list = "SELECT e.*, o.titre 
                             FROM emprunts e 
                             JOIN exemplaires ex ON e.exemplaire_id = ex.id 
                             JOIN oeuvres o ON ex.oeuvre_id = o.id 
                             WHERE e.adherent_id = $adherent_id";
                $res_list = mysqli_query($conn, $sql_list);

                if (mysqli_num_rows($res_list) == 0) {
                    echo "<tr><td colspan='4'>Aucun historique d'emprunt.</td></tr>";
                }

                while ($row = mysqli_fetch_assoc($res_list)) {
                    $date_e = date("d/m/Y", strtotime($row['date_emprunt']));
                    $retour = $row['date_retour_reelle'] ? date("d/m/Y", strtotime($row['date_retour_reelle'])) : "<span style='color:red'>Non rendu</span>";

                    echo "<tr>
                        <td><input type='checkbox' name='ids[]' value='{$row['id']}'></td>
                        <td>{$row['titre']}</td>
                        <td>{$date_e}</td>
                        <td>{$retour}</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
        <br>
        <input type="submit" name="validerDeleteAll" value="Supprimer la sélection" class="btn btn-delete">
    </form>
<?php endif; ?>

<?php include "includes/footer.php"; ?>