/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './packages/core/WebApp/resources/views/**/*.blade.php',
    './packages/core/WebApp/resources/assets/**/*.{js,ts,vue}',
  ],
  darkMode: ['class', '[data-theme="ungolmas-dark"]'],
  theme: {
    extend: {
      colors: {
        ugm: {
          bg: 'var(--ugm-bg)',
          card: 'var(--ugm-card)',
          border: 'var(--ugm-border)',
          text: 'var(--ugm-text)',
          muted: 'var(--ugm-muted)',
          primary: 'var(--ugm-primary)',
          accent: 'var(--ugm-accent)',
          gold: 'var(--ugm-gold)',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Outfit', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
