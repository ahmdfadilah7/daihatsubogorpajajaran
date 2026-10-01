import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Daihatsu Sahabat brand palette — a warm red primary used by
                // the login brand panel, admin accents and dashboard gradients.
                brand: {
                    50: '#fff1f1',
                    100: '#ffdfdf',
                    200: '#ffc5c5',
                    300: '#ff9d9d',
                    400: '#fb6565',
                    500: '#f23535',
                    600: '#e01818',
                    700: '#bc1111',
                    800: '#9b1212',
                    900: '#801616',
                    950: '#460606',
                },
            },
        },
    },

    plugins: [forms],
};
