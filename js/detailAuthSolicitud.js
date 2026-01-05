import app from "../helpers/app.js";

const d = document;

export default async function detailAuthSolicitud(data) {
  if (!location.pathname.includes("autorizar-solicitud.html")) return;
  const { DOMAIN, SOLICITUDES } = app;

  let idSolicitud = sessionStorage.getItem("id_solicitud");
  let puestoAuth = sessionStorage.getItem("puesto_user");
  if (!idSolicitud) location.href(`${DOMAIN}autorizar-solicitudes.html`);

  try {
    const formData = new FormData();
    formData.append("id_solicitud", idSolicitud);

    let options = {
      method: "POST",
      body: formData,
    };

    let res = await fetch(`${SOLICITUDES}getSolicitud.php`, options),
      json = await res.json();
    console.log(json);
    if (!res.ok || json.err)
      throw { status: res.status, statusText: res.statusText };
    let solicitud = json.solicitud;

    const $form = d.getElementById("form-auth-solicitud");

    $form["solicitud-id"].value = solicitud["solicitud_id"];

    console.log(solicitud["solicitud_puesto_nombre"]);
    d.getElementById("puesto").textContent =
      solicitud["solicitud_puesto_nombre"];
    d.getElementById("puesto").setAttribute(
      "data-id",
      `${solicitud["solicitud_puesto_id"]}`
    );

    d.getElementById("solicitante").textContent =
      solicitud["solicitud_solicitante"];
    d.getElementById("solicitante").setAttribute(
      "data-id",
      `${solicitud["solicitud_solicitante_id"]}`
    );

    d.getElementById("auth1-name").textContent =
      solicitud["solicitud_autorizador1"];
    d.getElementById("auth1-name").setAttribute(
      "data-id",
      `${solicitud["solicitud_autorizador1_id"]}`
    );
    d.getElementById("auth1-date").textContent =
      solicitud["solicitud_date_autorizacion1"];
    if (solicitud["solicitud_autorizador1_id"] !== "") {
      d.getElementById("container-auths").classList.remove("hidden");
      d.getElementById("container-auth1").classList.remove("hidden");
      d.getElementById("container-auth1").classList.remove("lg:hidden");
      d.getElementById("container-auth1").classList.add("lg:flex");
    }

    d.getElementById("auth2-name").textContent =
      solicitud["solicitud_autorizador2"];
    d.getElementById("auth2-name").setAttribute(
      "data-id",
      `${solicitud["solicitud_autorizador2_id"]}`
    );
    d.getElementById("auth2-date").textContent =
      solicitud["solicitud_date_autorizacion2"];
    if (solicitud["solicitud_autorizador2_id"] !== "") {
      d.getElementById("container-auths").classList.remove("hidden");
      d.getElementById("container-auth2").classList.remove("hidden");
      d.getElementById("container-auth2").classList.remove("lg:hidden");
      d.getElementById("container-auth2").classList.add("lg:flex");
    }

    switch (solicitud["solicitud_espacio_trabajo"]) {
      case "si":
        d.querySelector(
          'input[name="solicitud-rp1"][value="si"]'
        ).checked = true;
        break;
      case "no":
        d.querySelector(
          'input[name="solicitud-rp1"][value="no"]'
        ).checked = true;
        break;
      case "n/a":
        d.querySelector(
          'input[name="solicitud-rp1"][value="n/a"]'
        ).checked = true;
        break;
    }

    $form["solicitud-txt-rp1"].value =
      solicitud["solicitud_espacio_trabajo_com"];

    switch (solicitud["solicitud_mobiliario"]) {
      case "si":
        d.querySelector(
          'input[name="solicitud-rp2"][value="si"]'
        ).checked = true;
        break;
      case "no":
        d.querySelector(
          'input[name="solicitud-rp2"][value="no"]'
        ).checked = true;
        break;
      case "n/a":
        d.querySelector(
          'input[name="solicitud-rp2"][value="n/a"]'
        ).checked = true;
        break;
    }

    $form["solicitud-txt-rp2"].value = solicitud["solicitud_mobiliario_com"];

    switch (solicitud["solicitud_equipo_computo"]) {
      case "si":
        d.querySelector(
          'input[name="solicitud-rp3"][value="si"]'
        ).checked = true;
        break;
      case "no":
        d.querySelector(
          'input[name="solicitud-rp3"][value="no"]'
        ).checked = true;
        break;
      case "n/a":
        d.querySelector(
          'input[name="solicitud-rp3"][value="n/a"]'
        ).checked = true;
        break;
    }

    $form["solicitud-txt-rp3"].value =
      solicitud["solicitud_equipo_computo_com"];

    switch (solicitud["solicitud_herramientas"]) {
      case "si":
        d.querySelector(
          'input[name="solicitud-rp4"][value="si"]'
        ).checked = true;
        break;
      case "no":
        d.querySelector(
          'input[name="solicitud-rp4"][value="no"]'
        ).checked = true;
        break;
      case "n/a":
        d.querySelector(
          'input[name="solicitud-rp4"][value="n/a"]'
        ).checked = true;
        break;
    }

    $form["solicitud-txt-rp4"].value = solicitud["solicitud_herramientas_com"];

    $form["solicitud-txt-compras"].value =
      solicitud["solicitud_compras_necesarias"];

    $form["solicitud-fecha-tentativa"].value =
      solicitud["solicitud_fecha_tentativa"];

    $form["solicitud-txt-respgestion"].value =
      solicitud["solicitud_responsable"];

    $form["solicitud-vacantes"].value = solicitud["solicitud_num_vacantes"];

    $form["solicitud-sexo"].value = solicitud["solicitud_sexo"];

    $form["solicitud-estado-civil"].value = solicitud["solicitud_estado_civil"];

    $form["solicitud-escolaridad"].value = solicitud["solicitud_escolaridad"];

    $form["solicitud-age-min"].value = solicitud["solicitud_edad_min"];

    $form["solicitud-age-max"].value = solicitud["solicitud_edad_max"];

    $form["solicitud-experiencia"].value = solicitud["solicitud_experiencia"];

    $form["solicitud-txt-conocimientos"].value =
      solicitud["solicitud_conocimientos"];

    $form["solicitud-txt-habilidades"].value =
      solicitud["solicitud_habilidades"];

    $form["solicitud-txt-tools"].value = solicitud["solicitud_tools"];

    $form["solicitud-sueldo"].value = solicitud["solicitud_sueldo_id"];

    $form["solicitud-horario"].value = solicitud["solicitud_horario_id"];

    $form["solicitud-rolar"].value =
      solicitud["solicitud_rolar"] === 1 ? "si" : "no";

    let levelAuth1 = solicitud["solicitud_autorizacion1"];
    let levelAuth2 = solicitud["solicitud_autorizacion2"];
    let levelAuth3 = solicitud["solicitud_autorizacion3"];

    const $btnAuth = d.getElementById("btn-autorizar-solicitud");
    const $btnRechazar = d.getElementById("btn-rechazar-solicitud");
    const $btnReclutar = d.getElementById("btn-reclutar-solicitud");
    console.log("kevel:", levelAuth1);

    if (puestoAuth.includes("Director")) {
      if (levelAuth1 === "") {
        d.getElementById("auth-level").textContent = " 1";
        $btnAuth.setAttribute("data-level", "1");
        $btnRechazar.setAttribute("data-level", "1");
      } else {
        d.getElementById("auth-contain").style.display = "none";
        const $inputs = document.querySelectorAll("input");

        $inputs.forEach((input) => {
          input.disabled = true; // Esto agrega el atributo 'disabled'
        });
      }
    }

    if (puestoAuth === "Gerente de Recursos Humanos") {
      if (levelAuth2 === "") {
        d.getElementById("auth-level").textContent = " 2";
        $btnAuth.setAttribute("data-level", "2");
        d;
        $btnRechazar.setAttribute("data-level", "2");
      } else {
        d.getElementById("auth-contain").style.display = "none";
        const $inputs = document.querySelectorAll("input");

        $inputs.forEach((input) => {
          input.disabled = true; // Esto agrega el atributo 'disabled'
        });
      }
    }

    if (puestoAuth.includes("Reclutador")) {
      if (levelAuth3 === "") {
        d.getElementById("auth-level").textContent = "Reclutar";
        $btnAuth.setAttribute("data-level", "3");
        $btnRechazar.setAttribute("data-level", "3");
      } else {
        d.getElementById("auth-contain").style.display = "none";
      }
      const $inputs = document.querySelectorAll("input");

      $inputs.forEach((input) => {
        input.disabled = true; // Esto agrega el atributo 'disabled'
      });
    }
  } catch (err) {
    console.log(err);
    let message = err.statusText || "ocurrio un error";
    console.log(` error ${err.status} : ${message}`);
    alert(` ocurrio un error al obtener solicitud`);
  }
}
