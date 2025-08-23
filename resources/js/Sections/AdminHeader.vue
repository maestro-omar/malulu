<template>

  <q-toolbar class="bg-accent text-white mll-sub-header">
    <div class="mll-sub-header__content">
      <Breadcrumb v-if="breadcrumbs" :breadcrumbs="breadcrumbs" />
      <div class="mll-sub-header__title-and-buttons">
        <q-toolbar-title class="mll-sub-header__title">
          {{ title }}
        </q-toolbar-title>
        <div class="mll-sub-header__actions">
            <q-btn v-if="add && add.show" :href="add.href" color="green" size="md" :label="add.label || 'Nuevo'" />

            <Link v-if="trashed && trashed.show" :href="trashed.href"
              class="admin-button admin-button--top admin-button--gray">
            {{ trashed.label || 'Eliminados' }}
            </Link>
            <Link v-if="edit && edit.show" :href="edit.href" class="admin-button admin-button--top admin-button--blue">
            {{ edit.label || 'Editar' }}
            </Link>
            <button v-if="del && del.show" @click="del.onClick"
              class="admin-button admin-button--top admin-button--red">
              {{ del.label || 'Eliminar' }}
            </button>
        </div>
      </div>
      <div v-if="hasAdditionalButtons" class="mll-sub-header__actions mll-sub-header__actions--more">
        <slot name="additional-buttons"></slot>
      </div>
    </div>
  </q-toolbar>
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