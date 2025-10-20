export default function multiselect() {
  const multiselects = document.querySelectorAll(".multi-options");

  multiselects.forEach((wrapper) => {
    const dataKey = wrapper.dataset.options;
    const toggle = wrapper.querySelector(".dropdown-toggle");
    const searchInput = wrapper.querySelector(".search-input");
    const dropdown = wrapper.querySelector(".dropdown-options");
    const tagsContainer = wrapper.querySelector(".selected-tags");
    const hiddenInput = wrapper.querySelector("input[type='hidden']");

    // Datos reales
    const datasets = {
      depto: ["Ventas", "Compras", "Finanzas", "IT", "Recursos Humanos"],
      clientes: [
        "Nudental SA de CV",
        "JORGE RAMIREZ RAFAEL (Veladoras San Jorge)",
        "ALIMENTOS Y CEREALES SA DE CV",
        "BEAUTY ARMOUR TRADING",
        "TIENDAS TRES B, SA DE CV",
      ],
      empleado: [
        { id: "u1", nombre: "Bertha Jones" },
        { id: "u2", nombre: "Garrett Scott" },
        { id: "u3", nombre: "Adrian Malone" },
        { id: "u4", nombre: "Victor Schmidt" },
        { id: "u5", nombre: "Sylvia Fernandez" },
        { id: "u6", nombre: "Tillie Strickland" },
        { id: "u7", nombre: "Bess Pena" },
        { id: "u8", nombre: "Clifford Banks" },
      ],
    };

    const data = datasets[dataKey] || [];
    let selected = [];

    // Mostrar / ocultar dropdown
    toggle.addEventListener("click", (e) => {
      dropdown.classList.toggle("hidden");
      searchInput.focus();
      renderOptions();
    });

    // Cerrar si haces clic fuera
    document.addEventListener("click", (e) => {
      if (!wrapper.contains(e.target)) {
        dropdown.classList.add("hidden");
      }
    });

    // Filtro
    searchInput.addEventListener("input", () => {
      renderOptions();
    });

    // Renderiza opciones filtradas
    function renderOptions() {
      const query = searchInput.value.toLowerCase();

      const filtered = data.filter((item) => {
        const nombre = typeof item === "string" ? item : item.nombre;
        return (
          nombre.toLowerCase().includes(query) &&
          !selected.some((sel) => sel.id === (item.id ?? item))
        );
      });

      dropdown.innerHTML = "";

      if (filtered.length === 0) {
        dropdown.innerHTML =
          '<li class="px-4 py-2 text-gray-400">Sin resultados</li>';
        return;
      }

      filtered.forEach((item) => {
        const nombre = typeof item === "string" ? item : item.nombre;
        const id = typeof item === "string" ? item : item.id;

        const li = document.createElement("li");
        li.textContent = nombre;
        li.className = "px-4 py-2 hover:bg-blue-100 cursor-pointer text-sm";
        li.addEventListener("click", () => {
          selected.push({ id, nombre });
          updateTags();
          searchInput.value = "";
          renderOptions();
        });
        dropdown.appendChild(li);
      });
    }

    // Actualizar etiquetas y campo oculto
    function updateTags() {
      tagsContainer.innerHTML = "";
      selected.forEach(({ id, nombre }) => {
        const tag = document.createElement("span");
        tag.className =
          "bg-blue-500 text-white px-2 py-1 text-sm rounded-full flex items-center gap-1";
        tag.innerHTML = `
          ${nombre}
          <button type="button" class="hover:text-gray-300" data-id="${id}">&times;</button>
        `;

        tag.querySelector("button").addEventListener("click", () => {
          selected = selected.filter((item) => item.id !== id);
          updateTags();
          renderOptions();
        });

        tagsContainer.appendChild(tag);
      });

      // Guarda solo los ID separados por coma
      hiddenInput.value = selected.map((item) => item.id).join(",");
    }
  });
}
