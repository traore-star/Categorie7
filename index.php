<?php
// On vérifie si l'utilisateur est connecté ou pas
$est_connecte = !empty($_SESSION['user_id']); // ou le nom que tu utilises
?>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/header.css">

</head>
<body>
    <header class="header-nav">
        <img src="uploads/edm-sa.jpg" alt="Logo" class="header-logo">
        <h1>Tableau de bord</h1>

        <nav>
            <ul>
                <?php if ($est_connecte): ?>
                    <li><a href="traitements/loyout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="pages/login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
 <main></main>

<div class="hero">
  <div class="hero-content">
    <h1>Bienvenue sur la plateforme de gestion des abonnements - Catégorie 7 EDM-SA</h1>
    <p>Cette plateforme permet aux agents, retraités, ayants droit et partenaires de gérer efficacement leurs demandes d’abonnement.</p>
    
    <div class="action-buttons">
      <a href="pages/register.php" class="btn btn-primary">Créer un compte</a>
      <a href="pages/login.php" class="btn btn-secondary">Se connecter</a>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>

