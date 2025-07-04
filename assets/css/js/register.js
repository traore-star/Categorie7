document.addEventListener('DOMContentLoaded', function () {
  const typeUtilisateur = document.getElementById('type_utilisateur');
  const matriculeBlock = document.getElementById('matricule_block');
  const matriculeInput = document.getElementById('matricule');

  const ayantDroitBlock = document.getElementById('ayant_droit_block');
  const ayantDroitSelect = document.getElementById('ayant_droit');

  function updateFields() {
    const type = typeUtilisateur.value;

    // Affichage matricule : requis sauf si administrateur
    if (type === 'administrateur') {
      matriculeBlock.style.display = 'none';
      matriculeInput.removeAttribute('required');
      matriculeInput.value = '';
    } else {
      matriculeBlock.style.display = 'block';
      matriculeInput.setAttribute('required', 'required');
    }

    // Affichage ayant droit si retraité
    if (type === 'retraite') {
      ayantDroitBlock.style.display = 'block';
      ayantDroitSelect.setAttribute('required', 'required');
    } else {
      ayantDroitBlock.style.display = 'none';
      ayantDroitSelect.removeAttribute('required');
      ayantDroitSelect.value = '';
    }
  }

  typeUtilisateur.addEventListener('change', updateFields);

  // Appliquer au chargement
  updateFields();
});
