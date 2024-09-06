/* eslint-disable import/no-extraneous-dependencies */
const postcssImport = require('postcss-import');
const tailwindNesting = require('tailwindcss/nesting');
const tailwind = require('tailwindcss');
const autoprefixer = require('autoprefixer');

module.exports = {
    plugins: [
        postcssImport,
        tailwindNesting,
        tailwind,
        autoprefixer,
    ],
};
