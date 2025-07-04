<?php
include('../includes/header.php');
?>
<link rel="stylesheet" href="../assets/css/style.css">

<div class="container">
  <h2>Demande d’abonnement - Retraité EDM-SA</h2>
  <form action="controlleurs/retraite_registre_controller.php" method="POST" enctype="multipart/form-data">
    
    <label for="nom">Nom complet</label>
    <input type="text" name="nom_retraite" required>

    <label for="matricule">Matricule EDM-SA</label>
    <input type="text" name="matricule" required>

    <label for="email">Email</label>
    <input type="email" name="email" required>

    <label for="telephone">Téléphone</label>
    <input type="text" name="telephone" required>

    <label for="adresse">Adresse du site (lieu d’abonnement)</label>
    <input type="text" name="adresse" required>

    <label>Conjoint (laisser vide si non concerné)</label>
    <input type="text" name="conjoint">

    <label>Enfants (laisser vide si non concerné)</label>
    <input type="text" name="enfants">

    <label>Tuteur (laisser vide si non concerné)</label>
    <input type="text" name="tuteur">

    <label for="carte_identite">Pièce d’identité (PDF ou image)</label>
    <input type="file" name="carte_identite" accept=".pdf,.jpg,.jpeg,.png" required>

    <label for="justificatif_lien">Justificatif de lien (avec l’ayant droit sélectionné)</label>
    <input type="file" name="justificatif_lien" accept=".pdf,.jpg,.jpeg,.png" required>

    <label for="certificat_vie">Certificat de vie</label>
    <input type="file" name="certificat_vie" accept=".pdf,.jpg,.jpeg,.png" required>

    <button type="submit">Soumettre la demande</button>
  </form>
</div>

<?php
include('../includes/footer.php');
?>
