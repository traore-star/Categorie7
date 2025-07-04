<?php
session_start();
require_once('../includes/db_connect.php');
include('../includes/header.php');

//le rôle correspondant à chaque dashboard.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'secretaire_drh') {
    header("Location: ../pages/login.php");
    exit();
}
// Message de confirmation
$message = "";
// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom']);
    $matricule = trim($_POST['matricule']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $ayant_droit = trim($_POST['ayant_droit']);
    $type_logement = trim($_POST['type_logement']);
    $adresse = trim($_POST['adresse']);
    $type_agent = $_POST['type_agent'] ?? '';
    $ayant_droit = ($type_agent === 'decede') ? trim($_POST['ayant_droit']) : null;


    // Upload des fichiers
    $piece_identite = $_FILES['piece_identite']['name'];
    $justificatif_logement = $_FILES['justificatif_logement']['name'];
    $certificat_vie = $_FILES['certificat_vie']['name'];

    $target_dir = "../documents/";
    move_uploaded_file($_FILES['piece_identite']['tmp_name'], $target_dir . $piece_identite);
    move_uploaded_file($_FILES['justificatif_logement']['tmp_name'], $target_dir . $justificatif_logement);
    move_uploaded_file($_FILES['certificat_vie']['tmp_name'], $target_dir . $certificat_vie);

    // Enregistrement en BDD
    $stmt = $conn->prepare("INSERT INTO agents 
        (nom, matricule, email, telephone, ayant_droit, type_logement, adresse , type_agent, piece_identite, justificatif_logement, certificat_vie, statut_adminpersonnel, date_soumission, vu_par_secretaire)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), 0)");

    $stmt->bind_param("sssssssssss", $nom, $matricule, $email, $telephone, $ayant_droit, $type_logement, $adresse, $type_agent, $piece_identite, $justificatif_logement, $certificat_vie);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>✅ Agent enregistré avec succès.</p>";
    } else {
        $message = "<p style='color:red;'>❌ Erreur lors de l'enregistrement.</p>";
    }

    $stmt->close();
}

// 🔔 Notification de rejet non vus
// $notif_sql = "SELECT COUNT(*) AS total_rejets FROM agents WHERE statut_adminpersonnel = 'rejete' AND vu_par_secretaire = 0";
// $notif_result = $conn->query($notif_sql);

// if ($notif_result) {
//     $row = $notif_result->fetch_assoc();
//     $total_rejets = $row['total_rejets'];
// } else {
//     $total_rejets = 0;
// }


// Liste des agents
$agents = $conn->query("SELECT * FROM agents ORDER BY date_soumission DESC");
?>

<link rel="stylesheet" href="../assets/css/secretaire_dashboard.css">

<div class="container">

    <h2 style="cursor:pointer;" onclick="toggleFormulaire()">
        <span id="toggleIcon">📝</span> Saisie des informations des agents
    </h2>

    <?= $message ?>

    <div id="formulaire_agent" style="display: none;">

    <form action="" method="POST" enctype="multipart/form-data">
        <label>Nom et Prenom :</label>
        <input type="text" name="nom" required>

        <label>Matricule :</label>
        <input type="text" name="matricule" required>

        <label>Email :</label>
        <input type="email" name="email" required>

        <label>Téléphone :</label>
        <input type="text" name="telephone" required>

        <div class="form-group">
            <label for="type_agent">Type d’agent :</label>
            <select name="type_agent" id="type_agent" required onchange="toggleAyantDroit()">
                <option value="">-- Sélectionner --</option>
                <option value="actif">Actif</option>
                <option value="retraite">Retraité</option>
                <option value="decede">Décédé</option>
            </select>
        </div>

        <div class="form-group ayant-droit-section" id="ayant_droit_section">
            <label for="ayant_droit">Ayant droit :</label>
            <input type="text" name="ayant_droit" id="ayant_droit">
        </div>

        <label>Type de logement :</label>
        <input type="text" name="type_logement" required>

        <label>Adresse :</label>
        <input type="text" name="adresse" required>

        <label>Pièce d'identité (PDF ou image) :</label>
        <input type="file" name="piece_identite" accept=".pdf,.jpg,.jpeg,.png" required>

        <label>Justificatif de logement (PDF ou image) :</label>
        <input type="file" name="justificatif_logement" accept=".pdf,.jpg,.jpeg,.png" required>

        <label>Certificat de vie :</label>
        <input type="file" name="certificat_vie" accept=".pdf,.jpg,.jpeg,.png" required>

        <button type="submit">Soumettre</button>
    </form>
</div>
    <hr>

    <h3>📋 Liste des agents soumis</h3>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Matricule</th>
                <th>Email</th>
                <th>Téléphone</th>
                <!-- <th>Statut</th> -->
                <th>Date de soumission</th>
                <th>Modification</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($agent = $agents->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($agent['nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($agent['matricule'] ?? '') ?></td>
                    <td><?= htmlspecialchars($agent['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($agent['telephone'] ?? '') ?></td>
                    <!-- <td><?= htmlspecialchars($agent['statut'] ?? '') ?></td> -->
                    <td><?= htmlspecialchars($agent['date_soumission'] ?? '') ?></td>

                    <td>
                        <?php if ($agent['statut_adminpersonnel'] == 'en_attente'): ?>
                            <a href="modifier_agent.php?id=<?= $agent['id'] ?>">✏️ Modifier</a>
                        <?php else: ?>
                            <span style="color:gray;">🔒 Non modifiable</span>
                        <?php endif; ?>
                    </td>

                </tr>
            <?php endwhile; ?>

                   <script>
                function toggleAyantDroit() {
                    const typeAgent = document.getElementById('type_agent').value;
                    const section = document.getElementById('ayant_droit_section');
                    const input = document.getElementById('ayant_droit');

                    if (typeAgent === 'decede') {
                        section.classList.add('ayant-droit-visible');
                        input.setAttribute('required', 'required');
                    } else {
                        section.classList.remove('ayant-droit-visible');
                        input.removeAttribute('required');
                        input.value = ''; // Vider le champ
                    }
                }
                </script>

                <script>
            function toggleFormulaire() {
                const form = document.getElementById('formulaire_agent');
                const icon = document.getElementById('toggleIcon');
                if (form.style.display === "none") {
                    form.style.display = "block";
                    icon.textContent = "➖";
                } else {
                    form.style.display = "none";
                    icon.textContent = "📝";
                }
            }
            </script>

        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>
