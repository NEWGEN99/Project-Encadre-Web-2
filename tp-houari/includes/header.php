<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Gestion Bibliothèque</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function toggle(source) {
            var checkboxes = document.querySelectorAll('input[name="ids[]"]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>

<body>
    <header>
        <h1>Bibliothèque</h1>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="#">Auteurs & Oeuvres ▼</a>
                    <ul>
                        <li><a href="auteur.php">Gestion des Auteurs</a></li>
                        <li><a href="oeuvres.php">Gestion des Oeuvres</a></li>
                    </ul>
                </li>
                <li><a href="#">Adhérents ▼</a>
                    <ul>
                        <li><a href="adherent.php">Liste des Adhérents</a></li>
                    </ul>
                </li>
                <li><a href="#">Emprunts ▼</a>
                    <ul>
                        <li><a href="emprunts.php">Emprunter</a></li>
                        <li><a href="rendre.php">Rendre</a></li>
                        <li><a href="supprimer_emprunt_mass.php">Suppression en masse</a></li>
                        <li><a href="bilan_emprunts.php">Bilan</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>
    <main>