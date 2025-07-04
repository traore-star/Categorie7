<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "categorie7_db"; // Remplace par le nom correct si besoin

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}
?>

