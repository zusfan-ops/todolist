/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                ink: { 900: '#141B2E', 700: '#2A3550', 500: '#4A5670', 300: '#9AA3B8', 100: '#E7EAF1', 50: '#F5F6FA' },
                vest: { 500: '#F5A300', 600: '#DD8F00', 100: '#FFF1CF' },
                leaf: { 500: '#2F9E6E', 100: '#DCF3E8' },
                brick: { 500: '#D6482F', 100: '#FBE3DE' },
            },
            fontFamily: {
                disp: ['Archivo', 'sans-serif'],
                mono: ['"JetBrains Mono"', 'monospace'],
            },
        },
    },
    plugins: [],
};
