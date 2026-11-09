/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                petal: {
                    50: '#FFF4F8', 100: '#FDE3EE', 200: '#FACDE0', 300: '#F6A9C8', 400: '#F27DAF',
                    500: '#EC4F95', 600: '#CF2C7B', 700: '#B02468', 800: '#8F1F55', 900: '#6E1943',
                },
                sage: {
                    50: '#F4F8EC', 100: '#E6EFD6', 200: '#D2E2B5', 300: '#B6CE8F', 400: '#98B56C',
                    500: '#7D9A52', 600: '#637E3F', 700: '#4E6433', 800: '#3F512B', 900: '#2F3E20',
                },
                olive: {
                    50: '#F6F7F1', 100: '#E9ECDD', 200: '#D3D9BC', 300: '#B4BE8F', 400: '#8E9B62',
                    500: '#6F7E45', 600: '#59673A', 700: '#475330', 800: '#3A4429', 900: '#2C3420',
                },
                cream: { DEFAULT: '#FFFDF8', 100: '#FBF6EC' },
            },
            fontFamily: {
                sans: ['"DM Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
            },
        },
    },
    plugins: [],
};
