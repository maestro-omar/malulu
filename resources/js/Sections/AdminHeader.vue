<template>
  <div class="flex justify-between items-center">
    <div class="flex flex-col flex-1">
      <Breadcrumb v-if="breadcrumbs" :breadcrumbs="breadcrumbs" />
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ title }}
      </h2>
    </div>
    <div class="flex flex-col space-y-2 md:flex-row md:space-x-4 md:space-y-0">
      <slot name="additional-buttons"></slot>
      <Link
        v-if="add && add.show"
        :href="add.href"
        class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
      >
        {{ add.label || 'Nuevo' }}
      </Link>
      <Link
        v-if="trashed && trashed.show"
        :href="trashed.href"
        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
      >
        {{ trashed.label || 'Eliminados' }}
      </Link>
      <Link
        v-if="edit && edit.show"
        :href="edit.href"
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
      >
        {{ edit.label || 'Editar' }}
      </Link>
      <button
        v-if="del && del.show"
        @click="del.onClick"
        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
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
