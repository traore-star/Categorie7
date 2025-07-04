<?php
session_start();
require_once('../includes/db_connect.php');

// Affichage d’un message (erreur ou succès)
$message = $_SESSION['error'] ?? $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (!empty($email) && !empty($mot_de_passe)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Vérifier si le mot de passe est correct
            if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                // Vérifier si le compte est actif
                if (isset($user['actif']) && $user['actif'] == 0) {
                    $_SESSION['error'] = "❌ Ce compte est désactivé. Veuillez contacter l’administrateur.";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['prenom'] = $user['prenom'];
                    $_SESSION['role'] = $user['role'];

                    // Redirection selon le rôle
                    switch ($user['role']) {
                        case 'admin':
                            header("Location: ../dashboards/admin_dashboard.php");
                            break;
                        case 'secretaire_drh':
                            header("Location: ../drh/secretaire_saisie.php");
                            break;
                        case 'adminpersonnel':
                            header("Location: ../drh/adminpersonnel_dashboard.php");
                            break;
                        case 'scgc':
                            header("Location: ../SCGC/scgc_dashboard.php");
                            break;
                        case 'chefscgc':
                            header("Location: ../SCGC/chefscgc_dashboard.php");
                            break;
                        case 'audit_interne':
                            header("Location: ../audit_interne/audit_interne_dashboard.php");
                            break;
                        default:
                            $_SESSION['error'] = "⚠️ Rôle inconnu.";
                            header("Location: login.php");
                            exit();
                    }
                    exit();
                }
            } else {
                $_SESSION['error'] = "❌ Mot de passe incorrect.";
            }
        } else {
            $_SESSION['error'] = "❌ Aucun utilisateur trouvé avec cet email.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "❌ Veuillez remplir tous les champs.";
    }

    header("Location: login.php");
    exit();
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 500px;">
    <h3 class="text-center mb-4">🔐 Connexion</h3>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="mb-3">
            <label for="email" class="form-label">Email Gmail</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="votre.email@gmail.com" required>
        </div>

        <div class="mb-3">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success w-100">Se connecter</button>
    </form>

    <div class="text-center mt-3">
        <a href="register.php" class="btn btn-link">Créer un compte (Secrétaire uniquement)</a>
    </div>
</div>
</body>
</html>
