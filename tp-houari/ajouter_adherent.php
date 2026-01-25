<?php
include "includes/db.php";
include "includes/header.php";

$error = "";

$nom = "";
$prenom = "";
$adresse = "";
$date_paiement = date('Y-m-d');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $adresse = $_POST['adresse'];
    $date_paiement = $_POST['date_paiement'];

    if (strlen($nom) < 2 || strlen($prenom) < 2 || strlen($adresse) < 2) {
        $error = "Le nom, le prénom et l'adresse doivent contenir au moins 2 caractères.";
    } else {
        $sql = "INSERT INTO adherents (nom, prenom, adresse, date_paiement) 
                VALUES ('$nom', '$prenom', '$adresse', '$date_paiement')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>window.location.href='adherent.php';</script>";
        } else {
            $error = "Erreur SQL: " . mysqli_error($conn);
        }
    }
}
?>

<h2>Ajouter un Adhérent</h2>
<?php if ($error) echo "<div class='alert'>$error</div>"; ?>

<form method="post">
    <label>Nom</label>
    <input type="text" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>

    <label>Prénom</label>
    <input type="text" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required>

    <label>Adresse</label>
    <input type="text" name="adresse" value="<?php echo htmlspecialchars($adresse); ?>" required>

    <label>Date Paiement (Début d'abonnement)</label>
    <input type="date" name="date_paiement" value="<?php echo $date_paiement; ?>" required>

    <input type="submit" value="Ajouter">
</form>

<?php include "includes/footer.php"; ?>