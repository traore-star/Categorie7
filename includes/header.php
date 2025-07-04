<?php
// On vérifie si l'utilisateur est connecté ou pas
$est_connecte = !empty($_SESSION['user_id']); // ou le nom que tu utilises
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    
    <link rel="stylesheet" href="../assets/css/header.css">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>
<body>
    <header class="header-nav">
        <img src="../uploads/edm-sa.jpg" alt="Logo" class="header-logo">
        <h1>Tableau de bord</h1>

        <nav>
            <ul>
                <?php if ($est_connecte): ?>
                    <li><a href="../traitements/logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="../pages/login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

 <main>