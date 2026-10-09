import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        void: '#080909',
        parchment: '#F7F6F2',
        stone: {
          DEFAULT: '#D9D5CE',
          dark: '#A8A39A',
        },
        charcoal: '#343635',
        amber: {
          DEFAULT: '#E7A45E',
          light: '#F2C49A',
          dark: '#C8894A',
        },
      },
      fontFamily: {
        sans: ['var(--font-montserrat)', 'system-ui', 'sans-serif'],
        serif: ['var(--font-playfair)', 'Georgia', 'serif'],
      },
      fontSize: {
        hero: ['clamp(4rem, 12vw, 14rem)', { lineHeight: '0.9', letterSpacing: '-0.05em' }],
      },
      borderRadius: {
        card: '24px',
      },
      spacing: {
        section: 'clamp(4rem, 8vw, 8rem)',
      },
      animation: {
        'fade-in': 'fadeIn 0.8s ease-out forwards',
        'fade-up': 'fadeUp 0.8s ease-out forwards',
        'draw-line': 'drawLine 1s ease-out forwards',
        'scale-in': 'scaleIn 1s ease-out forwards',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        fadeUp: {
          '0%': { opacity: '0', transform: 'translateY(30px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        drawLine: {
          '0%': { transform: 'scaleY(0)' },
          '100%': { transform: 'scaleY(1)' },
        },
        scaleIn: {
          '0%': { opacity: '0', transform: 'scale(1.05)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
      },
    },
  },
  plugins: [],
};

export default config;
