import getPuestos from "../helpers/getPuestos.js";

const d = document;

export default async function setPuestos(data) {
  if (!location.pathname.includes("crear-solicitud.html")) return;

  const $selectPuestos = d.getElementById("solicitud-puesto");

  let depto = data.departamento;
  let puesto = data.puesto;
  let idUser = data.id;

  let json = await getPuestos(depto, puesto, idUser);

  let puestos = json.Puestos;

  const $fragment = d.createDocumentFragment();

  puestos.forEach((el) => {
    const $option = d.createElement("option");

    $option.textContent = `${el["descripcion"]}`;
    $option.value = el["id_archivo"];

    $fragment.appendChild($option);
  });

  $selectPuestos.appendChild($fragment);

  d.getElementById("solicitud-solicitante").value = data.id;
  d.getElementById("solicitud-solicitante").setAttribute(
    "data-puesto",
    data.puesto
  );
}
