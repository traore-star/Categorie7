<?php
require('../fpdf/fpdf.php');  // 🔁 Corrigé avec chemin correct

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "categorie7_db");
if ($conn->connect_error) {
    die("Échec de connexion : " . $conn->connect_error);
}

// Récupération de l'ID depuis l'URL
$agent_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($agent_id <= 0) {
    die("ID invalide.");
}

// Requête pour récupérer les infos de l’agent
$stmt = $conn->prepare("SELECT nom, matricule, email, date_soumission FROM agents WHERE id = ?");
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Aucun agent trouvé.");
}

$agent = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Création du PDF
$pdf = new FPDF();
$pdf->AddPage('P', 'A4');
$pdf->SetFont('Arial', 'B', 16);

// En-tête
$pdf->Cell(0, 10, 'RECEPISE DE VALIDATION - SCGC EDM-sa', 0, 1, 'C');
$pdf->Ln(10);

// Infos de l'agent
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Nom complet :');
$pdf->Cell(100, 10, mb_convert_encoding($agent['nom'], 'ISO-8859-1', 'UTF-8'), 0, 1);


$pdf->Cell(50, 10, 'Matricule :');
$pdf->Cell(100, 10, $agent['matricule'], 0, 1);

$pdf->Cell(50, 10, 'Email :');
$pdf->Cell(100, 10, $agent['email'], 0, 1);

$pdf->Cell(50, 10, 'Date de soumission :');
$pdf->Cell(100, 10, $agent['date_soumission'], 0, 1);

$pdf->Ln(10);

// Mention de validation
$pdf->MultiCell(0, 10, mb_convert_encoding("Ce récépissé atteste que le dossier de l'agent susmentionné a été validé par le Service Clientèle Grande Compte (SCGC).", 'ISO-8859-1', 'UTF-8'));
$pdf->Ln(10);

// Signature fictive
$pdf->Cell(0, 10, 'Signature SCGC : ______________________', 0, 1, 'R');

// Sortie du PDF
$pdf->Output('I', 'recepisse_' . $agent['matricule'] . '.pdf');
?>

