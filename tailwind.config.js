import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    safelist: [
        // 🎓 Roles (solo bg-100 y text-800)
        'bg-purple-100', 'text-purple-800',
        'bg-blue-100', 'text-blue-800',
        'bg-green-100', 'text-green-800',
        'bg-yellow-100', 'text-yellow-800',
        'bg-indigo-100', 'text-indigo-800',
        'bg-pink-100', 'text-pink-800',
        'bg-orange-100', 'text-orange-800',
        'bg-teal-100', 'text-teal-800',
        'bg-cyan-100', 'text-cyan-800',
        'bg-emerald-100', 'text-emerald-800',
        'bg-violet-100', 'text-violet-800',
        'bg-rose-100', 'text-rose-800',
        'bg-sky-100', 'text-sky-800',
        'bg-amber-100', 'text-amber-800',
        'bg-slate-100', 'text-slate-800',

        // 🕒 Shifts (también incluye bg-600)
        'bg-green-100', 'text-green-800', 'bg-green-600',
        'bg-orange-100', 'text-orange-800', 'bg-orange-600',
        'bg-indigo-100', 'text-indigo-800', 'bg-indigo-600',

        // Levels (también incluye bg-600)
        'bg-rose-100', 'text-rose-800', 'bg-rose-600',
        'bg-amber-100', 'text-amber-800', 'bg-amber-600',
        'bg-violet-100', 'text-violet-800', 'bg-violet-600',
    ],

    plugins: [forms],
};
