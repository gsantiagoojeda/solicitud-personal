import app from "./app.js";
const { API } = app;

export default async function obtenerJWT() {
  try {
    const response = await fetch(`${API}verificarJWT.php`, {
      method: "GET",
      credentials: "include",
    });

    const json = await response.json();
    const data = json.datos;
    console.log(data);
    if (json.success) {
      console.log("Usuario logueado:", data.id);
      return data;
    } else {
      console.warn("No válido:", data.message);
      return null;
    }
  } catch (error) {
    console.error("Error:", error);
  }
}
