<template>
  <q-layout view="hHh lpR fFf">
    <q-header elevated class="bg-white text-primary" height-hint="90">
      <q-toolbar>
        <SystemLogo />
        <q-toolbar-title>
          {{ title }}
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-page-container>
      <slot />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import SystemLogo from '@/Components/SystemLogo.vue';

defineProps({
  title: {
    type: String
  }
});
</script>