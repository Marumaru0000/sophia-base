import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/revolution/self-ordering/resources/views/**/*.blade.php',
    ],
    safelist: [
        'bg-blue-600',
        'bg-green-600',
        'text-white',
        'bg-gray-200',
        'text-gray-700',
        'border-blue-800',
        'border-gray-400',
        'border-green-800',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: colors.orange,
            },
        },
    },

    plugins: [
        forms,
        typography,
    ],
}
