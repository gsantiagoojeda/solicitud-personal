const d = document;

export default function validateRangeFilter(e) {
  if (
    !e.target.matches("#start-year") &&
    !e.target.matches("#start-year *") &&
    !e.target.matches("#end-year") &&
    !e.target.matches("#end-year *")
  )
    return;

  console.log(e.target);
}
