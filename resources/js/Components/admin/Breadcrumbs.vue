<template>
  <q-breadcrumbs class="admin-nav-breadcrumbs" separator="|">
    <q-breadcrumbs-el
      v-for="(crumb, i) in breadcrumbs"
      :key="i"
      :label="crumb.title"
      :href="crumb.url"
      :class="`admin-nav-breadcrumbs__item aaa ${crumb?.extraClass || 'bb'}`"
      :icon="crumb.icon"
    >
    </q-breadcrumbs-el>
    
  </q-breadcrumbs>
</template>

<script setup>
defineProps({
  breadcrumbs: {
    type: Array,
    required: true,
  },
})
</script>