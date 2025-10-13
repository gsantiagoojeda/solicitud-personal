import getPuestos from "../helpers/getPuestos.js";

const d = document;

export default async function setPuestos(data) {
  if (
    !location.pathname.includes("crear-solicitud.html") &&
    !location.pathname.includes("autorizar-solicitud.html")
  )
    return;

  const $selectPuestos = d.getElementById("solicitud-puesto");

  let depto = data.departamento;
  let puesto = data.puesto;
  let idUser = data.id;

  let json = await getPuestos(depto, puesto, idUser);

  // json = JSON.parse(puestos);
  console.log(json);
  let puestos = puestos.puestos;

  console.log(puestos);

  const $fragment = d.createDocumentFragment();

  sueldos.forEach((el) => {
    const $option = d.createElement("option");

    $option.textContent = `${el["nombre"]}`;
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
