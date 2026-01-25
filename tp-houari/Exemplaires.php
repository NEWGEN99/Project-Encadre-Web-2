<?php
include "includes/db.php";
include "includes/header.php";

$oeuvre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($oeuvre_id == 0) header("Location: oeuvres.php");

$info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT titre FROM oeuvres WHERE id=$oeuvre_id"));

if (isset($_POST['add_ex'])) {
    $etat = $_POST['etat'];
    $prix = $_POST['prix'];
    $date_achat = $_POST['date_achat']; // المستخدم يستطيع تعديله أو تركه كما هو

    if (!empty($prix) && !empty($date_achat)) {
        $stmt = $conn->prepare("INSERT INTO exemplaires (oeuvre_id, etat, prix, date_achat) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $oeuvre_id, $etat, $prix, $date_achat);
        $stmt->execute();
    }
}

// حذف
if (isset($_GET['del'])) {
    $eid = (int)$_GET['del'];
    $check = mysqli_query($conn, "SELECT COUNT(*) as c FROM emprunts WHERE exemplaire_id=$eid");
    if (mysqli_fetch_assoc($check)['c'] == 0) {
        mysqli_query($conn, "DELETE FROM exemplaires WHERE id=$eid");
    } else {
        echo "<script>alert('Impossible: Exemplaire lié à un emprunt');</script>";
    }
}
?>

<h2>Exemplaires du livre : <?php echo htmlspecialchars($info['titre']); ?></h2>

<form method="post" style="border:1px solid #ccc; padding:15px; margin-bottom:20px;">
    <label>État :</label>
    <select name="etat">
        <option value="Neuf">Neuf</option>
        <option value="Bon">Bon</option>
        <option value="Usé">Usé</option>
        <option value="Abîmé">Abîmé</option>
    </select>

    <label>Prix :</label>
    <input type="number" step="0.01" name="prix" required placeholder="0.00">

    <label>Date d'achat (Par défaut aujourd'hui) :</label>
    <input type="date" name="date_achat" value="<?php echo date('Y-m-d'); ?>" required>

    <input type="submit" name="add_ex" value="Ajouter" class="btn btn-add">
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>État</th>
            <th>Prix</th>
            <th>Date Achat</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $res = mysqli_query($conn, "SELECT * FROM exemplaires WHERE oeuvre_id=$oeuvre_id");
        while ($r = mysqli_fetch_assoc($res)) {
            $date_fmt = date("d/m/Y", strtotime($r['date_achat']));
            echo "<tr>
                <td>{$r['id']}</td>
                <td>{$r['etat']}</td>
                <td>{$r['prix']}</td>
                <td>{$date_fmt}</td>
                <td>
                    <a href='modifier_exemplaire.php?id={$r['id']}&oid=$oeuvre_id' class='btn btn-edit'>Modifier</a>
                    <a href='exemplaires.php?id=$oeuvre_id&del={$r['id']}' class='btn btn-delete' onclick='return confirm(\"Supprimer ?\")'>Supprimer</a>
                </td>
            </tr>";
        }
        ?>
    </tbody>
</table>
<br><a href="oeuvres.php" class="btn btn-info">Retour aux Oeuvres</a>
<?php include "includes/footer.php"; ?>