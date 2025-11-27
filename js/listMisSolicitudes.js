import app from "../helpers/app.js";

const d = document;

export default async function listMisSolicitudes(data) {
  if (!location.pathname.includes("ver-solicitudes.html")) return;
  const userId = data.id;

  const $tbody = d.getElementById("tbody-edit"),
    $template = d.getElementById("template-edit-solicitud").content;
  const { SOLICITUDES } = app;

  try {
    const formData = new FormData();
    formData.append("user-id", userId);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${SOLICITUDES}getMisSolicitudes.php`, options),
      json = await res.json();
    console.log(json);
    if (!res.ok || json.err)
      throw { status: res.status, statusText: res.statusText };

    const $fragment = document.createDocumentFragment();

    json.solicitudes.forEach((el) => {
      console.log(el);
      let $clone = document.importNode($template, true);

      $clone
        .querySelector("[data-td-id]")
        .setAttribute("data-td-id", el.solicitud_id);
      $clone.querySelector("[data-vacante]").textContent =
        el.solicitud_nombre_puesto;
      const status1Element = $clone.querySelector("[data-status1]");
      const status2Element = $clone.querySelector("[data-status2]");

      if (el.solicitud_autorizacion1 === "") {
        status1Element.textContent = "Pendiente";
        status1Element.classList.add("bg-green-600");
      } else if (el.solicitud_autorizacion1 === "Rechazada") {
        status1Element.textContent = el.solicitud_autorizacion1;
        status1Element.classList.add("bg-red-600");
      } else {
        status1Element.textContent = "Autorizada";
        status1Element.classList.add("bg-green-600");
      }
      $clone.querySelector("[data-auth1]").textContent =
        el.autorizador1_nombre_completo === ""
          ? "-"
          : el.autorizador1_nombre_completo;
      $clone.querySelector("[data-auth1puesto]").textContent =
        el.autorizador1_puesto === ""
          ? "-"
          : el.autorizador1_puesto.length > 40
          ? el.autorizador1_puesto.substring(0, 40) + "..." // Trunca y añade "..."
          : el.autorizador1_puesto;
      $clone.querySelector("[data-auth1date]").textContent =
        el.solicitud_date_autorizacion1 === ""
          ? "-"
          : el.solicitud_date_autorizacion1;

      //auth2
      if (el.solicitud_autorizacion2 === "") {
        status2Element.textContent = "Pendiente";
        status2Element.classList.add("bg-green-600");
      } else if (el.solicitud_autorizacion1 === "Rechazada") {
        status2Element.textContent = el.solicitud_autorizacion1;
        status2Element.classList.add("bg-red-600");
      } else {
        status2Element.textContent = "Autorizada";
        status2Element.classList.add("bg-green-600");
      }
      $clone.querySelector("[data-auth2]").textContent =
        el.autorizador2_nombre_completo === ""
          ? "-"
          : el.autorizador2_nombre_completo;
      $clone.querySelector("[data-auth2puesto]").textContent =
        el.autorizador2_puesto === ""
          ? "-"
          : el.autorizador2_puesto.length > 40
          ? el.autorizador2_puesto.substring(0, 40) + "..." // Trunca y añade "..."
          : el.autorizador2_puesto;
      $clone.querySelector("[data-auth2date]").textContent =
        el.solicitud_date_autorizacion2 === ""
          ? "-"
          : el.solicitud_date_autorizacion2;

      $fragment.appendChild($clone);
    });
    $tbody.appendChild($fragment);
    alternarFilas();
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al obtener solicitudes`);
  }
}

function alternarFilas() {
  const filas = document.querySelectorAll("tbody tr");
  filas.forEach((fila, index) => {
    fila.classList.toggle("bg-gray-100", (index + 1) % 2 === 0);
  });
}
