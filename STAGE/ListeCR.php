<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit();
}

include '_conf.php';
$connexion = mysqli_connect($serveur, $user, $mdp, $nomBDD);

if (!$connexion) {
    die('Erreur de connexion : ' . mysqli_connect_error());
}

$login = $_SESSION['login'];
$date_default = date('Y-m-d');
$date_selected = $date_default;
$descriptif = "";
$message = "";

// Recherche du compte rendu à la date sélectionnée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_selected = mysqli_real_escape_string($connexion, $_POST['date']);

    $sql = "SELECT * FROM compterendu WHERE login = '$login' AND date = '$date_selected'";
    $result = mysqli_query($connexion, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $descriptif = $row['descriptif'];
		$note= $row['note'];
    } else {
        $message = "Aucun compte rendu trouvé pour cette date.";
        $descriptif = "";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Afficher Compte Rendu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 pt-5" style="max-width: 600px;">
    <h2 class="mb-4 text-center">Rechercher un Compte Rendu</h2>

    <form method="POST" action="" class="card p-4 shadow-sm mb-4">
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date"
                   value="<?php echo htmlspecialchars($date_selected); ?>" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </div>
    </form>

    <?php if (!empty($message)): ?>
        <div class="alert alert-warning text-center"><?php echo htmlspecialchars($message); ?></div>
    <?php elseif (!empty($descriptif)): ?>
        <div class="card shadow-sm p-3 mt-3">
            <h4 class="mb-3 text-primary">Compte Rendu du <?php echo htmlspecialchars($date_selected); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($descriptif)); ?></p>
			<?php echo "Note du compte rendu :" ?>
			<p><?php echo(htmlspecialchars($note)); ?></p>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-secondary">← Retour à l'accueil</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>