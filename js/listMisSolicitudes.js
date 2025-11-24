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

    json.data.forEach((el) => {
      console.log(el);
      let $clone = document.importNode($template, true);

      $clone
        .querySelector("[data-td-id]")
        .setAttribute("data-td-id", el.solicitud_id);
      $clone.querySelector("[data-name]").textContent =
        el.usuario_nombre_completo;
      $clone.querySelector("[data-empresa]").textContent = el.usuario_empresa;
      $clone.querySelector("[data-depto]").textContent =
        el.usuario_departamento_nombre;
      $clone.querySelector("[data-puesto]").textContent =
        el.usuario_puesto.length > 40
          ? el.usuario_puesto.substring(0, 40) + "..."
          : el.usuario_puesto;
      $clone.querySelector("[data-vacante]").textContent =
        el.solicitud_nombre_puesto;
      const statusElement = $clone.querySelector("[data-status]");

      if (el.solicitud_autorizacion1 === "") {
        statusElement.textContent = "Pendiente";
        statusElement.classList.add("bg-green-600");
      } else if (el.solicitud_autorizacion1 === "Rechazada") {
        statusElement.textContent = el.solicitud_autorizacion1;
        statusElement.classList.add("bg-red-600");
      } else {
        statusElement.textContent = "Autorizada";
        statusElement.classList.add("bg-green-600");
      }
      $clone.querySelector("[data-auth]").textContent =
        el.autorizador1_nombre_completo === ""
          ? "-"
          : el.autorizador1_nombre_completo;
      $clone.querySelector("[data-authpuesto]").textContent =
        el.autorizador1_puesto === ""
          ? "-"
          : el.autorizador1_puesto.length > 40
          ? el.autorizador1_puesto.substring(0, 40) + "..." // Trunca y añade "..."
          : el.autorizador1_puesto;
      $clone.querySelector("[data-authdate]").textContent =
        el.solicitud_date_autorizacion1 === ""
          ? "-"
          : el.solicitud_date_autorizacion1;

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
