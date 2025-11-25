import obtenerJWT from "../helpers/obtenerJWT.js";
import createSolicitud from "./createSolicitud.js";
import goAutorizarSolicitud from "./goAutorizarSolicitud.js";
import goHome from "./goHome.js";
import goBack from "./goBack.js";
import goVerSolicitud from "./goVerSolicitud.js";
import infoMenu from "./infoMenu.js";
import menu from "./menu.js";
import { pushHtml } from "./pushHtml.js";
import setPuestos from "./setPuestos.js";
import setSueldos from "./setSueldos.js";
import setTurnos from "./setTurnos.js";
import listSolicitudes from "./listSolicitudes.js";
import multiselect from "./multiselect.js";
import detailAuthSolicitud from "./detailAuthSolicitud.js";
import autorizarSolicitud from "./autorizarSolicitud.js";
import goMiSolicitud from "./goMiSolicitud.js";
import listMisSolicitudes from "./listMisSolicitudes.js";
import validateRangeFilter from "./validateRangeFilter.js";

const d = document;

d.addEventListener("click", (e) => {
  goBack(e);
  goHome(e);
  menu(e);
  goAutorizarSolicitud(e);
  goVerSolicitud(e);
  goMiSolicitud(e);
  autorizarSolicitud(e);
});

d.addEventListener("submit", (e) => {
  e.preventDefault();
  createSolicitud(e);
});

d.addEventListener("change", (e) => {
  console.log(e);
  validateRangeFilter(e);
});

d.addEventListener("DOMContentLoaded", async (e) => {
  const data = await validarJWT();
  await pushHtml();
  infoMenu(data);
  setPuestos(data);
  setTurnos();
  setSueldos();
  multiselect();
  listSolicitudes(data);
  listMisSolicitudes(data);
  detailAuthSolicitud(data);
});

async function validarJWT() {
  const data = await obtenerJWT();
  if (!data) {
    alert("No se valido la sesión");
    //location.href = "https://gpoalze.cloud/";
  }

  let puesto = data.puesto.toLowerCase();
  if (!puesto.includes("gerente") && !puesto.includes("director")) {
    alert("No tienes los permisos para entrar a este módulo");
    // location.href = "http://gpoalze.cloud/";
  }
  return data;
}

//mLh.3WN]8y
//paswword uusario bd
