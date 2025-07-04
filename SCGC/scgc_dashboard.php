<?php
session_start();
include('../includes/header.php');

$conn = mysqli_connect('localhost', 'root', '', 'categorie7_db');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'scgc') {
    header("Location: ../pages/login.php");
    exit();
}

if (!$conn) {
    die("Échec de la connexion : " . mysqli_connect_error());
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action'])) {
    $id = (int)$_POST['agent_id'];
    $action = $_POST['action'];

    // Récupérer infos agent
    $stmt = $conn->prepare("SELECT nom, matricule FROM agents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows == 1) {
        $agent = $res->fetch_assoc();
        $nom = $agent['nom'];
        $matricule = $agent['matricule'];

        if ($action === "valider") {
            // ✅ Mise à jour correcte des statuts pour transmission au Chef SCGC
            $stmt = $conn->prepare("UPDATE agents SET statut_scgc = 'valide_scgc', statut_chefscgc = 'en_attente', date_validation_scgc = NOW() WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO historique_actions (agents_id, nom, matricule, action, acteur, date) VALUES (?, ?, ?, 'valider', 'SCGC', NOW())");
            $stmt->bind_param("iss", $id, $nom, $matricule);
            $stmt->execute();
            $stmt->close();
        }

        if ($action === "rejeter" && !empty($_POST['motif_rejet'])) {
            $motif = $_POST['motif_rejet'];

            $stmt = $conn->prepare("UPDATE agents SET statut_scgc = 'rejete_scgc', motif_rejet_scgc = ?, date_rejet_scgc = NOW() WHERE id = ?");
            $stmt->bind_param("si", $motif, $id);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO historique_actions (agents_id, nom, matricule, action, motif_rejet, acteur, date) VALUES (?, ?, ?, 'rejeter', ?, 'SCGC', NOW())");
            $stmt->bind_param("isss", $id, $nom, $matricule, $motif);
            $stmt->execute();
        }
    }
}

// ✅ Dossiers transmis par administration du personnel
$en_attente = mysqli_query($conn, "
    SELECT * FROM agents 
    WHERE statut_adminpersonnel = 'en_attente_scgc'
    AND (statut_scgc IS NULL OR statut_scgc = '')
    ORDER BY date_soumission DESC
");

$valides = mysqli_query($conn, "SELECT * FROM agents WHERE statut_scgc = 'valide_scgc' ORDER BY date_validation_scgc DESC");
$rejetesAudit = mysqli_query($conn, "SELECT * FROM agents WHERE statut_audit = 'rejeter_audit' ORDER BY date_rejet_audit DESC");
$rejetes_chefscgc = mysqli_query($conn, "SELECT * FROM agents WHERE statut_chefscgc = 'rejeter_par_chefscgc' ORDER BY date_rejet_chefscgc DESC");
$historique = $conn->query("
    SELECT h.*, a.piece_identite, a.justificatif_logement, a.certificat_vie
    FROM historique_actions h
    JOIN agents a ON h.matricule = a.matricule
    WHERE h.acteur = 'SCGC'
    ORDER BY h.date DESC
");

?>

<link rel="stylesheet" href="../assets/css/admin.css">
<h2>📁 Dossiers SCGC</h2>

<h3>Dossiers en attente</h3>
<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Date de soumission</th>
            <th>Documents</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($en_attente) > 0): ?>
            <?php while ($agent = mysqli_fetch_assoc($en_attente)): ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom']); ?></td>
                    <td><?= htmlspecialchars($agent['matricule']); ?></td>
                    <td><?= htmlspecialchars($agent['date_soumission']); ?></td>
                    <td>
                        <a href="../documents/<?= $agent['piece_identite']; ?>">Pièce d'identité</a><br>
                        <a href="../documents/<?= $agent['justificatif_logement']; ?>">Justificatif logement</a><br>
                        <a href="../documents/<?= $agent['certificat_vie']; ?>">Certificat de vie</a>
                    </td>
                    <td>
                        <form method="POST" style="margin-bottom: 8px;">
                            <input type="hidden" name="agent_id" value="<?= $agent['id']; ?>">                                
                            <button name="action" value="valider">✅ Valider</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="agent_id" value="<?= $agent['id']; ?>">                                
                            <input name="motif_rejet" type="text" placeholder="Motif de rejet" required>
                            <button name="action" value="rejeter">❌ Rejeter</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan='5'>Aucun cas en attente.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<h3>Dossiers rejetés par le Chef SCGC</h3>
<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Date</th>
            <th>Motif</th>
            <th>Documents</th> <!-- Ajout de la colonne -->
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($rejetes_chefscgc) > 0): ?>
            <?php while ($agent = mysqli_fetch_assoc($rejetes_chefscgc)): ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom']); ?></td>
                    <td><?= htmlspecialchars($agent['matricule']); ?></td>
                    <td><?= htmlspecialchars($agent['date_rejet_chefscgc']); ?></td>
                    <td><?= htmlspecialchars($agent['motif_rejet_chefscgc']); ?></td>
                    <td>
                        <?php if (!empty($agent['piece_identite'])): ?>
                            <a href="../documents/<?= htmlspecialchars($agent['piece_identite']); ?>" target="_blank">📄 Pièce ID</a><br>
                        <?php endif; ?>
                        <?php if (!empty($agent['justificatif_logement'])): ?>
                            <a href="../documents/<?= htmlspecialchars($agent['justificatif_logement']); ?>" target="_blank">🏠 Justif logement</a><br>
                        <?php endif; ?>
                        <?php if (!empty($agent['certificat_vie'])): ?>
                            <a href="../documents/<?= htmlspecialchars($agent['certificat_vie']); ?>" target="_blank">📃 Certificat de vie</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan='5'>Aucun cas rejeté.</td></tr>
        <?php endif; ?>
    </tbody>
</table>


<!-- <h3>Dossiers validés par SCGC</h3>
<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($valides) > 0): ?>
            <?php while ($agent = mysqli_fetch_assoc($valides)): ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom']); ?></td>
                    <td><?= htmlspecialchars($agent['matricule']); ?></td>
                    <td><?= htmlspecialchars($agent['date_validation_scgc']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan='3'>Aucun cas validé.</td></tr>
        <?php endif; ?>
    </tbody>
</table> -->

<hr>

<h2>Dossiers rejetés par l'audit interne</h2>
<?php if ($rejetesAudit->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Matricule</th>
                <th>Motif de rejet</th>
                <th>Date de rejet</th>
                <th>Documents</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($agent = $rejetesAudit->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom']); ?></td>
                    <td><?= htmlspecialchars($agent['matricule']); ?></td>
                    <td><?= htmlspecialchars($agent['motif_rejet_audit']); ?></td>
                    <td><?= htmlspecialchars($agent['date_rejet_audit']); ?></td>
                    <td>
                        <?php if (!empty($agent['piece_identite'])): ?>
                            <a href="../documents/<?= $agent['piece_identite']; ?>" target="_blank">📄 Pièce ID</a><br>
                        <?php endif; ?>
                        <?php if (!empty($agent['justificatif_logement'])): ?>
                            <a href="../documents/<?= $agent['justificatif_logement']; ?>" target="_blank">🏠 Justif logement</a><br>
                        <?php endif; ?>
                        <?php if (!empty($agent['certificat_vie'])): ?>
                            <a href="../documents/<?= $agent['certificat_vie']; ?>" target="_blank">📃 Certificat de vie</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Aucun dossier rejeté par l'audit interne.</p>
<?php endif; ?>


<h3>📜 Historique des traitements SCGC</h3>
<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Action</th>
            <th>Motif de rejet</th>
            <th>Documents</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $historique->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nom']); ?></td>
                <td><?= htmlspecialchars($row['matricule']); ?></td>
                <td><?= htmlspecialchars($row['action']); ?></td>
                <td><?= !empty($row['motif_rejet']) ? htmlspecialchars($row['motif_rejet']) : '-'; ?></td>
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
                <td><?= htmlspecialchars($row['date']); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include('../includes/footer.php'); ?>
