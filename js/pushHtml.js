const d = document;

export async function pushHtml() {
  const includes = d.querySelectorAll("[data-include]");

  for (let el of includes) {
    try {
      const res = await fetch(el.getAttribute("data-include"), {
        method: "GET",
        headers: { "content-type": "text/html; charset=utf-8" },
      });

      if (!res.ok) throw new Error(`HTTP ${res.status}`);

      const html = await res.text();
      el.outerHTML = html;
    } catch (err) {
      console.error("Error al cargar include:", err);
      el.outerHTML = "Ocurrió un error al cargar";
    }
  }
}
