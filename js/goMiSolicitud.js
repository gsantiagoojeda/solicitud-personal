import app from "../helpers/app.js";

const d = document;

export default function goMiSolicitud(e) {
  if (
    !location.pathname.includes("ver-solicitudes.html") ||
    !e.target.closest("[data-td-id]")
  )
    return;

  const { DOMAIN } = app;
  const $tdParent = e.target.closest("[data-td-id]");
  let id = $tdParent.dataset.tdId;

  sessionStorage.setItem("id_mi_solicitud", id);

  location.href = `${DOMAIN}mi-solicitud.html`;
}
