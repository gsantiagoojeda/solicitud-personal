import app from "../helpers/app.js";

const d = document;

export default async function listSolicitudesAuth(data = null, e = null) {
  if (!location.pathname.includes("solicitudes-autorizadas.html")) return;
  if (e !== null && !e.target.matches("#btn-apply-filters")) return;

  sessionStorage.removeItem("id_solicitud");

  const userId = data.id;

  const $tbody = d.getElementById("tbody-create"),
    $template = d.getElementById("template-item-solicitud").content,
    $templateEmpty = d.getElementById("template-tr-empty").content;
  const { SOLICITUDES } = app;

  try {
    const filterAuth = d.getElementById("status-autorizadas").checked;
    const filterPend = d.getElementById("status-pendientes").checked;
    const filterRech = d.getElementById("status-rechazadas").checked;
    const filterYearStart = d.getElementById("start-year").value;
    const filterYearEnd = d.getElementById("end-year").value;
    const formData = new FormData();
    formData.append("user-id", userId);
    formData.append("filterAuth", filterAuth);
    formData.append("filterPend", filterPend);
    formData.append("filterRech", filterRech);
    formData.append("filterYearStart", filterYearStart);
    formData.append("filterYearEnd", filterYearEnd);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${SOLICITUDES}getSolicitudesAuth.php`, options),
      json = await res.json();
    console.log(json);
    if (!res.ok || json.err)
      throw { status: res.status, statusText: res.statusText };

    const $fragment = document.createDocumentFragment();
    const solicitudes = json.data;

    if (solicitudes.length > 0) {
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
        const status2Element = $clone.querySelector("[data-status2]");

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
      $tbody.innerHTML = "";
      $tbody.appendChild($fragment);
      alternarFilas();
    } else {
      $tbody.innerHTML = "";
      let $clone = document.importNode($templateEmpty, true);
      $tbody.appendChild($clone);
    }
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
