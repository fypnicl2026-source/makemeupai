/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx,css}"],
  theme: {
    extend: {
      colors: {
        brand: {
          rose: "#ca4d91",
          "rose-light": "#e269a8",
          plum: "#9b2b6c",
          "plum-dark": "#6b1f4d",
          lilac: "#7f56d9",
          "lilac-light": "#9a7be5",
          blush: "#fff7fb",
          "blush-deep": "#ffe8f5",
          gold: "#c9a227",
          ink: "#1f1124",
          muted: "#6f6176",
          line: "#f0dce8",
        },
      },
      fontFamily: {
        sans: ["Inter", "system-ui", "sans-serif"],
      },
      boxShadow: {
        soft: "0 4px 24px rgba(114, 46, 92, 0.08)",
        elevated: "0 12px 40px rgba(114, 46, 92, 0.14)",
        glow: "0 0 40px rgba(202, 77, 145, 0.25)",
        "glow-sm": "0 0 20px rgba(127, 86, 217, 0.2)",
      },
      backgroundImage: {
        "brand-gradient": "linear-gradient(135deg, #ca4d91 0%, #9b2b6c 50%, #7f56d9 100%)",
        "brand-gradient-soft": "linear-gradient(135deg, #ffe8f5 0%, #fff7fb 50%, #f5f0ff 100%)",
        "hero-mesh": "radial-gradient(ellipse 80% 50% at 50% -20%, rgba(202,77,145,0.15), transparent), radial-gradient(ellipse 60% 40% at 100% 0%, rgba(127,86,217,0.1), transparent)",
      },
      borderRadius: {
        "2xl": "1rem",
        "3xl": "1.25rem",
      },
      keyframes: {
        fadeIn: {
          "0%": { opacity: "0", transform: "translateY(8px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        float: {
          "0%, 100%": { transform: "translateY(0)" },
          "50%": { transform: "translateY(-6px)" },
        },
        shimmer: {
          "0%": { backgroundPosition: "200% 0" },
          "100%": { backgroundPosition: "-200% 0" },
        },
      },
      animation: {
        "fade-in": "fadeIn 0.5s ease-out forwards",
        float: "float 4s ease-in-out infinite",
        shimmer: "shimmer 3s linear infinite",
      },
    },
  },
  plugins: [],
};
