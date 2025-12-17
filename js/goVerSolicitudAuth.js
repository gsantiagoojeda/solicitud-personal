import app from "../helpers/app.js";

const d = document;

export default function goAutorizarSolicitud(e) {
  if (
    !location.pathname.includes("solicitudes-autorizadas.html") ||
    !e.target.closest("[data-td-id]")
  )
    return;

  const { DOMAIN } = app;
  const $tdParent = e.target.closest("[data-td-id]");
  let id = $tdParent.dataset.tdId;

  sessionStorage.setItem("id_solicitud", id);

  location.href = `${DOMAIN}detailAuthSolicitud.html`;
}
