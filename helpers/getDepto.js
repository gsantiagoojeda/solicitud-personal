import app from "./app.js";

const d = document;

const { DEPTOS } = app;
export default async function getDepto(id) {
  try {
    const formData = new FormData();
    formData.append("id", id);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${DEPTOS}getDepto.php`, options),
      json = await res.json();

    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    return json;
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al obtener el departamento`);
  }
}
