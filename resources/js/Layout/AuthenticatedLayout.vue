<template>
  <q-layout view="hHr lpR fFf">
    <!-- Header with only toolbar -->
    <q-header reveal elevated class="bg-white text-primary mll-header" height-hint="90">
      <q-toolbar class="mll-header__toolbar">
        <SystemLogo :href="route('dashboard')" class="mll-header__logo" />
        <q-toolbar-title class="mll-header__title">
          {{ $page.props.appName }}
        </q-toolbar-title>
        <div class="mll-header__right">
          <q-tabs class="mll-header__tabs">
            <q-route-tab v-for="item in $page.props.menu.items" :key="item.route || item.href"
              :href="item.href ? item.href : (item.route ? route(item.route) : undefined)"
              :active="item.route ? route().current(item.route) : (item.href ? $page.url === item.href : false)"
              :label="item.name" :icon="item.icon ? item.icon : undefined"
              :class="item.colorClass ? item.colorClass : 'text-teal'" />
          </q-tabs>


          <span class="mll-header__user">{{ $page.props.auth.user.name }}</span>
          <q-btn class="mll-header__hamburger" dense flat round icon="menu" @click="toggleRightDrawer" />
        </div>
      </q-toolbar>
      <!-- AdminHeader slot -->
      <slot name="admin-header" />
    </q-header>

    <!-- Drawer -->
    <q-drawer v-model="rightDrawerOpen" side="right" overlay elevated class="mll-sidebar">
      <q-btn flat round dense size="10px" icon="close" class="absolute-top-right q-ma-xs"
        @click="rightDrawerOpen = false" />
      <q-list class="q-mt-lg">
        <template v-if="$page.props.menu.items">

          <template v-for="item in $page.props.menu.items" :key="item.route || item.href">
            <q-item clickable="true" v-ripple dense
              :class="['mll-sidebar__side-item mll-sidebar__side-item--only-sm', headerStyles.textColor, headerStyles.hoverColor]"
              :href="item.href ? item.href : (item.route ? route(item.route) : undefined)">
              <q-item-section>
                <q-item-label>
                  <q-icon v-if="item.icon" :name="item.icon" /> {{ item.name }}
                </q-item-label>
              </q-item-section>
            </q-item>
          </template>
          <q-separator />
        </template>

        <template v-for="item in $page.props.menu.userItems" :key="item.route || item.href || 'separator'">
          <template v-if="item.type === 'separator'">
            <q-separator />
          </template>
          <template v-else>
            <q-item :clickable="(item.route !== 'logout.get')" v-ripple dense
              :class="['mll-sidebar__side-item', headerStyles.textColor, headerStyles.hoverColor]"
              :href="item.href ? item.href : (item.route ? route(item.route) : undefined)">
              <q-item-section>
                <q-item-label>
                  <q-icon v-if="item.icon" :name="item.icon" /> {{ item.name }}
                </q-item-label>
              </q-item-section>
            </q-item>
          </template>
        </template>
      </q-list>
    </q-drawer>

    <q-page-container>
      <q-page :class="pageClass">

        <!-- Flash Messages -->
        <FlashMessages :flash="flash" />

        <slot name="main-page-content" />

      </q-page>
    </q-page-container>
  </q-layout>
</template>


<script setup>
//OMAR IMPORTANTE: rediseñar con https://quasar.dev/layout/gallery/google-photos
import SystemLogo from '@/Components/SystemLogo.vue';
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

defineProps({
  title: {
    type: String
  },
  pageClass: {
    type: String,
    default: 'q-pa-md'
  }
})
const $page = usePage();
const flash = $page.props.flash;

const rightDrawerOpen = ref(false)
// const showingNavigationDropdown = ref(false);
const school = $page.props.auth.user.activeSchool;

const toggleRightDrawer = () => {
  rightDrawerOpen.value = !rightDrawerOpen.value
}

// Parse school configuration from extra field
const schoolConfig = computed(() => {
  if (!school || !school.extra) return null;

  const extra = typeof school.extra === 'string'
    ? JSON.parse(school.extra)
    : school.extra;

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
  if (!school || !school.logo) {
    return ApplicationLogo;
  }
  return null;
});

onMounted(() => {
  // console.log('Auth User:', $page.props.auth.user);
  // console.log('Debug Info:', $page.props.debug);
  // console.log('Menu Items:', $page.props.menu.items);

  // Debug school prop
  // console.log('Layout - school prop:', school);
  // console.log('Layout - school type:', typeof school);
  // console.log('Layout - school extra:', school?.extra);
  // console.log('Layout - school config:', schoolConfig.value);
  // console.log('Layout - header styles:', headerStyles.value);
  // console.log('Layout - school keys:', Object.keys(school || {}));
  // console.log('Layout - school logo:', school?.logo);
});
</script>
