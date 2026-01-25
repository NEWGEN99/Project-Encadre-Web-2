<?php
include "includes/db.php";
include "includes/header.php";
?>
<h2>Liste des Auteurs</h2>
<a href="ajouter_auteur.php" class="btn btn-add">Ajouter un Auteur</a>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Nombre d'Oeuvres</th>
            <th>Opérations</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT a.*, COUNT(o.id) as total_oeuvres 
                FROM auteurs a 
                LEFT JOIN oeuvres o ON a.id = o.auteur_id 
                GROUP BY a.id";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['nom']}</td>
                <td>{$row['prenom']}</td>
                <td><span class='badge bg-info'>{$row['total_oeuvres']}</span></td>
                <td>
                    <a href='modifier_auteur.php?id={$row['id']}' class='btn btn-edit'>Modifier</a>
                    ";
            // إذا كان لديه كتب، زر الحذف ينبه المستخدم
            if ($row['total_oeuvres'] > 0) {
                echo "<a href='#' class='btn btn-delete' onclick='alert(\"Impossible: Cet auteur a des oeuvres.\")' style='opacity:0.5'>Supprimer</a>";
            } else {
                echo "<a href='supprimer_auteur.php?id={$row['id']}' class='btn btn-delete' onclick='return confirm(\"Supprimer ?\")'>Supprimer</a>";
            }
            echo "</td></tr>";
        }
        ?>
    </tbody>
</table>
<?php include "includes/footer.php"; ?>