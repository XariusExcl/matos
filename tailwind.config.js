/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    "./templates/**/**/*.html.twig",
  ],
  blocklist: ['container', 'collapse', 'visible', 'invisible', 'table'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        ink: {
          900: '#0B1622',
          700: '#334155',
          500: '#64748B',
          400: '#94A3B8',
          300: '#CBD5E1',
        },
        brand: {
          50: '#E8F8FE',
          100: '#CCEFFB',
          500: '#00ADEA',
          600: '#0093C8',
          700: '#0A6F96',
        },
        surface: {
          bg: '#F6F8FA',
          muted: '#F1F5F9',
          line: '#E5E9F0',
        },
      },
      boxShadow: {
        card: '0 1px 3px rgba(11,22,34,.06), 0 4px 12px -2px rgba(11,22,34,.05)',
        float: '0 8px 24px -4px rgba(11,22,34,.12)',
      },
    },
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
          "primary-content": "#0B1622",
          "secondary": "#0B1622",
          "secondary-content": "#ffffff",
          "neutral":   "#0B1622",
          "neutral-content": "#ffffff",
          "base-100":  "#ffffff",
          "base-200":  "#F6F8FA",
          "base-300":  "#E5E9F0",
          "base-content": "#0B1622",
          "info":      "#00adea",
          "success":   "#00a87e",
          "warning":   "#ffba10",
          "error":     "#db2e3f",
          "--rounded-box": "1rem",
          "--rounded-btn": "0.75rem",
          "--rounded-badge": "999px",
        },
      },
    ],
  },
}
