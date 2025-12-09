import app from "../helpers/app.js";

const d = document;

export default function menu(e) {
  const { DOMAIN } = app;
  const button = e.target.closest('[id^="btn-go-"]');
  if (!button) return;

  const key = button.id;
  console.log(key);
  switch (key) {
    case "btn-go-create":
      location.href = ` ${DOMAIN}crear-solicitud.html`;
      break;
    case "btn-go-history":
      location.href = ` ${DOMAIN}ver-solicitudes.html`;
      break;
    // case "btn-go-historyr":
    //   location.href = "";
    //   break;
    case "btn-go-auth":
      location.href = ` ${DOMAIN}autorizar-solicitudes.html`;
      break;
    case "btn-go-approved":
      location.href = ` ${DOMAIN}solicitudes-autorizadas.html`;
      break;
    case "btn-go-users":
      location.href = ` ${DOMAIN}usuarios.html`;
      break;
  }
}
