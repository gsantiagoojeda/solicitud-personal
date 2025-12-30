import app from "../helpers/app.js";

const d = document;

export default async function createSolicitud(e) {
  if (!e.target.matches("#form-create-solicitud")) return;

  const { SOLICITUDES, DOMAIN } = app;

  const $form = d.getElementById("form-create-solicitud"),
    puesto = $form["solicitud-puesto"].value,
    rp1 = $form["solicitud-rp1"].value,
    rp2 = $form["solicitud-rp2"].value,
    rp3 = $form["solicitud-rp3"].value,
    rp4 = $form["solicitud-rp4"].value,
    trp1 = $form["solicitud-txt-rp1"].value,
    trp2 = $form["solicitud-txt-rp2"].value,
    trp3 = $form["solicitud-txt-rp3"].value,
    trp4 = $form["solicitud-txt-rp4"].value,
    compras = $form["solicitud-txt-compras"].value,
    fechaTentativa = $form["solicitud-fecha-tentativa"].value,
    responsable = $form["solicitud-txt-respgestion"].value,
    numVacantes = $form["solicitud-vacantes"].value,
    sexo = $form["solicitud-sexo"].value,
    estadoCivil = $form["solicitud-estado-civil"].value,
    escolaridad = $form["solicitud-escolaridad"].value,
    edadMinima = $form["solicitud-age-min"].value,
    edadMaxima = $form["solicitud-age-max"].value,
    experiencia = $form["solicitud-experiencia"].value,
    conocimientos = $form["solicitud-txt-conocimientos"].value,
    habilidades = $form["solicitud-txt-habilidades"].value,
    tools = $form["solicitud-txt-tools"].value,
    sueldo = $form["solicitud-sueldo"].value,
    horario = $form["solicitud-horario"].value,
    rolar = $form["solicitud-rolar"].value,
    solicitante = $form["solicitud-solicitante"].value,
    solicitantePuesto =
      $form["solicitud-solicitante"].getAttribute("data-puesto");

  d.querySelector(".load").classList.remove("hidden");
  d.querySelector(".load").style.display = "flex";

  const formData = new FormData();
  formData.append("puesto", puesto);
  formData.append("rp1", rp1);
  formData.append("rp2", rp2);
  formData.append("rp3", rp3);
  formData.append("rp4", rp4);
  formData.append("trp1", trp1);
  formData.append("trp2", trp2);
  formData.append("trp3", trp3);
  formData.append("trp4", trp4);
  formData.append("compras", compras);
  formData.append("fechaTentativa", fechaTentativa);
  formData.append("responsable", responsable);
  formData.append("numVacantes", numVacantes);
  formData.append("sexo", sexo);
  formData.append("estadoCivil", estadoCivil);
  formData.append("escolaridad", escolaridad);
  formData.append("edadMinima", edadMinima);
  formData.append("edadMaxima", edadMaxima);
  formData.append("experiencia", experiencia);
  formData.append("conocimientos", conocimientos);
  formData.append("habilidades", habilidades);
  formData.append("tools", tools);
  formData.append("sueldo", sueldo);
  formData.append("horario", horario);
  formData.append("rolar", rolar);
  formData.append("solicitante", solicitante);
  formData.append("solicitante-puesto", solicitantePuesto);

  // for (const [key, value] of formData.entries()) {
  //   console.log(`${key}: ${value}`);
  // }

  let options = {
    method: "POST",
    body: formData,
  };
  try {
    let res = await fetch(`${SOLICITUDES}createSolicitud.php`, options);
    if (!res.ok) throw { status: res.status, statusText: res.statusText };
    let json = await res.json();
    console.log(json);
    if (!json.err) {
      document.querySelector(".load").style.display = "none";
      alert(`Solicitud creada`);
      location.href = `${DOMAIN}ver-solicitudes.html`;
    } else {
      throw { status: "200", statusText: json.statusText };
    }
  } catch (err) {
    //console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error: ${message}`);
  }
}
