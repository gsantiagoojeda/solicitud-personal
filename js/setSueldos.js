import getSueldos from "../helpers/getSueldos.js";

const d = document;

export default async function SetSueldos() {
  if (
    !location.pathname.includes("crear-solicitud.html") &&
    !location.pathname.includes("autorizar-solicitud.html")
  )
    return;

  const $selectSueldo = d.getElementById("solicitud-sueldo");

  let sueldos = await getSueldos();

  sueldos = JSON.parse(sueldos);

  console.log(sueldos);

  const $fragment = d.createDocumentFragment();

  sueldos.forEach((el) => {
    const $option = d.createElement("option");

    $option.textContent = `${el["sueldo_nombre"]}: ${el["sueldo_cantidad"]}`;
    $option.value = el["sueldo_id"];

    $fragment.appendChild($option);
  });

  $selectSueldo.appendChild($fragment);
}
