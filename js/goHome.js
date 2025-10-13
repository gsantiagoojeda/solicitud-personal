const d = document;

export default function goHome(e) {
  if (!e.target.matches("#go-home") && !e.target.matches("#go-home *")) return;

  location.href = "https://gpoalze.cloud/inicio.php";
}
