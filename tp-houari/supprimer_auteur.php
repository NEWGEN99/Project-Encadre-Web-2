<?php
include "includes/db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM oeuvres WHERE auteur_id=$id");
    $data = mysqli_fetch_assoc($check);

    if ($data['total'] > 0) {
        include "includes/header.php";
        echo "<div class='alert' style='background-color:#f8d7da; color:#721c24; margin:20px;'>
                <strong>Erreur :</strong> Impossible de supprimer cet auteur car il possède des oeuvres enregistrées.<br>
                Veuillez d'abord supprimer ses oeuvres. <br><br>
                <a href='auteur.php' class='btn btn-info'>Retour</a>
              </div>";
        include "includes/footer.php";
        exit();
    } else {
        mysqli_query($conn, "DELETE FROM auteurs WHERE id=$id");
        header("Location: auteur.php");
    }
} else {
    header("Location: auteur.php");
}
