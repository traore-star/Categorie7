<?php
require_once '../includes/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_id = intval($_POST['agent_id']);

    if (isset($_POST['valider'])) {
        $stmt = $conn->prepare("UPDATE agents SET statut_chef_scgc = 'valide', date_validation_chefscgc = NOW() WHERE id = ?");
        $stmt->bind_param("i", $agent_id);
        $stmt->execute();
        $stmt->close();

        header("Location: ../traitements/generer_recepisse.php?id=$agent_id");
        exit();

    }

    if (isset($_POST['rejeter'])) {
        $motif = trim($_POST['motif']);
        $date = date('Y-m-d');
        $rejet_par = "Chef SCGC";

        if (!empty($motif)) {
            $stmt = $conn->prepare("UPDATE agents SET statut_chef_scgc = 'rejete', motif_rejet_chefscgc = ?, date_rejet_chefscgc = ?, rejet_par = ? WHERE id = ?");
            $stmt->bind_param("sssi", $motif, $date, $rejet_par, $agent_id);
            $stmt->execute();
            $stmt->close();

            header("Location: ../SCGC/chefscgc_dashboard.php?rejet=1");
            exit();
        } else {
            header("Location: ../SCGC/chefscgc_dashboard.php?erreur=motif_obligatoire");
            exit();
        }
    }
} else {
    header("Location: ../SCGC/chefscgc_dashboard.php");
    exit();
}
