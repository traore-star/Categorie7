<?php
session_start();
require_once('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transmettre_scgc'])) {
    $agent_id = intval($_POST['agent_id']);

    // Mettre à jour le statut dans la base de données
    $stmt = $conn->prepare("UPDATE agents SET statut_adminpersonnel = 'valide', statut_scgc = 'en_attente', date_transmission_scgc = NOW() WHERE id = ?");
    $stmt->bind_param("i", $agent_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Le dossier a été transmis avec succès au SCGC.";
    } else {
        $_SESSION['message'] = "Erreur lors de la transmission au SCGC.";
    }

    $stmt->close();
    header("Location: adminpersonnel_dashboard.php");
    exit();
} else {
    $_SESSION['message'] = "Requête invalide.";
    header("Location: adminpersonnel_dashboard.php");
    exit();
}
