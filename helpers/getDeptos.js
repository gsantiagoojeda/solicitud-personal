import app from "./app.js";

const d = document;

const { DEPTOS } = app;
export default async function getDeptos() {
  try {
    let res = await fetch(`${DEPTOS}getDeptos.php`),
      json = await res.json();

    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    return json;
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al obtener departamentos`);
  }
}
