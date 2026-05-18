<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}
include '_conf.php';
$connexion = mysqli_connect($serveur, $user, $mdp, $nomBDD);
if (!$connexion) {
    die('Erreur de connexion : ' . mysqli_connect_error());
}
$login = mysqli_real_escape_string($connexion, $_SESSION['login']);

$sql = "SELECT cr.*, a.nom, a.prenom
FROM compterendu cr
JOIN adherent a ON cr.login = a.login
JOIN classe c ON a.idClasse = c.id
JOIN adherent p ON c.idProf = p.id
WHERE p.login = '$login'";

$result = mysqli_query($connexion, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Comptes Rendus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Comptes Rendus</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
            </ul>
            <span class="navbar-text text-white me-3">Connecté : <?= $login ?></span>
            <a class="btn btn-outline-light" href="logout.php">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Les Comptes Rendus</h2>
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class='card my-3'>
                <div class='card-header'>
                    <strong> <?= htmlspecialchars($row['nom']) ?> <?= htmlspecialchars($row['prenom']) ?>  | <?= htmlspecialchars($row['date']) ?></strong>
                </div>
                <div class='card-body'>
                    <p><?= htmlspecialchars($row['descriptif']) ?></p>
					<p><?= htmlspecialchars($row['note']) ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Aucun compte rendu trouvé.</p>
    <?php endif; ?>

    
    <div class="text-center my-4">
        <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>