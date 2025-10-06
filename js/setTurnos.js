import getTurnos from "../helpers/getTurnos.js";

const d = document;

export default async function SetTurnos() {
  if (
    !location.pathname.includes("crear-solicitud.html") &&
    !location.pathname.includes("autorizar-solicitud.html")
  )
    return;

  const $selectTurno = d.getElementById("solicitud-horario");

  let turnos = await getTurnos();

  turnos = JSON.parse(turnos);

  console.log(turnos);

  const $fragment = d.createDocumentFragment();

  turnos.forEach((el) => {
    const $option = d.createElement("option");

    $option.textContent = `${el["hora_inicio"]} a ${el["hora_termino"]}`;
    $option.value = el["id_turnos"];

    $fragment.appendChild($option);
  });

  $selectTurno.appendChild($fragment);
}
