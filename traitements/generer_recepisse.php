<?php
ob_start(); // ← tampon de sortie activé
require_once '../includes/db_connect.php';
require('../fpdf/fpdf.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de dossier invalide.");
}

$agent_id = intval($_GET['id']);

$sql = "SELECT nom, matricule, date_validation_chefscgc FROM agents WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Dossier introuvable.");
}

$agent = $result->fetch_assoc();

// Création du PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'RECEPISSE DE VALIDATION SCGC D EDM-sa', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Ln(10);
$pdf->Cell(0, 10, 'Nom complet : ' . $agent['nom'], 0, 1);
$pdf->Cell(0, 10, 'Matricule : ' . $agent['matricule'], 0, 1);
$pdf->Cell(0, 10, 'Date de validation : ' . $agent['date_validation_chefscgc'], 0, 1);
$pdf->Ln(10);
$pdf->MultiCell(0, 10, "Ce document certifie que le dossier a été validé par le Chef du Service Clientèle Grande Compte (SCGC).");

ob_end_clean(); // ← on vide le tampon pour éviter les sorties accidentelles
$pdf->Output('I', 'Recepisse_' . $agent['matricule'] . '.pdf');
exit();
