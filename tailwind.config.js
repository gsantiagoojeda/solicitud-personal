/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.html", // si tienes HTML en la raíz
    "./js/**/*.js", // si tus JS están en /js
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Nunito", "ui-sans-serif", "system-ui"],
      },
    },
  },
  plugins: [],
};
