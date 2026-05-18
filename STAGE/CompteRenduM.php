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

$login = mysqli_real_escape_string($connexion, $_SESSION['login']);

// Initialisation des variables
$date_selected = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$descriptif = "";
$message = "";
$note = "NULL"; // Pour la note

// Récupérer le compte rendu pour la date sélectionnée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'load') {
    $check_sql = "SELECT descriptif, note FROM compterendu WHERE login = '$login' AND date = '$date_selected'";
    $check_result = mysqli_query($connexion, $check_sql);

    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $row = mysqli_fetch_assoc($check_result);
        $descriptif = $row['descriptif'];
        $note = is_null($row['note']) ? "NULL" : $row['note'];
    } else {
        $descriptif = "";
        $note = "NULL";
    }
}

// Enregistrer ou mettre à jour le compte rendu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $new_descriptif = mysqli_real_escape_string($connexion, $_POST['descriptif']);
    $note_post = isset($_POST['radio-notes']) ? $_POST['radio-notes'] : "NULL";
    $note_sql = ($note_post === "NULL") ? "NULL" : intval($note_post);

    // Récupérer l'id de l'adhérent à partir du login
    $id_sql = "SELECT id FROM adherent WHERE login = '$login'";
    $res = mysqli_query($connexion, $id_sql);
    $row = mysqli_fetch_assoc($res);
    $id_adherent = $row['id'];

    // Vérifier si un compte rendu existe déjà pour cette date
    $check_sql = "SELECT * FROM compterendu WHERE id = $id_adherent AND date = '$date_selected'";
    $check_result = mysqli_query($connexion, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        // Modifier (UPDATE)
        $update_sql = "UPDATE compterendu 
                       SET descriptif = '$new_descriptif', note = $note_sql 
                       WHERE id = $id_adherent AND date = '$date_selected'";
        if (mysqli_query($connexion, $update_sql)) {
            $message = "Compte rendu mis à jour avec succès.";
        } else {
            $message = "Erreur lors de la mise à jour : " . mysqli_error($connexion);
        }
    } else {
        // Insérer (INSERT)
        $insert_sql = "INSERT INTO compterendu (id, login, date, descriptif, note) 
                       VALUES ($id_adherent, '$login', '$date_selected', '$new_descriptif', $note_sql)";
        if (mysqli_query($connexion, $insert_sql)) {
            $message = "Compte rendu ajouté avec succès.";
        } else {
            $message = "Erreur lors de l'ajout : " . mysqli_error($connexion);
        }
    }

    // Recharger les données
    $descriptif = $new_descriptif;
    $note = $note_post;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Compte Rendu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 pt-5" style="max-width: 600px;">
    <h2 class="mb-4 text-center">Ajouter ou Modifier un Compte Rendu</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="card p-4 shadow-sm">
        <input type="hidden" name="action" id="action" value="load">

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date"
                   value="<?php echo htmlspecialchars($date_selected); ?>" required
                   onchange="document.getElementById('action').value='load'; this.form.submit();">
        </div>

        <div class="mb-3">
            <label for="descriptif" class="form-label">Descriptif</label>
            <textarea class="form-control" id="descriptif" name="descriptif" rows="5" ><?php echo htmlspecialchars($descriptif); ?></textarea>
        </div>
        <div class="note-radios mb-3">
            <?php for ($i = 0; $i <= 5; $i++): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="note<?php echo $i; ?>" name="radio-notes" value="<?php echo $i; ?>" <?php if ($note !== "NULL" && $note == $i) echo "checked"; ?>>
                    <label class="form-check-label" for="note<?php echo $i; ?>"><?php echo $i; ?></label>
                </div>
            <?php endfor; ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" id="noteNULL" name="radio-notes" value="NULL" <?php if ($note === "NULL") echo "checked"; ?>>
                <label class="form-check-label" for="noteNULL">Pas de note</label>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary" onclick="document.getElementById('action').value='save';">Enregistrer</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-secondary">← Retour à l'accueil</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>