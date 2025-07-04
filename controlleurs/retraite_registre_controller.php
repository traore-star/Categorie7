<?php
// Empêche l'accès direct sans formulaire
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Accès non autorisé. Veuillez soumettre le formulaire.";
    exit;
}

// Connexion à la base de données
$host = 'localhost';
$dbname = 'categorie7_db';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Récupération sécurisée des données
$nom       = isset($_POST['nom_retraite']) ? $_POST['nom_retraite'] : '';
$matricule = isset($_POST['matricule']) ? $_POST['matricule'] : '';
$adresse   = isset($_POST['adresse']) ? $_POST['adresse'] : '';
$telephone = isset($_POST['telephone']) ? $_POST['telephone'] : '';
$email     = isset($_POST['email']) ? $_POST['email'] : '';

$conjoint  = isset($_POST['conjoint']) ? $_POST['conjoint'] : '';
$enfants   = isset($_POST['enfants']) ? $_POST['enfants'] : '';
$tuteur    = isset($_POST['tuteur']) ? $_POST['tuteur'] : '';

// Téléversement des fichiers
$dossierCible = "../documents/";
$cartePath = $dossierCible . basename($_FILES["carte_identite"]["name"]);
$viePath   = $dossierCible . basename($_FILES["certificat_vie"]["name"]);
$lienPath  = $dossierCible . basename($_FILES["justificatif_lien"]["name"]);

move_uploaded_file($_FILES["carte_identite"]["tmp_name"], $cartePath);
move_uploaded_file($_FILES["certificat_vie"]["tmp_name"], $viePath);
move_uploaded_file($_FILES["justificatif_lien"]["tmp_name"], $lienPath);

// Insertion dans la base de données
$sql = "INSERT INTO retraites (nom, matricule, adresse, telephone, email, conjoint, enfants, tuteur, carte_identite, certificat_vie, justificatif_lien)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssss", $nom, $matricule, $adresse, $telephone, $email, $conjoint, $enfants, $tuteur, $cartePath, $viePath, $lienPath);

if ($stmt->execute()) {
    echo "Demande enregistrée avec succès.";
} else {
    echo "Erreur : " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
