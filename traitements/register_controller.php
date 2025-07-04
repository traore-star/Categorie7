<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'categorie7_db';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifie que le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $profil = $_POST['type_utilisateur'];
    $ayant_droit = isset($_POST['ayant_droit']) ? $_POST['ayant_droit'] : null;

    $sql = "INSERT INTO utilisateurs (nom, email, telephone, mot_de_passe, profil, ayant_droit)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nom, $email, $telephone, $mot_de_passe, $profil, $ayant_droit);

    if ($stmt->execute()) {
        header("Location: ../pages/confirmation.php");
        exit;
    } else {
        echo "Erreur : " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Accès interdit.";
}

$conn->close();
?>
