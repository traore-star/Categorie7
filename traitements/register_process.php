<?php
session_start();
require_once('../includes/db_connect.php');

// Récupération des données du formulaire
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';
$role = $_POST['role'] ?? '';

// Vérifie que tous les champs sont remplis
if (empty($nom) || empty($prenom) || empty($email) || empty($telephone) || empty($mot_de_passe) || empty($role)) {
    header("Location: ../pages/register.php?error=Veuillez remplir tous les champs");
    exit();
}

// Vérifie si l'email est déjà utilisé
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: ../pages/register.php?error=Email déjà utilisé");
    exit();
}
$stmt->close();

// Hachage du mot de passe
$mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

// Insertion dans la base de données
$stmt = $conn->prepare("INSERT INTO users (nom, prenom, email, telephone, mot_de_passe, role) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $nom, $prenom, $email, $telephone, $mot_de_passe_hash, $role);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: ../pages/register.php?success=Compte créé avec succès");
    exit();
} else {
    $stmt->close();
    header("Location: ../pages/register.php?error=Erreur lors de l'inscription");
    exit();
}

