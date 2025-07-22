<script setup>
import { ref, onMounted, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/admin/ApplicationLogo.vue';
import Dropdown from '@/Components/admin/Dropdown.vue';
import DropdownLink from '@/Components/admin/DropdownLink.vue';
import NavLink from '@/Components/admin/NavLink.vue';
import ResponsiveNavLink from '@/Components/admin/ResponsiveNavLink.vue';
import Breadcrumb from '@/Components/admin/Breadcrumbs.vue';

const props = defineProps({
    school: {
        type: Object,
        default: null
    }
});

const showingNavigationDropdown = ref(false);
const page = usePage();

// Parse school configuration from extra field
const schoolConfig = computed(() => {
    if (!props.school || !props.school.extra) return null;

    const extra = typeof props.school.extra === 'string'
        ? JSON.parse(props.school.extra)
        : props.school.extra;

    return extra.config || null;
});

// Get header styling based on school configuration
const headerStyles = computed(() => {
    if (!schoolConfig.value || !schoolConfig.value.header) {
        return {
            background: '#F5F5F5',
            textColor: '#475569',
            hoverColor: '#0d9488'
        };
    }

    const headerConfig = schoolConfig.value.header;
    return {
        background: headerConfig.background || '#F5F5F5',
        textColor: headerConfig.textColor || '#475569',
        hoverColor: headerConfig.hoverColor || '#0d9488'
    };
});

// Get logo component or image
const logoComponent = computed(() => {
    if (!props.school || !props.school.logo) {
        return ApplicationLogo;
    }
    return null;
});

onMounted(() => {
    // console.log('Auth User:', page.props.auth.user);
    // console.log('Debug Info:', page.props.debug);
    // console.log('Menu Items:', page.props.menu.items);

    // Debug school prop
    // console.log('Layout - school prop:', props.school);
    // console.log('Layout - school type:', typeof props.school);
    // console.log('Layout - school extra:', props.school?.extra);
    // console.log('Layout - school config:', schoolConfig.value);
    // console.log('Layout - header styles:', headerStyles.value);
    // console.log('Layout - school keys:', Object.keys(props.school || {}));
    // console.log('Layout - school logo:', props.school?.logo);
});
</script>

<template>
    <div class="authenticated-layout">
        <div class="authenticated-layout__container">
            <nav class="authenticated-layout__nav" :background-color="headerStyles.background">
                <!-- Primary Navigation Menu -->
                <div class="authenticated-layout__nav-container">
                    <div class="authenticated-layout__nav-content">
                        <div class="authenticated-layout__nav-left">
                            <!-- Logo -->
                            <div class="authenticated-layout__nav-logo">
                                <Link :href="route('dashboard')">
                                    <component
                                        v-if="logoComponent"
                                        :is="logoComponent"
                                        class="authenticated-layout__nav-logo-image"
                                        :color="headerStyles.textColor"
                                    />
                                    <img
                                        v-else-if="school && school.logo"
                                        :src="school.logo"
                                        :alt="school.name"
                                        :title="school.name"
                                        class="authenticated-layout__nav-logo-image"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="authenticated-layout__nav-links">
                                <template v-for="item in $page.props.menu.items" :key="item.route">
                                    <NavLink
                                        :href="route(item.route)"
                                        :active="route().current(item.route)"
                                        :color="headerStyles.textColor"
                                        :hoverColor="headerStyles.hoverColor"
                                    >
                                        {{ item.name }}
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="authenticated-layout__nav-right">
                            <!-- Settings Dropdown -->
                            <div class="authenticated-layout__nav-dropdown">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="authenticated-layout__nav-dropdown-trigger">
                                            <button type="button"
                                                class="authenticated-layout__nav-dropdown-trigger-button"
                                                :class="[headerStyles.textColor, headerStyles.hoverColor]">
                                                {{ $page.props.auth.user.name }}

                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <template v-for="item in $page.props.menu.userItems"
                                            :key="item.route || 'separator'">
                                            <template v-if="item.type === 'separator'">
                                                <div class="authenticated-layout__responsive-settings-separator"></div>
                                            </template>
                                            <template v-else>
                                                <DropdownLink :href="route(item.route)"
                                                    :as="item.route === 'logout.get' ? 'button' : 'a'">
                                                    {{ item.name }}
                                                </DropdownLink>
                                            </template>
                                        </template>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="authenticated-layout__nav-hamburger">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="authenticated-layout__nav-hamburger-button"
                                :class="[headerStyles.textColor, headerStyles.hoverColor]">
                                <svg stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="['authenticated-layout__responsive', { 'authenticated-layout__responsive--visible': showingNavigationDropdown }]">
                    <div class="authenticated-layout__responsive-menu">
                        <template v-for="item in $page.props.menu.items" :key="item.route">
                            <ResponsiveNavLink
                                :href="route(item.route)"
                                :active="route().current(item.route)"
                                :class="[headerStyles.textColor, headerStyles.hoverColor]"
                            >
                                {{ item.name }}
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="authenticated-layout__responsive-settings">
                        <div class="authenticated-layout__responsive-settings-user">
                            <div class="authenticated-layout__responsive-settings-user-name" :class="headerStyles.textColor">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="authenticated-layout__responsive-settings-user-email">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="authenticated-layout__responsive-settings-links">
                            <template v-for="item in $page.props.menu.userItems" :key="item.route || 'separator'">
                                <template v-if="item.type === 'separator'">
                                    <div class="authenticated-layout__responsive-settings-separator"></div>
                                </template>
                                <template v-else>
                                    <ResponsiveNavLink :href="route(item.route)"
                                        :as="item.route === 'logout.get' ? 'button' : 'a'"
                                        :class="[headerStyles.textColor, headerStyles.hoverColor]">
                                        {{ item.name }}
                                    </ResponsiveNavLink>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="authenticated-layout__header" v-if="$slots.header">
                <div class="authenticated-layout__header-container">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="authenticated-layout__main">
                <slot />
            </main>
        </div>
    </div>
</template>