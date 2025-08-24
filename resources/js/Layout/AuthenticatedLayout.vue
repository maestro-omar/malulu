<template>
  <q-layout view="hHh lpr fFr">
    <!-- Header with only toolbar -->
    <q-header elevated class="bg-primary text-white" height-hint="64">
      <q-toolbar>
        <q-toolbar-title>
          <q-avatar>
            <SystemLogo :href="route('dashboard')" />
          </q-avatar>
          {{ page.props.appName }}
        </q-toolbar-title>
        <q-tabs>
          <q-route-tab v-for="item in page.props.menu.items" :key="item.route" :href="route(item.route)"
            :active="route().current(item.route)" :label="item.name" />
        </q-tabs>


        {{ page.props.auth.user.name }}
        <q-btn dense flat round icon="menu" @click="toggleRightDrawer" />
      </q-toolbar>
    </q-header>

    <!-- Drawer -->
    <q-drawer v-model="rightDrawerOpen" side="right" overlay elevated>
      <q-btn flat round dense size="10px" icon="close" class="absolute-top-right q-ma-xs"
        @click="rightDrawerOpen = false" />
      <q-list class="q-mt-lg">
        <template v-for="item in page.props.menu.userItems" :key="item.route || 'separator'">
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

    <!-- Page container -->
    <q-page-container>

      <!-- AdminHeader slot -->
      <slot name="admin-header" />

      <!-- Page content -->
      <slot />
    </q-page-container>
  </q-layout>
</template>


<script setup>
//OMAR IMPORTANTE: rediseñar con https://quasar.dev/layout/gallery/google-photos
import SystemLogo from '@/Components/SystemLogo.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

defineProps({
  title: {
    type: String
  }
})

const rightDrawerOpen = ref(false)

const toggleRightDrawer = () => {
  rightDrawerOpen.value = !rightDrawerOpen.value
}


const showingNavigationDropdown = ref(false);
const page = usePage();
const school = page.props.auth.user.activeSchool;

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
  // console.log('Auth User:', page.props.auth.user);
  // console.log('Debug Info:', page.props.debug);
  // console.log('Menu Items:', page.props.menu.items);

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
