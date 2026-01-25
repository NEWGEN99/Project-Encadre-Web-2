<?php
include "includes/db.php";
include "includes/header.php";

$adherent_id = isset($_GET['adherent_id']) ? (int)$_GET['adherent_id'] : 0;

if (isset($_POST['rendre_livre'])) {
    $emprunt_id = (int)$_POST['emprunt_id'];
    $adh_current = (int)$_POST['adherent_id_hidden'];
    $date_retour = date('Y-m-d');

    $sql_update = "UPDATE emprunts SET date_retour_reelle='$date_retour' WHERE id=$emprunt_id";
    mysqli_query($conn, $sql_update);

    echo "<script>window.location.href='rendre.php?adherent_id=$adh_current';</script>";
}
?>

<h2>Rendre un Livre</h2>

<div style="background: #fff; padding: 15px; border: 1px solid #ddd; margin-bottom: 20px;">
    <form method="get" action="rendre.php" style="width: 100%; border:none; padding:0;">
        <label>Sélectionner un Adhérent (ayant des emprunts) :</label>
        <select name="adherent_id" onchange="this.form.submit()">
            <option value="">-- Choisir --</option>
            <?php
            $sql_adh = "SELECT DISTINCT a.id, a.nom, a.prenom 
                        FROM adherents a 
                        JOIN emprunts e ON a.id = e.adherent_id 
                        WHERE e.date_retour_reelle IS NULL";
            $res_adh = mysqli_query($conn, $sql_adh);
            while ($r = mysqli_fetch_assoc($res_adh)) {
                $sel = ($adherent_id == $r['id']) ? 'selected' : '';
                echo "<option value='{$r['id']}' $sel>{$r['nom']} {$r['prenom']}</option>";
            }
            ?>
        </select>
    </form>
</div>

<?php if ($adherent_id > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Livre</th>
                <th>ID Exemplaire</th>
                <th>Date Emprunt</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql_emp = "SELECT e.id as emprunt_id, e.date_emprunt, ex.id as ex_id, o.titre 
                        FROM emprunts e 
                        JOIN exemplaires ex ON e.exemplaire_id = ex.id 
                        JOIN oeuvres o ON ex.oeuvre_id = o.id 
                        WHERE e.adherent_id = $adherent_id AND e.date_retour_reelle IS NULL";
            $res_emp = mysqli_query($conn, $sql_emp);

            if (mysqli_num_rows($res_emp) == 0) {
                echo "<tr><td colspan='4'>Aucun livre à rendre pour cet adhérent.</td></tr>";
            }

            while ($row = mysqli_fetch_assoc($res_emp)) {
                $date_emp_aff = date("d/m/Y", strtotime($row['date_emprunt']));

                echo "<tr>
                    <td>{$row['titre']}</td>
                    <td>{$row['ex_id']}</td>
                    <td>{$date_emp_aff}</td>
                    <td>
                        <form method='post' style='margin:0; padding:0; border:none; width:auto;'>
                            <input type='hidden' name='emprunt_id' value='{$row['emprunt_id']}'>
                            <input type='hidden' name='adherent_id_hidden' value='$adherent_id'>
                            <input type='submit' name='rendre_livre' value='Rendre' class='btn btn-add'>
                        </form>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include "includes/footer.php"; ?>