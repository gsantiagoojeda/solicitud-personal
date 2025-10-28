import app from "../helpers/app.js";

const d = document;

export default async function detailAuthSolicitud(data) {
  if (!location.pathname.includes("autorizar-solicitud.html")) return;
  const { DOMAIN, SOLICITUDES } = app;

  let idSolicitud = sessionStorage.getItem("id_solicitud");
  if (!idSolicitud) location.href(`${DOMAIN}autorizar-solicitudes.html`);

  try {
    const formData = new FormData();
    formData.append("id_solicitud", idSolicitud);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${SOLICITUDES}getSolicitud.php`, options),
      json = await res.json();
    console.log(json);
    if (!res.ok || json.err)
      throw { status: res.status, statusText: res.statusText };
    let solicitud = json.solicitud;

    const $form = d.getElementById("form-create-solicitud");

    $form["puesto"].textContent = solicitud["solicitud_puesto_nombre"];
    $form["puesto"].setAttribute(
      "data-id",
      `${solicitud["solicitud_puesto_id"]}`
    );

    $form["solicitante"].textContent = solicitud["solicitud_solicitante"];
    $form["solicitante"].setAttribute(
      "data-id",
      `${solicitud["solicitud_solicitante_id"]}`
    );

    $form["auth1-name"].textContent = solicitud["solicitud_autorizador1"];
    $form["auth1-name"].setAttribute(
      "data-id",
      `${solicitud["solicitud_autorizador1_id"]}`
    );
    if (solicitud["solicitud_autorizador1_id"]) {
      d.getElementById("container-auths").classList.remove("hidden");
      d.getElementById("container-auth1").classList.remove("hidden");
    }

    $form["auth2-name"].textContent = solicitud["solicitud_autorizador2"];
    $form["auth2-name"].setAttribute(
      "data-id",
      `${solicitud["solicitud_autorizador2_id"]}`
    );
    if (solicitud["solicitud_autorizador2_id"]) {
      d.getElementById("container-auths").classList.remove("hidden");
      d.getElementById("container-auth2").classList.remove("hidden");
    }

    switch (solicitud["solicitud_espacio_trabajo"]) {
      case "si":
        d.querySelector(
          'input[name="solicitud-rp1"][value="si"]'
        ).checked = true;
        break;
      case "no":
        d.querySelector(
          'input[name="solicitud-rp1"][value="no"]'
        ).checked = true;
        break;
      case "n/a":
        d.querySelector(
          'input[name="solicitud-rp1"][value="n/a"]'
        ).checked = true;
        break;
    }

    $form["solicitud-txt-rp1"].value =
      solicitud["solicitud_espacio_trabajo_com"];

    switch (solicitud["solicitud_mobiliario"]) {
      case "si":
        d.querySelector(
          'input[name="solicitud-rp2"][value="si"]'
        ).checked = true;
        break;
      case "no":
        d.querySelector(
          'input[name="solicitud-rp2"][value="no"]'
        ).checked = true;
        break;
      case "n/a":
        d.querySelector(
          'input[name="solicitud-rp2"][value="n/a"]'
        ).checked = true;
        break;
    }

    $form["solicitud-txt-rp2"].value = solicitud["solicitud_mobiliario_com"];

    switch (solicitud["solicitud_equipo_computo"]) {
      case "si":
        d.querySelector(
          'input[name="solicitud-rp3"][value="si"]'
        ).checked = true;
        break;
      case "no":
        d.querySelector(
          'input[name="solicitud-rp3"][value="no"]'
        ).checked = true;
        break;
      case "n/a":
        d.querySelector(
          'input[name="solicitud-rp3"][value="n/a"]'
        ).checked = true;
        break;
    }

    $form["solicitud-txt-rp3"].value =
      solicitud["solicitud_equipo_computo_com"];

    switch (solicitud["solicitud_herramientas"]) {
      case "si":
        d.querySelector(
          'input[name="solicitud-rp4"][value="si"]'
        ).checked = true;
        break;
      case "no":
        d.querySelector(
          'input[name="solicitud-rp4"][value="no"]'
        ).checked = true;
        break;
      case "n/a":
        d.querySelector(
          'input[name="solicitud-rp4"][value="n/a"]'
        ).checked = true;
        break;
    }

    $form["solicitud-txt-rp4"].value = solicitud["solicitud_herramientas_com"];
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al obtener solicitud`);
  }
}
