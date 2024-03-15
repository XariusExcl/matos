/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    "./templates/**/**/*.html.twig",
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('daisyui'),
    require('@tailwindcss/typography'),
  ],
  daisyui: {
    themes: [
      {
        mytheme: {
          "primary":   "#00adea",
          "secondary": "#327B96",
          "neutral":   "#322d2d",
          "base-100":  "#f0f8ff",
          "base-200":  "#E5F3FF",
          "base-300":  "#D6EBFC",
          "info":      "#00cdf8",
          "success":   "#00be8f",
          "warning":   "#ffba10",
          "error":     "#db2e3f",
        },
      },
    ],
  },
}

