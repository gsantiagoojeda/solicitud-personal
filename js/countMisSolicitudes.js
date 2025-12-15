import app from "../helpers/app";

const d = document;

export default async function countMisSolicitudes(data) {
  if (!location.pathname.includes("menu.html")) return;

  try {
    const { SOLICITUDES, DOMAIN } = app;
    const $count = d.getElementById("count-mis-pendientes");
    const $container = d.getElementById("container-mis-pendientes");

    const userId = data.id;

    const formData = new FormData();
    formData.append("user-id", userId);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${SOLICITUDES}countMisSolicitudes.php`, options),
      json = await res.json();
    console.log(json);
    if (!res.ok || json.err)
      throw { status: res.status, statusText: res.statusText };
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al contar solicitudes`);
  }
}
