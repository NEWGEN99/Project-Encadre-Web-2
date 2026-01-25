<?php
include "includes/db.php";
if (isset($_GET['id'])) {
    mysqli_query($conn, "DELETE FROM emprunts WHERE id=" . $_GET['id']);
}
header("Location: emprunts.php");
