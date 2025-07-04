<?php
session_start();
require_once('../includes/db_connect.php');
include('../includes/header.php');

//le rôle correspondant à chaque dashboard.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'adminpersonnel') {
    header("Location: ../pages/login.php");
    exit();
}

// Transmission d’un dossier au SCGC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transmettre_id'])) {
    $agent_id = intval($_POST['transmettre_id']); // L'agent que tu veux transmettre

    // 1️⃣ Mettre à jours le statut de l'agent
    $stmt = $conn->prepare("UPDATE agents SET statut_adminpersonnel='en_attente_scgc' WHERE id = ?");
    $stmt->bind_param("i", $agent_id);
    $stmt->execute();
    $stmt->close();

    // 2️⃣ Récupération des détails de l'agent depuis la base
    $stmt = $conn->prepare("SELECT nom, matricule FROM agents WHERE id = ?");
    $stmt->bind_param("i", $agent_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $agent = $result->fetch_assoc();

        $agentNom = $agent['nom'];
        $agentMatricule = $agent['matricule'];
        $actionDesc = "Transmis au SCGC";
        $acteur = "Personnel administratif";

        // 3️⃣ Inserting into historique_actions
        $dateNow = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("INSERT INTO historique_actions (agents_id, nom, matricule, action, acteur, date) VALUES (?, ?, ?, ?, ?,?)");

        $stmt->bind_param("isssss", $agents_id, $agentNom, $agentMatricule, $actionDesc, $acteur, $dateNow);
        $stmt->execute();
        $stmt->close();

        // Redirection
        header("Location: adminpersonnel_dashboard.php?msg=transmis");

        exit;

    } else {
        echo "<p>Impossible de trouver l'agent.</p>";
    }
}
// Récupération des agents à traiter
$requete_en_attente = $conn->query("SELECT * FROM agents WHERE statut_adminpersonnel = 'en_attente' ORDER BY date_soumission DESC");

?>
<link rel="stylesheet" href="../assets/css/adminpersonnel_dashboard.css">

<div class="container">
    
    <h2>📋 Dossiers en attente de traitement</h2>

    <?php if ($requete_en_attente->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Matricule</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Document</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($agent = $requete_en_attente->fetch_assoc()) : ?>
                    <tr>
                        <td><?= htmlspecialchars($agent['nom']) ?> </td>
                        <td><?= htmlspecialchars($agent['matricule']) ?> </td>
                        <td><?= htmlspecialchars($agent['email']) ?> </td>
                        <td><?= htmlspecialchars($agent['telephone']) ?> </td>
                        <td>
                            <a href="../documents/<?= $agent['piece_identite'] ?>" target="_blank">📄 Pièce ID</a><br>
                            <a href="../documents/<?= $agent['justificatif_logement'] ?>" target="_blank">🏠 Justif logement</a><br>
                            <a href="../documents/<?= $agent['certificat_vie'] ?>" target="_blank">📃 Certificat de vie</a>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Transmettre ce dossier au SCGC ?');">
                                <input type="hidden" name="transmettre_id" value="<?= $agent['id'] ?>"> 
                                <button type="submit">📤 Transmettre</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun dossier en attente actuellement.</p>
    <?php endif; ?>
    <hr>
</div>

<h2>Historique des transmissions (Administration Personnel)</h2>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Matricule</th>
            <th>Action</th>
            <th>Date</th>
            <th>Documents</th>
            <th>Acteur</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $requete = "
            SELECT h.nom, h.matricule, h.action, h.date, h.acteur,
                   a.certificat_vie, a.piece_identite, a.justificatif_logement
            FROM historique_actions h
            JOIN agents a ON h.matricule = a.matricule
            WHERE h.acteur = 'Personnel administratif'
            ORDER BY h.date DESC
        ";
        $historique = $conn->query($requete);

        if ($historique->num_rows > 0) {
            while ($histo = $historique->fetch_assoc()) {
                ?>
                <tr>
                    <td><?= htmlspecialchars($histo['nom']) ?> </td>
                    <td><?= htmlspecialchars($histo['matricule']) ?> </td>
                    <td><?= htmlspecialchars($histo['action']) ?> </td>
                    <td><?= htmlspecialchars($histo['date']) ?> </td>
                    <td>
                        <?php if (!empty($histo['piece_identite'])): ?>
                            <a href="../documents/<?= $histo['piece_identite'] ?>" target="_blank">📄 Pièce ID</a><br>
                        <?php endif; ?>
                        <?php if (!empty($histo['justificatif_logement'])): ?>
                            <a href="../documents/<?= $histo['justificatif_logement'] ?>" target="_blank">🏠 Justif logement</a><br>
                        <?php endif; ?>
                        <?php if (!empty($histo['certificat_vie'])): ?>
                            <a href="../documents/<?= $histo['certificat_vie'] ?>" target="_blank">📃 Certificat de vie</a>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($histo['acteur']) ?> </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='6'>Aucun historique</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php include('../includes/footer.php'); ?>
