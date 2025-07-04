// confirmation.js

let seconds = 10;
const countdownEl = document.getElementById("countdown");
const redirectURL = "../index.php";

const interval = setInterval(() => {
  seconds--;
  countdownEl.textContent = seconds;

  if (seconds <= 0) {
    clearInterval(interval);
    window.location.href = redirectURL;
  }
}, 1000);
