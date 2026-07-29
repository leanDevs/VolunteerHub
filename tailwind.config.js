/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        jci: {
          blue: '#0F4C81',
          dark: '#0A3154',
          light: '#1B75BC',
          accent: '#F5A623',
          bg: '#F4F7FC'
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
