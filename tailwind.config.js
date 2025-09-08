/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./src/**/*.{html,js,php}",
    "./customer/**/*.{html,js,php}",
    "./*.php",
    "./*.html",
  ],
  safelist: [
    "hidden",
    "block",
    "grid",
    "flex",
    "scale-y-0",
    "scale-y-100",
    "opacity-0",
    "opacity-100",
    "transform",
    "transition-all",
    "duration-300",
    "ease-in-out",
  ],
  theme: {
    extend: {
      animation: {
        "fade-in": "fadeIn 0.3s ease-out",
        "slide-up": "slideUp 0.3s ease-out",
        "pulse-slow": "pulse 3s infinite",
      },
      keyframes: {
        fadeIn: {
          "0%": { opacity: "0" },
          "100%": { opacity: "1" },
        },
        slideUp: {
          "0%": { transform: "translateY(20px)", opacity: "0" },
          "100%": { transform: "translateY(0)", opacity: "1" },
        },
      },
    },
  },
  plugins: [],
};
