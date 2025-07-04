<?php
session_start();
require_once('../includes/db_connect.php');
include('../includes/header.php');

// Sécurité : seul l'admin peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}
// // Charger tous les rôles pour la liste déroulante
$message = "";
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_utilisateur'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $telephone = trim($_POST['telephone']);
    $matricule = trim($_POST['matricule']);
    $role = trim($_POST['role']);

    // Vérifie si l'email existe déjà
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "<span style='color:red;'>❌ Email déjà utilisé.</span>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (prenom, nom, email, mot_de_passe, telephone, matricule, role, created_at, actif)
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
        $stmt->bind_param("sssssss", $prenom, $nom, $email, $mot_de_passe, $telephone, $matricule, $role);

        if ($stmt->execute()) {
            $message = "<span style='color:green;'>✅ Utilisateur ajouté avec succès.</span>";
        } else {
            $message = "<span style='color:red;'>❌ Une erreur est survenue.</span>";
        }

        $stmt->close();
    }

    $check->close();
}

// Désactiver / Réactiver un utilisateur
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $getStatus = $conn->query("SELECT actif FROM users WHERE id = $id");
    if ($getStatus && $getStatus->num_rows === 1) {
        $current = $getStatus->fetch_assoc()['actif'];
        $new = $current ? 0 : 1;
        $conn->query("UPDATE users SET actif = $new WHERE id = $id");
        header("Location: gestion_utilisateurs.php");
        exit();
    }
}
$search = trim($_GET['search_matricule'] ?? '');
if ($search !== '') {
    $stmt = $conn->prepare("SELECT id, nom, prenom, email, matricule, telephone, created_at, actif, role FROM users WHERE matricule LIKE ? ORDER BY created_at DESC");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT id, nom, prenom, email, matricule, telephone, created_at, actif, role FROM users ORDER BY created_at DESC");
}

?>

<link rel="stylesheet" href="../assets/css/admin.css">

<h3 style="cursor: pointer;" id="toggleForm">
    <span id="toggleIcon">➕</span> Ajouter un utilisateur
</h3>

<?php if (!empty($message)) echo "<p>$message</p>"; ?>

<div id="formContainer" style="display: none; transition: all 0.4s ease;">
    <form method="POST" action="" style="margin-bottom:30px;">
        <label>Prénom :</label>
        <input type="text" name="prenom" required>

        <label>Nom :</label>
        <input type="text" name="nom" required>

        <label>Email (Gmail) :</label>
        <input type="email" name="email" required>

        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required>

        <label>Matricule :</label>
        <input type="text" name="matricule" required>

        <label>Téléphone :</label>
        <input type="text" name="telephone" required>

        <label for="role">Rôle :</label>
        <select name="role" required>
            <option value="">-- Sélectionner un rôle --</option>
            <option value="admin">administrateur</option>
            <option value="secretaire_drh">secretaire_drh</option>
            <option value="adminpersonnel_dashboard">administration personnel</option>
            <option value="scgc">scgc</option>
            <option value="chefscgc">chefscgc</option>
            <option value="audit_interne">audit_interne</option>
        </select>

        <button type="submit" name="ajouter_utilisateur">Ajouter l'utilisateur</button>
    </form>
</div>


<div class="container">
    <h2>📋 Gestion des utilisateurs</h2>
    <form method="GET" action="" style="margin-bottom: 20px;">
    <input type="text" name="search_matricule" placeholder="🔍 Rechercher par matricule..." value="<?= htmlspecialchars($_GET['search_matricule'] ?? '') ?>" />
    <button type="submit">Rechercher</button>
</form>
    <p>Liste complète des utilisateurs enregistrés dans le système.</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Matricule</th>
                <th>Rôle</th>
                <th>Date de création</th>
                <th>Téléphone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr style="background-color: <?= $row['actif'] == 0 ? '#f8d7da' : '#ffffff'; ?>; color: black;">
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nom']?? '') ?></td>
                        <td><?= htmlspecialchars($row['prenom']?? '') ?></td>
                        <td><?= htmlspecialchars($row['email']?? '') ?></td>
                        <td><?= htmlspecialchars($row['matricule']?? '') ?></td>
                        <td><?= htmlspecialchars($row['role'] ?? '—') ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td><?= htmlspecialchars($row['telephone']) ?></td>
                        <td>
                            <a href="modifier_utilisateur.php?id=<?= $row['id'] ?>">✏️ Modifier</a> |
                            <a href="?toggle=<?= $row['id'] ?>" onclick="return confirm('Voulez-vous vraiment <?= $row['actif'] ? 'désactiver' : 'réactiver' ?> cet utilisateur ?')">
                                <?= $row['actif'] ? '❌ Désactiver' : '✅ Réactiver' ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">Aucun utilisateur trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    const toggleBtn = document.getElementById('toggleForm');
    const formContainer = document.getElementById('formContainer');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', () => {
        if (formContainer.style.display === 'none') {
            formContainer.style.display = 'block';
            toggleIcon.textContent = '➖';
        } else {
            formContainer.style.display = 'none';
            toggleIcon.textContent = '➕';
        }
    });
</script>

<?php include('../includes/footer.php'); ?>
