<?php
require_once('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dossier_id'])) {
    $dossier_id = intval($_POST['dossier_id']);
    $date_now = date('Y-m-d H:i:s');

    $sql = "UPDATE dossiers
            SET statut_adminpersonnel = 'valide',
                date_transmission_scgc = ?,
                statut_scgc = 'en_attente'
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $date_now, $dossier_id);

    if ($stmt->execute()) {
        header("Location: adminpersonnel_dashboard.php?success=1");
        exit();
    } else {
        echo "Erreur lors de la transmission.";
    }
} else {
    echo "Requête invalide.";
}
?>
