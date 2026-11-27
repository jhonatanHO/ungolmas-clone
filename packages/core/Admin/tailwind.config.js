/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './packages/core/Admin/resources/views/**/*.blade.php',
    './packages/core/Admin/resources/assets/**/*.{js,ts,vue}',
  ],
  darkMode: ['class', '[data-theme="cmsnet-dark"]'],
  theme: {
    extend: {
      colors: {
        theme: {
          50: 'var(--theme-50)',
          100: 'var(--theme-100)',
          200: 'var(--theme-200)',
          300: 'var(--theme-300)',
          400: 'var(--theme-400)',
          500: 'var(--theme-500)',
          700: 'var(--theme-700)',
          900: 'var(--theme-900)',
          950: 'var(--theme-950)',
        },
      },
    },
  },
  plugins: [],
};
