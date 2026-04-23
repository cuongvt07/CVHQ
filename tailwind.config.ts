import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        "space-black": "#050505",
        "electric-blue": "#00D1FF",
        antigravity: {
          surface: "rgba(255, 255, 255, 0.03)",
          border: "rgba(255, 255, 255, 0.1)",
          glow: "rgba(0, 209, 255, 0.15)",
        },
      },
      backgroundImage: {
        "glass-gradient": "linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%)",
        "blue-glow": "radial-gradient(circle at center, rgba(0, 209, 255, 0.15) 0%, transparent 70%)",
        "text-aurora": "linear-gradient(to bottom, #ffffff 0%, rgba(255, 255, 255, 0.8) 50%, rgba(255, 255, 255, 0.4) 100%)",
      },
      animation: {
        "float": "float 6s ease-in-out infinite",
        "pulse-glow": "pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite",
      },
      keyframes: {
        float: {
          "0%, 100%": { transform: "translateY(0)" },
          "50%": { transform: "translateY(-10px)" },
        },
        "pulse-glow": {
          "0%, 100%": { opacity: "1", boxShadow: "0 0 15px rgba(0, 209, 255, 0.3)" },
          "50%": { opacity: "0.7", boxShadow: "0 0 5px rgba(0, 209, 255, 0.1)" },
        },
      },
      backdropBlur: {
        antigravity: "12px",
      },
      borderRadius: {
        "antigravity-pill": "100px",
        "antigravity-card": "24px",
      },
    },
  },
  plugins: [],
};

export default config;
