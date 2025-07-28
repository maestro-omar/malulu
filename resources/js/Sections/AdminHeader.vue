<template>
  <div class="admin-header">
    <div class="admin-header__content">
      <Breadcrumb v-if="breadcrumbs" :breadcrumbs="breadcrumbs" />
      <div class="admin-header__title-and-buttons">
        <h2 class="admin-header__title page-subtitle">
          {{ title }}
        </h2>
        <div class="admin-header__actions">
          <Link v-if="add && add.show" :href="add.href" class="admin-button admin-button--top admin-button--indigo">
          {{ add.label || 'Nuevo' }}
          </Link>
          <Link v-if="trashed && trashed.show" :href="trashed.href"
            class="admin-button admin-button--top admin-button--gray">
          {{ trashed.label || 'Eliminados' }}
          </Link>
          <Link v-if="edit && edit.show" :href="edit.href" class="admin-button admin-button--top admin-button--blue">
          {{ edit.label || 'Editar' }}
          </Link>
          <button v-if="del && del.show" @click="del.onClick" class="admin-button admin-button--top admin-button--red">
            {{ del.label || 'Eliminar' }}
          </button>
        </div>
      </div>
      <div v-if="hasAdditionalButtons" class="admin-header__actions admin-header__actions--more">
        <slot name="additional-buttons"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { useSlots } from 'vue';
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

const slots = useSlots();
const hasAdditionalButtons = !!slots['additional-buttons'];
</script>