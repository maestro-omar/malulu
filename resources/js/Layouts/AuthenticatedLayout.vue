<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/admin/ApplicationLogo.vue';
import Dropdown from '@/Components/admin/Dropdown.vue';
import DropdownLink from '@/Components/admin/DropdownLink.vue';
import NavLink from '@/Components/admin/NavLink.vue';
import ResponsiveNavLink from '@/Components/admin/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

onMounted(() => {
    // console.log('Auth User:', page.props.auth.user);
    // console.log('Debug Info:', page.props.debug);
    // console.log('Menu Items:', page.props.menu.items);
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-gradient-to-b from-red-900 to-blue-900 border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-white"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <template v-for="item in $page.props.menu.items" :key="item.route">
                                    <NavLink :href="route(item.route)" :active="route().current(item.route)" class="text-white hover:text-gray-200">
                                        {{ item.name }}
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white hover:text-gray-200 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <template v-for="item in $page.props.menu.userItems" :key="item.route || 'separator'">
                                            <template v-if="item.type === 'separator'">
                                                <div class="border-t border-gray-200 my-1"></div>
                                            </template>
                                            <template v-else>
                                                <DropdownLink :href="route(item.route)" :as="item.route === 'logout.get' ? 'button' : 'a'">
                                                    {{ item.name }}
                                                </DropdownLink>
                                            </template>
                                        </template>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <template v-for="item in $page.props.menu.items" :key="item.route">
                            <ResponsiveNavLink :href="route(item.route)" :active="route().current(item.route)" class="text-white hover:text-gray-200">
                                {{ item.name }}
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-white">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-200">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <template v-for="item in $page.props.menu.userItems" :key="item.route || 'separator'">
                                <template v-if="item.type === 'separator'">
                                    <div class="border-t border-gray-200 my-1"></div>
                                </template>
                                <template v-else>
                                    <ResponsiveNavLink
                                        :href="route(item.route)"
                                        :as="item.route === 'logout.get' ? 'button' : 'a'"
                                        class="text-white hover:text-gray-200"
                                    >
                                        {{ item.name }}
                                    </ResponsiveNavLink>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
