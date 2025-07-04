<?php
require_once '../includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = isset($_POST['agent_id']) ? intval($_POST['agent_id']) : 0;

    if ($agent_id <= 0) {
        die("ID de l'agent invalide.");
    }

    if (isset($_POST['valider'])) {
        // Validation finale : dossier validé chez SCGC et transmis au chef SCGC
        $stmt = $conn->prepare("UPDATE agents SET 
            statut_scgc = 'valide', 
            statut_adminpersonnel = 'valide',
            statut_chef_scgc = 'en_attente'
        WHERE id = ?");
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $stmt->close();

        header("Location: ../SCGC/scgc_dashboard.php?success=1");
        exit();
    }

    if (isset($_POST['rejeter'])) {
        $motif = trim($_POST['motif']);
        $date_rejet = date('Y-m-d');
        $rejet_par = "SCGC";

        if (!empty($motif)) {
            $stmt = $conn->prepare("UPDATE agents SET 
                statut_scgc = 'rejete', 
                motif_rejet_scgc = ?, 
                date_rejet_scgc = ?, 
                rejet_par = ? 
            WHERE id = ?");
            $stmt->bind_param("sssi", $motif, $date_rejet, $rejet_par, $agent_id);
            $stmt->execute();
            $stmt->close();

            header("Location: ../SCGC/scgc_dashboard.php?rejet=1");
            exit();
        } else {
            header("Location: ../SCGC/scgc_dashboard.php?erreur=motif_obligatoire");
            exit();
        }
    }
} else {
    header("Location: ../SCGC/scgc_dashboard.php");
    exit();
}
