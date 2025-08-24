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

          <q-btn v-if="trashed && trashed.show" :href="trashed.href" color="grey-6" size="md"
            :label="trashed.label || 'Eliminados'" />
          <q-btn v-if="edit && edit.show" :href="edit.href" color="primary" size="md" :label="edit.label || 'Editar'" />
          <q-btn v-if="del && del.show" @click="del.onClick" color="red" size="md" :label="del.label || 'Eliminar'" />
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