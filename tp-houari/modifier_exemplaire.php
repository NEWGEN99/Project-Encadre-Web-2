<?php
include "includes/db.php";
include "includes/header.php";

$id = $_GET['id'];
$oid = $_GET['oid'];

$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM exemplaires WHERE id=$id"));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $etat = $_POST['etat'];
    mysqli_query($conn, "UPDATE exemplaires SET etat='$etat' WHERE id=$id");
    header("Location: exemplaires.php?id=$oid");
}
?>
<main>
    <h2>Modifier l'Exemplaire (ID: <?php echo $id; ?>)</h2>
    <form method="post">
        <label>État</label>
        <select name="etat">
            <option value="Neuf" <?php if ($row['etat'] == 'Neuf') echo 'selected'; ?>>Neuf</option>
            <option value="Bon" <?php if ($row['etat'] == 'Bon') echo 'selected'; ?>>Bon</option>
            <option value="Usé" <?php if ($row['etat'] == 'Usé') echo 'selected'; ?>>Usé</option>
            <option value="Abîmé" <?php if ($row['etat'] == 'Abîmé') echo 'selected'; ?>>Abîmé</option>
        </select>
        <input type="submit" value="Mettre à jour">
    </form>
</main>
<?php include "includes/footer.php"; ?>