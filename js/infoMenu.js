const d = document;

export default function infoMenu(data) {
  if (!location.pathname.includes("menu.html")) return;

  const $nombre = d.getElementById("menu-nombre"),
    $puesto = d.getElementById("menu-puesto");

  $nombre.textContent = `${data.nombre} ${data["apellido_paterno"]}`;
  $puesto.textContent = data.puesto;
  sessionStorage.setItem("id_user", data.id);
}
