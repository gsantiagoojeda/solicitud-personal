const d = document;

export default function goVerSolicitud(e) {
  if (
    !location.pathname.includes("ver-solicitudes.html") ||
    !e.target.closest("[data-td-id]")
  )
    return;
  const $tdParent = e.target.closest("[data-td-id]");
  let id = $tdParent.dataset.tdId;

  console.log(id);
}
