<?php
include "includes/db.php";
include "includes/header.php";
?>

<h2>Gestion des Oeuvres</h2>
<a href="ajouter_oeuvre.php" class="btn btn-add">Ajouter une Oeuvre</a>

<table>
    <thead>
        <tr>
            <th>Titre</th>
            <th>Auteur</th>
            <th>Date Parution</th>
            <th>Nbr Total</th>
            <th>Nbr Dispo</th>
            <th>Exemplaires</th>
            <th>Opérations</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT oeuvres.id, oeuvres.titre, oeuvres.date_parution, auteurs.nom, auteurs.prenom 
                FROM oeuvres 
                LEFT JOIN auteurs ON oeuvres.auteur_id = auteurs.id
                ORDER BY oeuvres.id DESC";

        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($oeuvre = mysqli_fetch_assoc($result)) {
                $oid = $oeuvre['id'];

                $res_total = mysqli_query($conn, "SELECT COUNT(*) as c FROM exemplaires WHERE oeuvre_id=$oid");
                $nbr_total = mysqli_fetch_assoc($res_total)['c'] ?? 0;


                $sql_dispo = "SELECT COUNT(*) as c FROM exemplaires 
                              WHERE oeuvre_id=$oid 
                              AND id NOT IN (SELECT exemplaire_id FROM emprunts WHERE date_retour_reelle IS NULL)";
                $res_dispo = mysqli_query($conn, $sql_dispo);
                $nbr_dispo = mysqli_fetch_assoc($res_dispo)['c'] ?? 0;

                $nom_auteur = $oeuvre['nom'] ? htmlspecialchars($oeuvre['nom'] . " " . $oeuvre['prenom']) : "Inconnu";

                echo "<tr>
                        <td>" . htmlspecialchars($oeuvre['titre']) . "</td>
                        <td>" . $nom_auteur . "</td>
                        <td>" . htmlspecialchars($oeuvre['date_parution']) . "</td>
                        <td>$nbr_total</td>
                        <td>$nbr_dispo</td>
                        <td><a href='exemplaires.php?id=$oid' class='btn btn-info'>Gérer</a></td>
                        <td>
                            <a href='modifier_oeuvre.php?id=$oid' class='btn btn-edit'>M</a>
                            <a href='supprimer_oeuvre.php?id=$oid' class='btn btn-delete' onclick='return confirm(\"Supprimer ?\")'>S</a>
                        </td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7' style='text-align:center'>Aucune oeuvre trouvée.</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php include "includes/footer.php"; ?>