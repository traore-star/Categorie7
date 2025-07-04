document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");

  form.addEventListener("submit", function (e) {
    let errors = [];

    // Vérification des champs obligatoires
    const nom = form.nom_retraite.value.trim();
    const matricule = form.matricule.value.trim();
    const email = form.email.value.trim();
    const telephone = form.telephone.value.trim();
    const conjoint = form.conjoint.value.trim();
    const enfants = form.enfants.value.trim();

    if (nom === "" || matricule === "" || email === "" || telephone === "" || conjoint === "" || enfants === "") {
      errors.push("Tous les champs obligatoires doivent être remplis.");
    }

    // Format email
    if (!email.match(/^[^@]+@[^@]+\.[^@]+$/)) {
      errors.push("Adresse email invalide.");
    }

    // Vérification des fichiers
    const fichiers = ["carte_identite", "certificat_vie", "justificatif_lien"];
    fichiers.forEach(id => {
      const file = form[id].files[0];
      if (!file) {
        errors.push("Tous les documents doivent être téléchargés.");
      } else {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!["pdf", "jpg", "png"].includes(ext)) {
          errors.push("Seuls les fichiers PDF, JPG ou PNG sont autorisés.");
        }
      }
    });

    // Affichage des erreurs
    if (errors.length > 0) {
      e.preventDefault();
      alert(errors.join("\n"));
    }
  });
});
