<?php
include "includes/db.php";
include "includes/header.php";

function getStatus($date_paiement)
{
    $date_debut = new DateTime($date_paiement);
    $date_fin = clone $date_debut;
    $date_fin->modify('+1 year');

    $today = new DateTime();

    $interval = $today->diff($date_debut);
    $months_passed = ($interval->y * 12) + $interval->m;

    if ($today >= $date_fin) {
        return "<span class='badge bg-danger'>Paiement en retard depuis le " . $date_fin->format('d/m/Y') . "</span>";
    } elseif ($months_passed >= 11) {
        return "<span class='badge bg-warning'>Paiement à renouveler</span>";
    } else {
        return "<span class='badge bg-success'>Valide</span>";
    }
}
?>

<h2>Liste des Adhérents</h2>
<a href="ajouter_adherent.php" class="btn btn-add">Ajouter un Adhérent</a>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Adresse</th>
            <th>Date Adhésion</th>
            <th>Status Paiement</th>
            <th>Livres Empruntés</th>
            <th>Opérations</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT * FROM adherents";
        $res = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($res)) {

            $adh_id = $row['id'];
            $sql_count = "SELECT COUNT(*) as c FROM emprunts WHERE adherent_id=$adh_id AND date_retour_reelle IS NULL";
            $nb_livres = mysqli_fetch_assoc(mysqli_query($conn, $sql_count))['c'];

            echo "<tr>
                <td>{$row['nom']}</td>
                <td>{$row['prenom']}</td>
                <td>{$row['adresse']}</td>
                <td>" . date("d/m/Y", strtotime($row['date_paiement'])) . "</td>
                <td>" . getStatus($row['date_paiement']) . "</td>
                <td style='text-align:center'>$nb_livres</td>
                <td>
                     <a href='modifier_adherent.php?id={$row['id']}' class='btn btn-edit'>Modifier</a>
                     <a href='supprimer_adherent.php?id={$row['id']}' class='btn btn-delete' onclick='return confirm(\"Confirmer la suppression ?\")'>Supprimer</a>
                </td>
            </tr>";
        }
        ?>
    </tbody>
</table>
<?php include "includes/footer.php"; ?>