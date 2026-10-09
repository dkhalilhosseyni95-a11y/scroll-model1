import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        // New design system — HORIZON PROPERTIES
        navy: {
          DEFAULT: '#0B1F33',
          deep: '#071524',
          light: '#1A3450',
        },
        ink: '#0A1622',
        champagne: {
          DEFAULT: '#B8966A',
          light: '#D6BD92',
          dark: '#9A7C50',
        },
        ivory: '#FAF7F1',
        cream: '#F4EFE6',
        mist: '#EFEAE1',
        slate: {
          DEFAULT: '#5A6470',
          light: '#8A8F99',
        },
        line: '#E6E0D6',

        // Legacy aliases (kept so unrefactored pages still compile)
        void: '#0B1F33',
        parchment: '#FAF7F1',
        stone: {
          DEFAULT: '#5A6470',
          dark: '#8A8F99',
        },
        charcoal: '#E6E0D6',
        amber: {
          DEFAULT: '#B8966A',
          light: '#D6BD92',
          dark: '#9A7C50',
        },
      },
      fontFamily: {
        sans: ['var(--font-montserrat)', 'system-ui', 'sans-serif'],
        serif: ['var(--font-playfair)', 'Georgia', 'serif'],
      },
      fontSize: {
        hero: ['clamp(2.75rem, 8vw, 7rem)', { lineHeight: '1.02', letterSpacing: '-0.03em' }],
      },
      borderRadius: {
        card: '20px',
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
