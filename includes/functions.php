<?php
function changerStatut($conn, $id, $nouveauStatut, $dateColonne) {
    $stmt = $conn->prepare("UPDATE agents SET statut = ?, $dateColonne = NOW() WHERE id = ?");
    $stmt->bind_param("si", $nouveauStatut, $id);
    $stmt->execute();
    $stmt->close();
}

// Récupère l'ancien statut
$stmt = $conn->prepare("SELECT statut FROM agents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($oldStatut);
$stmt->fetch();
$stmt->close();

$newStatut = 'en_attente_chefscgc';

$stmt = $conn->prepare("UPDATE agents SET statut = ?, date_validation_scgc = NOW() WHERE id = ?");
$stmt->bind_param("si", $newStatut, $id);
$stmt->execute();
$stmt->close();

// Insert dans l'historique
$stmt = $conn->prepare("INSERT INTO historique_dossiers(id_agent, ancien_statut, nouveau_statut, date) VALUES(?,?,?,NOW())");

$stmt->bind_param("iss", $id, $oldStatut, $newStatut);
$stmt->execute();
$stmt->close();


// Récupération de l'agent
$stmt = $conn->prepare("SELECT historique FROM agents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($historique);
$stmt->fetch();
$stmt->close();

$historique = $historique ? json_decode($historique, true) : []; // ou array vide si nul

// Ajouter le nouvel élément
$historique[] = [
    "date"   => date("Y-m-d H:i:s"),
    "statut" => "en_attente_chefscgc"
];

// Mettre à jours
$historique_json = json_encode($historique);

$stmt = $conn->prepare("UPDATE agents SET statut = ?, historique = ? WHERE id = ?");
$stmt->bind_param("ssi", $nouveau_statut, $historique_json, $id);
$stmt->execute();
$stmt->close();

function traiterDossierChefSCGC($conn, $id, $action, $motif = null) {
    if ($action === 'valider') {
        $stmt = $conn->prepare("UPDATE agents SET statut = 'valide_chefscgc', date_validation_chefscgc = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
    } elseif ($action === 'rejeter' && $motif !== null) {
        // Le rejet renvoie vers SCGC avec un statut explicite
        $stmt = $conn->prepare("UPDATE agents SET statut = 'rejeter_scgc', motif_rejet_scgc = ?, date_rejet_scgc = NOW(), rejet_par = 'chefscgc' WHERE id = ?");
        $stmt->bind_param("si", $motif, $id);
    } else {
        return false; // Action invalide
    }

    $result = $stmt->execute();
    $stmt->close();
    return $result;
}



?>
