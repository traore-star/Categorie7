<?php
session_start();
include('../includes/header.php');
require_once('../includes/db_connect.php');

//le rôle correspondant à chaque dashboard.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'audit_interne') {
    header("Location: ../pages/login.php");
    exit();
}

// Traitement des actions depuis le formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['agent_id'], $_POST['action'])) {
        $id = intval($_POST['agent_id']);
        $action = $_POST['action'];

        // Récupération de l'agent
        $stmt = $conn->prepare("SELECT nom, matricule FROM agents WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $agent = $result->fetch_assoc();
            $agentNom = $agent['nom'];
            $agentMatricule = $agent['matricule'];

            $acteur = "Audit Interne";

            if ($action == "valider") {
                // Mise à jour du statut
                $stmt = $conn->prepare("UPDATE agents SET statut_audit='valide_audit', date_validation_audit = NOW() WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                $actionDesc = "valider";

                // Vérification pour éviter doublon dans historique_actions
                $check = $conn->prepare("SELECT id FROM historique_actions WHERE agents_id = ? AND action = ? AND acteur = ? AND DATE(date) = CURDATE()");
                $check->bind_param("iss", $id, $actionDesc, $acteur);
                $check->execute();
                $result = $check->get_result();

                if ($result->num_rows === 0) {
                    $stmt = $conn->prepare("INSERT INTO historique_actions (agents_id, nom, matricule, action, date, acteur) VALUES (?, ?, ?, ?, NOW(), ?)");
                    $stmt->bind_param("issss", $id, $agentNom, $agentMatricule, $actionDesc, $acteur);
                    $stmt->execute();
                    $stmt->close();
                }
                $check->close();

                // Vérification pour éviter doublon dans audit_historique
                $check2 = $conn->query("SELECT id FROM audit_historique WHERE action = 'Dossier validé' AND DATE(date_action) = CURDATE()");
                if ($check2->num_rows === 0) {
                    $conn->query("INSERT INTO audit_historique (action, date_action) VALUES ('Dossier validé', NOW())");
                }

            } elseif ($action == "rejeter" && !empty($_POST['motif_rejet'])) {
                $motif = $_POST['motif_rejet'];

                // Mise à jour du statut avec motif
                $stmt = $conn->prepare("UPDATE agents SET statut_audit='rejeter_audit', motif_rejet_audit = ?, date_rejet_audit = NOW() WHERE id = ?");
                $stmt->bind_param("si", $motif, $id);
                $stmt->execute();
                $stmt->close();

                $actionDesc = "rejeter";

                // Vérification pour éviter doublon dans historique_actions
                $check = $conn->prepare("SELECT id FROM historique_actions WHERE agents_id = ? AND action = ? AND acteur = ? AND DATE(date) = CURDATE()");
                $check->bind_param("iss", $id, $actionDesc, $acteur);
                $check->execute();
                $result = $check->get_result();

                if ($result->num_rows === 0) {
                    $stmt = $conn->prepare("INSERT INTO historique_actions (agents_id, nom, matricule, action, motif_rejet, date, acteur) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
                    $stmt->bind_param("isssss", $id, $agentNom, $agentMatricule, $actionDesc, $motif, $acteur);
                    $stmt->execute();
                    $stmt->close();
                }
                $check->close();

                // Vérification pour éviter doublon dans audit_historique
                $check2 = $conn->query("SELECT id FROM audit_historique WHERE action = 'Dossier rejeté' AND DATE(date_action) = CURDATE()");
                if ($check2->num_rows === 0) {
                    $conn->query("INSERT INTO audit_historique (action, date_action) VALUES ('Dossier rejeté', NOW())");
                }
            }
        } else {
            echo "<p>Agent non trouvé</p>";
        }
    }
}

// Chargement des listes
$en_attente = $conn->query("SELECT * FROM agents WHERE statut_chefscgc = 'valide_chefscgc' AND (statut_audit IS NULL OR statut_audit = '') ORDER BY date_validation_chefscgc DESC
");

$rejetes = $conn->query("SELECT * FROM agents WHERE statut_audit='rejeter_audit' ORDER BY date_rejet_audit DESC");
$valides = $conn->query("SELECT * FROM agents WHERE statut_audit='valide_audit' ORDER BY date_validation_audit DESC");
?>


<link rel="stylesheet" href="../assets/css/admin.css">
<h2>📁 Dossiers en attente (Audit Interne)</h2>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Date de transmission</th>
            <th>Documents</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($en_attente->num_rows > 0) {
            while ($agent = $en_attente->fetch_assoc()) {
                ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom']) ?> </td>
                    <td><?= htmlspecialchars($agent['matricule']) ?> </td>
                    <td><?= htmlspecialchars($agent['date_validation_chefscgc']) ?> </td>
                    <td>
                        <a href="../documents/<?= $agent['piece_identite'] ?>" target="_blank">Pièce d'identité</a><br>
                        <a href="../documents/<?= $agent['justificatif_logement'] ?>" target="_blank">Justificatif logement</a><br>
                        <a href="../documents/<?= $agent['certificat_vie'] ?>" target="_blank">Certificat de vie</a>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="agent_id" value="<?= $agent['id'] ?>">                                
                            <button name="action" value="valider" class="btn btn-info">Valider</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="agent_id" value="<?= $agent['id'] ?>">                                
                            <input name="motif_rejet" type="text" placeholder="Motif de rejet" required>
                            <button name="action" value="rejeter" class="btn btn-danger">Rejeter</button>
                        </form>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='5'>Aucun cas en attente.</td></tr>";
        }
        ?>
    </tbody>
</table>

<h3>📋 Cas rejetés par Audit Interne</h3>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Date rejet</th>
            <th>Motif</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($rejetes->num_rows > 0) {
            while ($agent = $rejetes->fetch_assoc()) {
                ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom']) ?> </td>
                    <td><?= htmlspecialchars($agent['matricule']) ?> </td>
                    <td><?= htmlspecialchars($agent['date_rejet_audit']) ?> </td>
                    <td><?= htmlspecialchars($agent['motif_rejet_audit']) ?> </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='4'>Aucun cas rejeté.</td></tr>";
        }
        ?>
    </tbody>
</table>

<h3>📋 Cas validés par Audit Interne</h3>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($valides->num_rows > 0) {
            while ($agent = $valides->fetch_assoc()) {
                ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom']) ?> </td>
                    <td><?= htmlspecialchars($agent['matricule']) ?> </td>
                    <td><?= htmlspecialchars($agent['date_validation_audit']) ?> </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='3'>Aucun cas validé.</td></tr>";
        }
        ?>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Action</th>
            <th>Motif</th>
            <th>Documents</th>
            <th>Date</th>
            <th>Acteur</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $historique = $conn->query("SELECT * FROM historique_actions WHERE acteur='Audit Interne' ORDER BY date DESC");

        if ($historique->num_rows > 0) {
            while ($histo = $historique->fetch_assoc()) {
                // Récupérer les documents depuis la table agents
                $matricule = $histo['matricule'];
                $docs = $conn->query("SELECT piece_identite, justificatif_logement, certificat_vie FROM agents WHERE matricule = '$matricule'");
                $doc = $docs->fetch_assoc();
                ?>
                <tr>
                    <td><?= htmlspecialchars($histo['nom'] ?? '') ?> </td>
                    <td><?= htmlspecialchars($histo['matricule'] ?? '') ?> </td>
                    <td><?= htmlspecialchars($histo['action'] ?? '') ?> </td>
                    <td><?= htmlspecialchars($histo['motif_rejet'] ?? '-') ?> </td>
                    <td>
                        <?php if (!empty($doc['piece_identite'])): ?>
                            <a href="../documents/<?= $doc['piece_identite'] ?>" target="_blank">📄 ID</a><br>
                        <?php endif; ?>
                        <?php if (!empty($doc['justificatif_logement'])): ?>
                            <a href="../documents/<?= $doc['justificatif_logement'] ?>" target="_blank">🏠 Justif</a><br>
                        <?php endif; ?>
                        <?php if (!empty($doc['certificat_vie'])): ?>
                            <a href="../documents/<?= $doc['certificat_vie'] ?>" target="_blank">📃 Certificat</a>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($histo['date'] ?? '') ?> </td>
                    <td><?= htmlspecialchars($histo['acteur'] ?? '') ?> </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='7'>Aucun historique</td></tr>";
        }
        ?>
    </tbody>
</table>


<?php include('../includes/footer.php'); ?>