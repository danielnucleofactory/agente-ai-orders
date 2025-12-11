import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './internal_modules/**/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                inter: ['Inter', ...defaultTheme.fontFamily.sans],
                "dm-sans": ['DM Sans', ...defaultTheme.fontFamily.sans],
                sans: ['Lato', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'header': '0px 4px 30px 0px rgba(230, 229, 234, 0.75);',
                'login': '8px 8px 30px 0px rgba(0, 0, 0, 0.28);'
            },
            backgroundImage: {
                'home-hero': "url('/public/img/home-bg.png')",
            },
            colors: {
                'green-100': '#0B4739',
                'green-90': '#0F614D',
                'green-80': '#127A62',
                'green-70': '#169476',
                'green-60': '#1AAD8A',
                'green-50': '#28C7A1',
                'green-40': '#36D9B2',
                'green-30': '#45E6BF',
                'green-20': '#55F2CD',
                'green-10': '#66FFDB',
                'grey-100': '#1D1F1F',
                'grey-90': '#313535',
                'grey-80': '#4A4F4F',
                'grey-70': '#636969',
                'grey-60': '#7B8484',
                'grey-50': '#959D9D',
                'grey-40': '#B0B5B5',
                'grey-30': '#CACECE',
                'grey-20': '#E5E6E6',
                'grey-10': '#FAFAFA',
                'success': '#5DD595',
                'danger': '#FF3459',
                'warning': '#FEAE33',
                'dark-blue': '#127A62',
                'light-blue': '#1AAD8A',
                'neutral-blue': '#28C7A1'
            }
        },
    },

    plugins: [forms],
};
