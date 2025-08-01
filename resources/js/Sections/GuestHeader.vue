<template>
  <div class="guest-header">
    <div class="guest-header__content">
      <Breadcrumb v-if="breadcrumbs" :breadcrumbs="breadcrumbs" class="guest-header__breadcrumb" />
      <h2 class="guest-header__title">
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
