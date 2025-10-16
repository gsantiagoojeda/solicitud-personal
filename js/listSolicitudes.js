import app from "../helpers/app.js";

const d = document;

export default async function listSolicitudes(data) {
  if (!location.pathname.includes("autorizar-solicitudes.html")) return;
  const userId = data.id;

  const $tbody = d.getElementById("tbody-create"),
    $template = d.getElementById("template-create-solicitud").content;
  const { SOLICITUDES } = app;

  try {
    const formData = new FormData();
    formData.append("user-id", userId);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${SOLICITUDES}getSolicitudes.php`),
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
      $clone.querySelector("[data-name]").textContent = el.usuario_nombre;
      $clone.querySelector("[data-name]").textContent = el.usuario_nombre;
      $clone.querySelector("[data-mail]").textContent = el.usuario_correo;
      $clone.querySelector("[data-depto]").textContent =
        el.usuario_departamento_nombre;
      $clone.querySelector("[data-puesto]").textContent = el.usuario_puesto;
      $clone.querySelector("[data-vacante]").textContent =
        el.solicitud_nombre_puesto;
      $clone.querySelector("[data-status]").textContent = el.usuario_puesto;

      el.$fragment.appendChild($clone);
    });
    $tbody.appendChild($fragment);
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al obtener solicitudes`);
  }
}
