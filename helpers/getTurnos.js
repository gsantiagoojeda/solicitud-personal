import app from "./app.js";

const d = document;

const { TURNOS } = app;
export default async function getTurnos() {
  try {
    let res = await fetch(`${TURNOS}getTurnos.php`),
      json = await res.json();

    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    return json;
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al obtener turnos`);
  }
}
