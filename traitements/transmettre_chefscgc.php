<?php
include('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_dossier'])) {
    $id_dossier = intval($_POST['id_dossier']);
    $date_actuelle = date('Y-m-d H:i:s');

    $query = "UPDATE dossiers 
              SET date_transmission_chefscgc = '$date_actuelle',
                  statut_chefscgc = 'en_attente'
              WHERE id = $id_dossier";

    if (mysqli_query($conn, $query)) {
        header("Location: scgc_dashboard.php?message=transmis");
        exit;
    } else {
        echo "Erreur lors de la transmission : " . mysqli_error($conn);
    }
} else {
    echo "Requête invalide.";
}
