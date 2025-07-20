<template>
  <div class="flex justify-between items-center">
    <div class="flex flex-col flex-1">
      <Breadcrumb v-if="breadcrumbs" :breadcrumbs="breadcrumbs" />
      <h2 class="page-subtitle">
        {{ title }}
      </h2>
    </div>
  </div>
</template>

<script setup>
import Breadcrumb from "@/Components/admin/Breadcrumbs.vue";

defineProps({
  breadcrumbs: Array,
  title: String,
});
</script>
