const d = document;

export default function validateRangeFilter(e) {
  console.log("rango");
  if (
    !e.target.matches("#start-year") &&
    !e.target.matches("#start-year *") &&
    !e.target.matches("#end-year") &&
    !e.target.matches("#end-year *")
  )
    return;

  console.log(e.target);
}
