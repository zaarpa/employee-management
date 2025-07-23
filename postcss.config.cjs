// Please rename this file to postcss.config.cjs for compatibility with Vite when using 'type': 'module' in package.json.
module.exports = {
    plugins: [require("@tailwindcss/postcss")(), require("autoprefixer")],
};
