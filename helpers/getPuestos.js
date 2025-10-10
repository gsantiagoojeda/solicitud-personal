import app from "./app.js";

const d = document;

const { DEPTOS } = app;
export default async function getPuestos(depto, puesto, idUser) {
  try {
    const formData = new FormData();
    formData.append("depto", depto);
    formData.append("puesto", puesto);
    formData.append("id-user", idUser);

    let options = {
      method: "POST",
      body: formData,
    };
    let res = await fetch(`${DEPTOS}getPuestos.php`, options),
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
