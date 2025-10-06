const d = document;

export default function goBack(e) {
  if (!e.target.matches("#go-back") && !e.target.matches("#go-back *")) return;

  history.back();
}
