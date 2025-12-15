const d = document;

export default function infoMenu(data) {
  const $nombre = d.getElementById("menu-nombre"),
    $puesto = d.getElementById("menu-puesto");

  $nombre.textContent = `${data.nombre} ${data["apellido_paterno"]}`;
  $puesto.textContent = data.puesto;
  sessionStorage.setItem("id_user", data.id);
  sessionStorage.setItem("puesto_user", data.puesto);

  if (!location.pathname.includes("menu.html")) return;

  const $btnGoAuth = d.getElementById("btn-go-auth");
  const $btnGoApproved = d.getElementById("btn-go-approved");

  if (
    data.puesto.includes("Director") ||
    data.puesto === "Gerente de Recursos Humanos"
  ) {
    $btnGoAuth.setAttribute("data-active", "true");
    $btnGoAuth.style.opacity = "1";
    $btnGoApproved.setAttribute("data-active", "true");
    $btnGoApproved.style.opacity = "1";
  } else {
    if (data.puesto.includes("Reclutador")) {
      $btnGoApproved.setAttribute("data-active", "true");
      $btnGoApproved.style.opacity = "1";
      $btnGoAuth.style.opacity = "0.5";
    } else {
      $btnGoApproved.style.opacity = "0.5";
      $btnGoAuth.style.opacity = "0.5";
    }
  }
}
