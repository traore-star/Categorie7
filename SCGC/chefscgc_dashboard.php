<?php
session_start();
include('../includes/header.php');

$conn = mysqli_connect('localhost', 'root', '', 'categorie7_db');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'chefscgc') {
    header("Location: ../pages/login.php");
    exit();
}

if (!$conn) {
    die("Échec de la connexion : " . mysqli_connect_error()); 
}

// Traitement des actions valider / rejeter
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['agent_id'], $_POST['action'])) {
    $id = (int)$_POST['agent_id'];
    $action = $_POST['action'];

    $stmt = $conn->prepare("SELECT nom, matricule FROM agents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $agent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($agent) {
        if ($action == "valider") {
            $stmt = $conn->prepare("UPDATE agents SET statut_chefscgc='valide_chefscgc', date_validation_chefscgc = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO historique_actions (agents_id, nom, matricule, action, acteur, date) VALUES (?, ?, ?, 'valider', 'Chef SCGC', NOW())");
            $stmt->bind_param("iss", $id, $agent['nom'], $agent['matricule']);
            $stmt->execute();
            $stmt->close();

        } elseif ($action == "rejeter" && !empty(trim($_POST['motif_rejet']))) {
            $motif = trim($_POST['motif_rejet']);

            $stmt = $conn->prepare("UPDATE agents SET statut_chefscgc = 'rejeter_par_chefscgc', motif_rejet_chefscgc = ?, date_rejet_chefscgc = NOW() WHERE id = ?");
            $stmt->bind_param("si", $motif, $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO historique_actions (agents_id, nom, matricule, action, motif_rejet, acteur, date) VALUES (?, ?, ?, 'rejeter', ?, 'Chef SCGC', NOW())");
            $stmt->bind_param("isss", $id, $agent['nom'], $agent['matricule'], $motif);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Historique des cas validés ou rejetés par le Chef SCGC
$historique_chef = $conn->query("
    SELECT nom, matricule, statut_chefscgc, 
           date_validation_chefscgc, 
           date_rejet_chefscgc, 
           motif_rejet_chefscgc 
    FROM agents
    WHERE statut_chefscgc IN ('valide_chefscgc', 'rejeter_par_chefscgc')
    ORDER BY COALESCE(date_validation_chefscgc, date_rejet_chefscgc) DESC
");


// Requêtes de sélection
$en_attente = $conn->query("SELECT * FROM agents WHERE statut_chefscgc = 'en_attente' ORDER BY date_validation_scgc DESC");
$valides = $conn->query("SELECT * FROM agents WHERE statut_chefscgc = 'valide_chefscgc' ORDER BY date_validation_chefscgc DESC");
$rejetes = $conn->query("SELECT * FROM agents WHERE statut_chefscgc = 'rejeter_par_chefscgc' ORDER BY date_rejet_chefscgc DESC");
$historique_chef = $conn->query("
    SELECT nom, matricule, statut_chefscgc, 
           date_validation_chefscgc, 
           date_rejet_chefscgc, 
           motif_rejet_chefscgc,
           piece_identite, justificatif_logement, certificat_vie
    FROM agents
    WHERE statut_chefscgc IN ('valide_chefscgc', 'rejeter_par_chefscgc')
    ORDER BY COALESCE(date_validation_chefscgc, date_rejet_chefscgc) DESC
");

?>

<link rel="stylesheet" href="../assets/css/admin.css">
<h2>📁 Dossiers Chef SCGC</h2>
<h3>Dossiers en attente</h3>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Documents</th>
            <th>Date validation SCGC</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($en_attente && $en_attente->num_rows > 0): ?>
            <?php while ($agent = $en_attente->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom']) ?></td>
                    <td><?= htmlspecialchars($agent['matricule']) ?></td>
                    <td>
                        <?php if (!empty($agent['piece_identite'])): ?>
                            <a href="../documents/<?= $agent['piece_identite'] ?>" target="_blank">📄 Pièce ID</a><br>
                        <?php endif; ?>
                        <?php if (!empty($agent['justificatif_logement'])): ?>
                            <a href="../documents/<?= $agent['justificatif_logement'] ?>" target="_blank">📁 Justificatif</a><br>
                        <?php endif; ?>
                        <?php if (!empty($agent['certificat_vie'])): ?>
                            <a href="../documents/<?= $agent['certificat_vie'] ?>" target="_blank">📃 Certificat de vie</a>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($agent['date_validation_scgc']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="agent_id" value="<?= (int)$agent['id'] ?>">
                            <button name="action" value="valider">✅ Valider</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="agent_id" value="<?= (int)$agent['id'] ?>">
                            <input name="motif_rejet" placeholder="Motif de rejet" required>
                            <button name="action" value="rejeter">❌ Rejeter</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">Aucun dossier en attente.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<h3>📋 Historique des dossiers traités par le Chef SCGC</h3>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Motif</th>
            <th>Documents</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($historique_chef && $historique_chef->num_rows > 0): ?>
            <?php while ($row = $historique_chef->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['matricule']) ?></td>
                    <td>
                        <?= $row['statut_chefscgc'] === 'valide_chefscgc' ? 'Validé' : 'Rejeté' ?>
                    </td>
                    <td>
                        <?= $row['statut_chefscgc'] === 'valide_chefscgc' 
                            ? htmlspecialchars($row['date_validation_chefscgc']) 
                            : htmlspecialchars($row['date_rejet_chefscgc']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['motif_rejet_chefscgc'] ?? '-') ?></td>
                    <td>
                        <?php if (!empty($row['piece_identite'])): ?>
                            <a href="../documents/<?= $row['piece_identite']; ?>" target="_blank">📄 Pièce ID</a><br>
                        <?php endif; ?>
                        <?php if (!empty($row['justificatif_logement'])): ?>
                            <a href="../documents/<?= $row['justificatif_logement']; ?>" target="_blank">🏠 Justif logement</a><br>
                        <?php endif; ?>
                        <?php if (!empty($row['certificat_vie'])): ?>
                            <a href="../documents/<?= $row['certificat_vie']; ?>" target="_blank">📃 Certificat de vie</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Aucun dossier traité par le Chef SCGC.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<!-- Autres sections (valides, rejetes, historique) identiques -->
<?php include('../includes/footer.php'); ?>
