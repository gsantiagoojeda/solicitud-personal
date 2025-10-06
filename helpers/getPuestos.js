import app from "./app.js";

const d = document;

const { DEPTOS } = app;
export default async function getPuestos(depto) {
  try {
    const formData = new FormData();
    formData.append("depto", depto);

    let options = {
      method: "POST",
      body: formData,
    };
    let res = await fetch(`${DEPTOS}getPuestos.php`, depto),
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
