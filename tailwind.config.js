/** @type {import('tailwindcss').Config} */
// tailwind.config.js
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    "./node_modules/flowbite/**/*.js" // set up the path to the flowbite package
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('flowbite/plugin') // add the flowbite plugin
  ],
}
