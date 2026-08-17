/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./layout/**/*.php",
    "./*.php",
    "./includes/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        'nile-primary': '#0044B9',      // Primary Blue
        'nile-secondary': '#0087F2',    // Secondary Blue
        'nile-dark': '#001E72',         // Dark Blue
        'nile-light': '#0080ED',        // Light Blue
        'nile-white': '#FFFFFF',        // White
      }
    },
  },
  plugins: [],
}