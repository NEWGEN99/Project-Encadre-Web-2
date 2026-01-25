<?php
include "includes/db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql_check = "SELECT COUNT(*) as c FROM emprunts WHERE adherent_id=$id AND date_retour_reelle IS NULL";
    $count = mysqli_fetch_assoc(mysqli_query($conn, $sql_check))['c'];

    if ($count > 0) {
        include "includes/header.php";
        echo "<div class='alert' style='margin:20px;'>
                <strong>Impossible :</strong> Cet adhérent a encore $count livre(s) non rendu(s).<br>
                <a href='adherent.php' class='btn btn-info'>Retour</a>
              </div>";
        include "includes/footer.php";
        exit();
    }

    mysqli_query($conn, "DELETE FROM adherents WHERE id=$id");
}
header("Location: adherent.php");
exit();
