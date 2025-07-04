<?php
session_start();
require_once('../includes/db_connect.php');

// Sécurité d'accès
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretaire_drh') {
    header("Location: ../pages/login.php");
    exit();
}

// Vérification ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit("Identifiant de l'agent invalide.");
}

$id = (int)$_GET['id'];
$message = "";

// Récupérer l'agent
$stmt = $conn->prepare("SELECT * FROM agents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$agent = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$agent) {
    exit("Agent introuvable.");
}

// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Champs texte
    $nom = trim($_POST['nom']);
    $matricule = trim($_POST['matricule']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $type_logement = trim($_POST['type_logement']);
    $adresse = trim($_POST['adresse']);
    $type_agent = $_POST['type_agent'];
    $ayant_droit = ($type_agent === 'decede') ? trim($_POST['ayant_droit']) : null;

    // Fichiers
    $upload_dir = "../uploads/";
    $piece_identite = $agent['piece_identite'];
    $certificat_vie = $agent['certificat_vie'];
    $justificatif_logement = $agent['justificatif_logement'];

    if (!empty($_FILES['piece_identite']['name'])) {
        $piece_identite = uniqid() . "_" . basename($_FILES['piece_identite']['name']);
        move_uploaded_file($_FILES['piece_identite']['tmp_name'], $upload_dir . $piece_identite);
    }

    if (!empty($_FILES['certificat_vie']['name'])) {
        $certificat_vie = uniqid() . "_" . basename($_FILES['certificat_vie']['name']);
        move_uploaded_file($_FILES['certificat_vie']['tmp_name'], $upload_dir . $certificat_vie);
    }

    if (!empty($_FILES['justificatif_logement']['name'])) {
        $justificatif_logement = uniqid() . "_" . basename($_FILES['justificatif_logement']['name']);
        move_uploaded_file($_FILES['justificatif_logement']['tmp_name'], $upload_dir . $justificatif_logement);
    }

    // Mise à jour base
    $stmt = $conn->prepare("
        UPDATE agents SET 
            nom = ?, matricule = ?, email = ?, telephone = ?, type_logement = ?, adresse = ?, 
            type_agent = ?, ayant_droit = ?, 
            piece_identite = ?, certificat_vie = ?, justificatif_logement = ?
        WHERE id = ?
    ");
    $stmt->bind_param(
        "sssssssssssi",
        $nom, $matricule, $email, $telephone, $type_logement, $adresse,
        $type_agent, $ayant_droit,
        $piece_identite, $certificat_vie, $justificatif_logement,
        $id
    );

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>✅ Mise à jour réussie.</p>";
    } else {
        $message = "<p style='color:red;'>❌ Échec de la mise à jour.</p>";
    }
    $stmt->close();
}
?>

<!-- HTML -->
<link rel="stylesheet" href="../assets/css/secretaire_dashboard.css">

<div class="container">
    <h2>✏️ Modifier l'agent</h2>
    <?= $message ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Nom :</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($agent['nom']) ?>" required>

        <label>Matricule :</label>
        <input type="text" name="matricule" value="<?= htmlspecialchars($agent['matricule']) ?>" required>

        <label>Email :</label>
        <input type="email" name="email" value="<?= htmlspecialchars($agent['email']) ?>" required>

        <label>Téléphone :</label>
        <input type="text" name="telephone" value="<?= htmlspecialchars($agent['telephone']) ?>" required>

        <label>Type de logement :</label>
        <input type="text" name="type_logement" value="<?= htmlspecialchars($agent['type_logement']) ?>" required>

        <label>Adresse :</label>
        <input type="text" name="adresse" value="<?= htmlspecialchars($agent['adresse']) ?>" required>

        <label>Type d’agent :</label>
        <select name="type_agent" id="type_agent" onchange="toggleAyantDroit()" required>
            <option value="">-- Choisir --</option>
            <option value="actif" <?= $agent['type_agent'] === 'actif' ? 'selected' : '' ?>>Actif</option>
            <option value="retraite" <?= $agent['type_agent'] === 'retraite' ? 'selected' : '' ?>>Retraité</option>
            <option value="decede" <?= $agent['type_agent'] === 'decede' ? 'selected' : '' ?>>Décédé</option>
        </select>

        <div id="ayant_droit_section" style="display: <?= $agent['type_agent'] === 'decede' ? 'block' : 'none' ?>;">
            <label>Ayant droit :</label>
            <input type="text" name="ayant_droit" value="<?= htmlspecialchars($agent['ayant_droit'] ?? '') ?>">
        </div>

        <label>Pièce d'identité :</label>
        <input type="file" name="piece_identite">
        <?php if (!empty($agent['piece_identite'])): ?>
            <a href="../uploads/<?= htmlspecialchars($agent['piece_identite']) ?>" target="_blank">📄 Voir le fichier actuel</a>
        <?php endif; ?>

        <label>Certificat de vie :</label>
        <input type="file" name="certificat_vie">
        <?php if (!empty($agent['certificat_vie'])): ?>
            <a href="../uploads/<?= htmlspecialchars($agent['certificat_vie']) ?>" target="_blank">📄 Voir le fichier actuel</a>
        <?php endif; ?>

        <label>Justificatif de logement :</label>
        <input type="file" name="justificatif_logement">
        <?php if (!empty($agent['justificatif_logement'])): ?>
            <a href="../uploads/<?= htmlspecialchars($agent['justificatif_logement']) ?>" target="_blank">📄 Voir le fichier actuel</a>
        <?php endif; ?>

        <br><br>
        <button type="submit">💾 Enregistrer</button>
        <a href="secretaire_saisie.php" class="btn">↩️ Retour</a>
    </form>
</div>

<script>
function toggleAyantDroit() {
    const type = document.getElementById('type_agent').value;
    document.getElementById('ayant_droit_section').style.display = (type === 'decede') ? 'block' : 'none';
}
</script>
