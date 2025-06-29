<template>
  <div class="flex justify-between items-center">
    <div class="flex flex-col flex-1">
      <Breadcrumb v-if="breadcrumbs" :breadcrumbs="breadcrumbs" />
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
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
