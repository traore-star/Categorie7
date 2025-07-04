<?php
require_once('../includes/db_connect.php');

// Créer le dossier d’uploads s’il n’existe pas
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Vérifie que le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom']);
    $matricule = trim($_POST['matricule']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $ayant_droit = trim($_POST['ayant_droit']);
    $type_logement = trim($_POST['type_logement']);
    $adresse = trim($_POST['adresse']);
    $statut = 'en_attente'; // par défaut
    $date_soumission = date('Y-m-d H:i:s');

    // Gérer les fichiers
    $piece_identite = $_FILES['piece_identite'];
    $justificatif_logement = $_FILES['justificatif_logement'];
    $certificat_vie = $_FILES['certificat_vie'];

    function uploadFichier($fichier, $prefixe) {
        global $uploadDir;
        $nomFichier = $prefixe . '_' . time() . '_' . basename($fichier['name']);
        $cheminComplet = $uploadDir . $nomFichier;
        if (move_uploaded_file($fichier['tmp_name'], $cheminComplet)) {
            return $cheminComplet;
        }
        return null;
    }

    $path_piece_identite = uploadFichier($piece_identite, 'identite');
    $path_justificatif = uploadFichier($justificatif_logement, 'logement');
    $path_certificat = uploadFichier($certificat_vie, 'certificat');

    if (!$path_piece_identite || !$path_justificatif || !$path_certificat) {
        die("❌ Erreur lors du téléchargement des fichiers.");
    }

    // Enregistrement dans la base de données
    $stmt = $conn->prepare("INSERT INTO agents (nom, matricule, email, telephone, ayant_droit, type_logement, adresse, piece_identite, justificatif_logement, certificat_vie, statut, date_soumission)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss", $nom, $matricule, $email, $telephone, $ayant_droit, $type_logement, $adresse, $path_piece_identite, $path_justificatif, $path_certificat, $statut, $date_soumission);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>✅ Informations soumises avec succès à l’administration.</p>";
        echo '<a href="secretaire_saisie.php">🔙 Retour</a>';
    } else {
        echo "<p style='color:red;'>❌ Une erreur s’est produite : " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p>⛔ Méthode invalide.</p>";
}
?>
