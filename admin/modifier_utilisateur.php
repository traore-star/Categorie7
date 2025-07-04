<?php
session_start();
require_once('../includes/db_connect.php');

// Sécurité : seul l'admin peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

// Vérifie que l'ID est présent
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_utilisateurs.php");
    exit();
}

$id = (int)$_GET['id'];
$message = "";

// Récupération des données actuelles de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: gestion_utilisateurs.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $matricule = trim($_POST['matricule']);
    $role = trim($_POST['role']);

    $stmt = $conn->prepare("UPDATE users SET nom = ?, prenom = ?, email = ?, telephone = ?, matricule = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $nom, $prenom, $email, $telephone, $matricule, $role, $id);

    if ($stmt->execute()) {
        $message = "<span style='color:green;'>✅ Utilisateur mis à jour avec succès.</span>";
    } else {
        $message = "<span style='color:red;'>❌ Échec de la mise à jour.</span>";
    }

    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier utilisateur</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
    label, input, select, textarea {
        color: black;
    }

    input, select, textarea {
        background-color: white;
        border: 1px solid #ccc;
        padding: 6px;
    }

    form {
        /* background-color:rgb(249, 249, 249); */
        padding: 20px;
        border-radius: 8px;
    }
</style>

</head>
<body>
<div class="container">
    <h2>✏️ Modifier l'utilisateur</h2>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <form method="POST" action="">
        <label>Nom :</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>

        <label>Prénom :</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>

        <label>Email :</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Matricule :</label>
        <input type="text" name="matricule" value="<?= htmlspecialchars($user['matricule']) ?>" required>

        <label>Téléphone :</label>
        <input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>" required>

        <label>Rôle :</label>
        <select name="role" required>
            <option value="">-- Sélectionner un rôle --</option>
            <?php
            $roles = ['admin', 'secretaire_drh', 'adminpersonnel', 'scgc', 'chefscgc', 'audit_interne'];
            foreach ($roles as $r):
            ?>
                <option value="<?= $r ?>" <?= ($user['role'] === $r) ? 'selected' : '' ?>><?= $r ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">💾 Enregistrer les modifications</button>
        <a href="gestion_utilisateurs.php" class="btn-retour">⬅️ Retour</a>
    </form>
</div>
</body>
</html>
