<script setup>
import ApplicationLogo from '@/Components/admin/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div>
                    <Link href="/">
                    <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
                    </Link>
                </div>
                <slot name="header" />
                <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-end">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                    Inicio</Link>

                    <template v-else>
                        <Link :href="route('login')"
                            class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                        Acceso</Link>

                        <Link v-if="canRegister" :href="route('register')"
                            class="ms-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                        Register</Link>
                    </template>
                </div>
            </header>


            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>