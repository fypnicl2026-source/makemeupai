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
          lilac: "#8b6bb5",
          "lilac-light": "#a78bc7",
          blush: "#fbf6f8",
          "blush-deep": "#f7e8f0",
          gold: "#c9a227",
          ink: "#1f1124",
          muted: "#6f6176",
          line: "#ebd6e2",
        },
      },
      fontFamily: {
        sans: ["Outfit", "system-ui", "sans-serif"],
        display: ["Fraunces", "Georgia", "serif"],
      },
      boxShadow: {
        soft: "0 4px 20px rgba(107, 31, 77, 0.06)",
        elevated: "0 10px 32px rgba(107, 31, 77, 0.1)",
        glow: "0 8px 28px rgba(202, 77, 145, 0.18)",
        "glow-sm": "0 4px 16px rgba(202, 77, 145, 0.14)",
      },
      backgroundImage: {
        "brand-gradient": "linear-gradient(135deg, #ca4d91 0%, #9b2b6c 70%, #7a3d68 100%)",
        "brand-gradient-soft": "linear-gradient(145deg, #f7e8f0 0%, #fbf6f8 55%, #f5f0f3 100%)",
        "hero-mesh":
          "radial-gradient(ellipse 70% 45% at 20% -10%, rgba(202,77,145,0.12), transparent 55%), radial-gradient(ellipse 50% 40% at 90% 10%, rgba(155,43,108,0.08), transparent 50%)",
      },
      borderRadius: {
        "2xl": "1rem",
        "3xl": "1.25rem",
      },
      keyframes: {
        fadeIn: {
          "0%": { opacity: "0", transform: "translateY(10px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        float: {
          "0%, 100%": { transform: "translateY(0)" },
          "50%": { transform: "translateY(-5px)" },
        },
        shimmer: {
          "0%": { backgroundPosition: "200% 0" },
          "100%": { backgroundPosition: "-200% 0" },
        },
      },
      animation: {
        "fade-in": "fadeIn 0.55s ease-out forwards",
        "fade-in-slow": "fadeIn 0.75s ease-out forwards",
        float: "float 5s ease-in-out infinite",
        shimmer: "shimmer 3s linear infinite",
      },
    },
  },
  plugins: [],
};
