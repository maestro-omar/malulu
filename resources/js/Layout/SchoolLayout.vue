<template>
  <q-layout view="hHh lpr fFr">
    <!-- Header with only toolbar -->
    <q-header reveal elevated class="bg-primary text-white" height-hint="64"
      :background-color="headerStyles.background">
      <q-toolbar>
        <q-toolbar-title>
          <component v-if="logoComponent" :is="logoComponent" class="school-layout__nav-logo-image"
            :color="headerStyles.textColor" :href="route('dashboard')" />
          <Link v-else-if="school && school.logo" :href="route('dashboard')">
          <img:src="school.logo" :alt="school.name" :title="school.name" class="school-layout__nav-logo-image" />
          </Link>
          {{ $page.props.appName }}
        </q-toolbar-title>
        <q-tabs>
          <q-route-tab v-for="item in $page.props.menu.items" :key="item.route" :href="route(item.route)"
            :active="route().current(item.route)" :label="item.name" />
        </q-tabs>


        {{ $page.props.auth.user.name }}
        <q-btn dense flat round icon="menu" @click="toggleRightDrawer" />
      </q-toolbar>
      <!-- AdminHeader slot -->
      <slot name="admin-header" />
    </q-header>

    <!-- Drawer -->
    <q-drawer v-model="rightDrawerOpen" side="right" overlay elevated>
      <q-btn flat round dense size="10px" icon="close" class="absolute-top-right q-ma-xs"
        @click="rightDrawerOpen = false" />
      <q-list class="q-mt-lg">
        <template v-for="item in $page.props.menu.userItems" :key="item.route || 'separator'">
          <template v-if="item.type === 'separator'">
            <q-separator />
          </template>
          <template v-else>
            <q-item :clickable="item.route !== 'logout.get'" v-ripple
              :class="['authenticated-layout__responsive-settings-item', headerStyles.textColor, headerStyles.hoverColor]"
              :href="route(item.route)">
              <q-item-section>
                <q-item-label>{{ item.name }}</q-item-label>
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
    return SystemLogo;
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