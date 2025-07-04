<?php
session_start();
require_once('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!empty($email) && !empty($mot_de_passe) && !empty($role)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['role'] = $user['role'];

                // Redirection selon le rôle
                switch ($role) {
                    case 'secretaire_drh':
                        header("Location: ../drh/secretaire_saisie.php");
                        break;
                    case 'administration_traitement':
                        header("Location: ../dashboards/administration_dashboard.php");
                        break;
                    case 'scgc':
                        header("Location: ../SCGC/scgc_dashboard.php");
                        break;
                    case 'chef_scgc':
                        header("Location:  ../chef_scgc/chefscgc_dashboard.php");
                        break;
                    case 'ai':
                        header("Location: ../dashboards/auditinterne_dashboard.php");
                        break;
                    default:
                        header("Location: ../pages/unauthorized.php");
                }
                exit();
            } else {
                header("Location: ../pages/login.php?error=Mot de passe incorrect");
                exit();
            }
        } else {
            header("Location: ../pages/login.php?error=Email ou rôle invalide");
            exit();
        }
    } else {
        header("Location: ../pages/login.php?error=Tous les champs sont obligatoires");
        exit();
    }
} else {
    header("Location: ../pages/login.php");
    exit();
}
