<?php
session_start();
//le rôle correspondant à chaque dashboard.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

include('../includes/header.php');
?>
<link rel="stylesheet" href="../assets/css/dashboard.css">



<div class="dashboard-container">
    <h2>Bienvenue Administrateur 👨‍💼</h2>
    <!-- <p>Tableau de bord - Contrôle complet du système</p> -->

    <div class="dashboard-actions">
        <a href="../admin/gestion_utilisateurs.php" class="btn btn-primary">Gérer les utilisateurs</a>
        <!-- <a href="../admin/gestion_demandes.php" class="btn btn-secondary">Gérer les demandes d’abonnement</a>
        <a href="../admin/statistiques.php" class="btn btn-tertiary">Voir les statistiques</a>
        <a href="../pages/historique.php" class="btn btn-outline">Consulter l’historique global</a> -->
        <!-- <a href="../traitements/loyout.php" class="btn btn-danger">Se déconnecter</a> -->
    </div>
</div>

<?php include('../includes/footer.php'); ?>
