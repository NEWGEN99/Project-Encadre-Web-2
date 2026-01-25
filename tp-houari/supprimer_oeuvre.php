<?php
include "includes/db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM exemplaires WHERE oeuvre_id=$id");
    $count = mysqli_fetch_assoc($check)['total'];

    if ($count > 0) {
        include "includes/header.php";
        echo "<div class='alert' style='margin:20px;'>
                <strong>Erreur :</strong> Impossible de supprimer cette oeuvre car elle possède des exemplaires.<br>
                <a href='oeuvres.php' class='btn btn-info'>Retour</a>
              </div>";
        include "includes/footer.php";
        exit();
    } else {
        mysqli_query($conn, "DELETE FROM oeuvres WHERE id=$id");
        header("Location: oeuvres.php");
    }
}
