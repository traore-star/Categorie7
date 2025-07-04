// assets/js/script.js
function toggleAyantDroitOptions() {
  const type = document.getElementById("type_utilisateur").value;
  const block = document.getElementById("ayant_droit_block");
  const ayantDroit = document.getElementById("ayant_droit");

  if (type === "retraite") {
    block.style.display = "block";
    ayantDroit.setAttribute("required", "required");
  } else {
    block.style.display = "none";
    ayantDroit.value = "";
    ayantDroit.removeAttribute("required");
  }
}
