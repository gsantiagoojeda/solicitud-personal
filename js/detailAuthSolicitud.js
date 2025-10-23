import app from "../helpers/app.js";

const d = document;

export default async function detailAuthSolicitud(data) {
  if (!location.pathname.includes("autorizar-solicitud.html")) return;
  const { DOMAIN, SOLICITUDES } = app;

  let idSolicitud = sessionStorage.getItem("id_solicitud");
  if (!idSolicitud) location.href(`${DOMAIN}autorizar-solicitudes.html`);

  try {
    const formData = new FormData();
    formData.append("id-solicitud", idSolicitud);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${SOLICITUDES}getSolicitud.php`, options),
      json = await res.json();
    console.log(json);
    if (!res.ok || json.err)
      throw { status: res.status, statusText: res.statusText };

    const $form = d.getElementById("form-create-solicitud");
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al obtener solicitud`);
  }
}
