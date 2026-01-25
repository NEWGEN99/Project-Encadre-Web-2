<?php
include "includes/db.php";
include "includes/header.php";

$nb_auteurs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM auteurs"))['c'];
$nb_oeuvres = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM oeuvres"))['c'];
$nb_adherents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM adherents"))['c'];
$nb_emprunts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM emprunts WHERE date_retour_reelle IS NULL"))['c'];
?>

<div style="text-align: center; padding: 50px;">
    <h2>Bienvenue dans le système de gestion de bibliothèque</h2>
    <p>Utilisez le menu ci-dessus pour naviguer.</p>

    <div style="display: flex; justify-content: space-around; margin-top: 50px;">
        <div style="background: #007bff; color: white; padding: 20px; border-radius: 8px; width: 20%;">
            <h3>Auteurs</h3>
            <p style="font-size: 2em;"><?php echo $nb_auteurs; ?></p>
        </div>
        <div style="background: #28a745; color: white; padding: 20px; border-radius: 8px; width: 20%;">
            <h3>Oeuvres</h3>
            <p style="font-size: 2em;"><?php echo $nb_oeuvres; ?></p>
        </div>
        <div style="background: #ffc107; color: black; padding: 20px; border-radius: 8px; width: 20%;">
            <h3>Adhérents</h3>
            <p style="font-size: 2em;"><?php echo $nb_adherents; ?></p>
        </div>
        <div style="background: #dc3545; color: white; padding: 20px; border-radius: 8px; width: 20%;">
            <h3>Emprunts (En cours)</h3>
            <p style="font-size: 2em;"><?php echo $nb_emprunts; ?></p>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>