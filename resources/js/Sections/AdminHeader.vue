<template>
  <div class="admin-header">
    <div class="admin-header__content">
      <Breadcrumb v-if="breadcrumbs" :breadcrumbs="breadcrumbs" />
      <h2 class="page-subtitle">
        {{ title }}
      </h2>
    </div>
    <div class="admin-header__actions">
      <slot name="additional-buttons"></slot>
      <Link
        v-if="add && add.show"
        :href="add.href"
        class="admin-button admin-button--top admin-button--indigo"
      >
        {{ add.label || 'Nuevo' }}
      </Link>
      <Link
        v-if="trashed && trashed.show"
        :href="trashed.href"
        class="admin-button admin-button--top admin-button--gray"
      >
        {{ trashed.label || 'Eliminados' }}
      </Link>
      <Link
        v-if="edit && edit.show"
        :href="edit.href"
        class="admin-button admin-button--top admin-button--blue"
      >
        {{ edit.label || 'Editar' }}
      </Link>
      <button
        v-if="del && del.show"
        @click="del.onClick"
        class="admin-button admin-button--top admin-button--red"
      >
        {{ del.label || 'Eliminar' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/admin/Breadcrumbs.vue";

defineProps({
  breadcrumbs: Array,
  title: String,
  edit: {
    type: Object,
    default: null,
  },
  trashed: {
    type: Object,
    default: null,
  },
  del: {
    type: Object,
    default: null,
  },
  add: {
    type: Object,
    default: null,
  },
});
</script>
