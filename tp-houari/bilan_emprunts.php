<?php
include "includes/db.php";
include "includes/header.php";
?>

<h2>Bilan des Emprunts</h2>

<div style="display:flex; gap:20px; margin-bottom:20px;">
    <div style="background:#f8d7da; padding:15px; flex:1; border-radius:5px;">
        <h4>Livres non rendus (Actuels)</h4>
        <?php
        $sql_retard = "SELECT COUNT(*) as c FROM emprunts WHERE date_retour_reelle IS NULL";
        $retard = mysqli_fetch_assoc(mysqli_query($conn, $sql_retard))['c'];
        echo "<h1 style='color:#721c24'>$retard</h1>";
        ?>
    </div>
    <div style="background:#d4edda; padding:15px; flex:1; border-radius:5px;">
        <h4>Total Emprunts (Historique)</h4>
        <?php
        $sql_total = "SELECT COUNT(*) as c FROM emprunts";
        $total = mysqli_fetch_assoc(mysqli_query($conn, $sql_total))['c'];
        echo "<h1 style='color:#155724'>$total</h1>";
        ?>
    </div>
</div>

<h3>Derniers Emprunts (10 Derniers)</h3>
<table>
    <thead>
        <tr>
            <th>Adhérent</th>
            <th>Livre</th>
            <th>Date Emprunt</th>
            <th>État</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT e.*, a.nom, a.prenom, o.titre 
                FROM emprunts e 
                JOIN adherents a ON e.adherent_id = a.id 
                JOIN exemplaires ex ON e.exemplaire_id = ex.id 
                JOIN oeuvres o ON ex.oeuvre_id = o.id 
                ORDER BY e.date_emprunt DESC LIMIT 10";
        $res = mysqli_query($conn, $sql);

        if (mysqli_num_rows($res) == 0) {
            echo "<tr><td colspan='4'>Aucun emprunt enregistré.</td></tr>";
        }

        while ($row = mysqli_fetch_assoc($res)) {
            $date_e = date("d/m/Y", strtotime($row['date_emprunt']));

            if ($row['date_retour_reelle']) {
                $date_r = date("d/m/Y", strtotime($row['date_retour_reelle']));
                $etat = "<span style='color:green'>Rendu le $date_r</span>";
            } else {
                $etat = "<span style='color:red; font-weight:bold;'>Non rendu</span>";
            }

            echo "<tr>
                <td>{$row['nom']} {$row['prenom']}</td>
                <td>{$row['titre']}</td>
                <td>{$date_e}</td>
                <td>{$etat}</td>
            </tr>";
        }
        ?>
    </tbody>
</table>

<?php include "includes/footer.php"; ?>