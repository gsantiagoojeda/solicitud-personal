import app from "../helpers/app.js";

const d = document;

export default async function countPendAutorizar(data) {
  if (!location.pathname.includes("menu.html")) return;

  try {
    const { SOLICITUDES, DOMAIN } = app;
    const $count = d.getElementById("count-pendientes");
    const $container = d.getElementById("container-pendientes");

    const userId = data.id;
    const userPuesto = data.puesto;

    const formData = new FormData();
    formData.append("user-id", userId);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${SOLICITUDES}countSolicitudes.php`, options),
      json = await res.json();
    console.log(json);
    if (!res.ok || json.err)
      throw { status: res.status, statusText: res.statusText };
    $count.textContent = `${json["data"]}`;
    if (
      userPuesto.includes("Director") ||
      userPuesto === "Gerente de Recursos Humanos"
    )
      $container.style.opacity = "1";
    else {
      $container.style.opacity = "0.5";
      $count.textContent = "-";
    }
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al contar solicitudes`);
  }
}
