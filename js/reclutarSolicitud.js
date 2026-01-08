import app from "../helpers/app.js";

const d = document;

export default async function reclutarSolicitud(e) {
  if (!e.target.matches("#btn-reclutar-solicitud")) return;

  const { SOLICITUDES, DOMAIN } = app;

  const $form = d.getElementById("form-auth-solicitud"),
    id = $form["solicitud-id"].value,
    idAutorizador = sessionStorage.getItem("id_user"),
    level = e.target.getAttribute("data-level");

  d.querySelector(".load").classList.remove("hidden");
  d.querySelector(".load").style.display = "flex";

  const formData = new FormData();
  formData.append("id", id);
  formData.append("autorizador", idAutorizador);
  formData.append("level", level);

  // for (const [key, value] of formData.entries()) {
  //   console.log(`${key}: ${value}`);
  // }

  let options = {
    method: "POST",
    body: formData,
  };
  try {
    let res = await fetch(`${SOLICITUDES}reclutarSolicitud.php`, options);
    if (!res.ok) throw { status: res.status, statusText: res.statusText };
    let json = await res.json();
    console.log(json);
    if (!json.err) {
      document.querySelector(".load").style.display = "none";
      alert(`Solicitud${status}`);
      location.href = `${DOMAIN}autorizar-solicitudes.html`;
    } else {
      throw { status: "200", statusText: json.statusText };
    }
  } catch (err) {
    //console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error: ${message}`);
  }
}
