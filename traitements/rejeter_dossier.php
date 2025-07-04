<?php
require_once('../includes/db_connect.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "UPDATE dossiers SET statut_scgc = 'rejete', date_traitement_scgc = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: ../SCGC/chefscgc_dashboard.php");
exit();
