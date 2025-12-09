const d = document;

export default function validateRangeFilter(e) {
  if (
    !e.target.matches("#start-year") &&
    !e.target.matches("#start-year *") &&
    !e.target.matches("#end-year") &&
    !e.target.matches("#end-year *")
  ) {
    return;
  }

  const $btnFilters = d.getElementById("btn-apply-filters");
  const $startYear = d.getElementById("start-year");
  let startYear = $startYear.value;
  const $endYear = d.getElementById("end-year");
  let endYear = $endYear.value;

  if (parseInt(startYear) > parseInt(endYear)) {
    $endYear.classList.remove("border-gray-600");
    $endYear.classList.add("text-red-600");
    $endYear.classList.add("border-red-600");
    $btnFilters.setAttribute("disabled", true);
    $btnFilters.classList.add("opacity-50");
    $btnFilters.classList.add("cursor-not-allowed");
  } else {
    $endYear.classList.add("border-gray-600");
    $endYear.classList.remove("text-red-600");
    $endYear.classList.remove("border-red-600");
    $btnFilters.removeAttribute("disabled");
    $btnFilters.classList.remove("opacity-50");
    $btnFilters.classList.remove("cursor-not-allowed");
  }
}
