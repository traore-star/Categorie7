<?php
session_start();
require_once('../includes/db_connect.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/unauthorized.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $matricule = trim($_POST['matricule']);

    // Vérifier si l'email ou le matricule existe déjà
    $check_sql = "SELECT * FROM users WHERE email = ? OR matricule = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $email, $matricule);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $_SESSION['message'] = "❌ Cet email ou matricule est déjà utilisé.";
        $_SESSION['message_type'] = "error";
        header("Location: ../admin/ajouter_admin.php");
        exit();
    } else {
        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $role = 'admin';

        $insert_sql = "INSERT INTO users (full_name, email, password, role, matricule, created_at)
                       VALUES (?, ?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sssss", $full_name, $email, $hashed_password, $role, $matricule);

        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "✅ Nouvel administrateur créé avec succès.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "❌ Erreur lors de l’ajout de l’administrateur.";
            $_SESSION['message_type'] = "error";
        }

        $insert_stmt->close();
        header("Location: ../admin/ajouter_admin.php");
        exit();
    }

    $check_stmt->close();
    $insert_stmt->close();
}
