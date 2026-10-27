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
            //DEFINICION DE PALETA DE COLORES
            colors: {
                kato: {
                    blue: '#071D49',       // Azul oscuro
                    darkgreen: '#154734',  // Verde oscuro
                    green: '#4C8C2B',      // Verde medio
                    lightgreen: '#84BD00', // Verde claro
                    yellow: '#F9C510',     // Amarillo
                    gray: '#63666A',       // Gris
                }
            }
        },
    },

    plugins: [forms],
};
