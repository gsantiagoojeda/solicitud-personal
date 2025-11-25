const d = document;

export default function validateRangeFilter(e) {
  if (
    !location.pathname.includes("ver-solicitudes.html") ||
    (!e.target.matches("#start-year") &&
      !e.target.matches("#start-year *") &&
      !e.target.matches("#end-year") &&
      !e.target.matches("#end-year *"))
  ) {
    return;
  }

  const $startYear = d.getElementById("start-year");
  let startYear = $startYear.value;
  const $endYear = d.getElementById("end-year");
  let endYear = $endYear.value;

  if (parseInt(startYear) < parseInt(endYear)) {
    $endYear.classList.remove("border-gray-600");
    $endYear.classList.add("text-red-600");
    $endYear.classList.add("border-red-600");
  }
}
