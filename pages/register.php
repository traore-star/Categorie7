<?php
require_once('../includes/db_connect.php');
// ... connexion DB ...

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $role = $_POST['role'];

    if ($role !== 'secretaire') {
        die("Inscription refusée : seul le rôle 'secretaire' est autorisé.");
    }

    // Inscription normale du secrétaire
    $stmt = $conn->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, telephone, matricule, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

    $mot_de_passe_hash = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $stmt->bind_param("sssssss", $_POST['nom'], $_POST['prenom'], $_POST['email'], $mot_de_passe_hash, $_POST['telephone'], $_POST['matricule'], $role);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h3 class="text-center mb-4">📝 Créer un compte</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <form action="../traitements/register_process.php" method="POST">
        <div class="row">
            <div class="mb-3 col-md-6">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" name="nom" id="nom" class="form-control" required>
            </div>
            <div class="mb-3 col-md-6">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" name="prenom" id="prenom" class="form-control" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Adresse Gmail</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="ex: nom@gmail.com" required>
        </div>
        <div class="mb-3">
            <label for="telephone" class="form-label">Numéro de téléphone</label>
            <input type="tel" name="telephone" id="telephone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Profil</label>
            <select name="role" id="role" class="form-select" required>
                <option value="">-- Sélectionnez votre profil --</option>
                <option value="secretaire_drh">Secrétaire DRH</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Créer un compte</button>
    </form>

    <div class="text-center mt-3">
        <a href="login.php" class="btn btn-link">🔐 Déjà inscrit ? Connectez-vous</a>
    </div>
</div>
</body>
</html>
