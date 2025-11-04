<template>
  <Head :title="`Reemplazar Archivo ${file.nice_name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Reemplazar Archivo ${file.nice_name}`" :cancel="{
        href: route('provinces.file.show', { 'province': province.id, 'file': file.id }),
        label: 'Cancelar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper admin-form__wrapper--full-width">
          <FileForm
            :sub-types="subTypes"
            context="province"
            :context-id="province.id"
            :existing-file="file"
            :store-url="route('provinces.file.replace', { 'province': province.id, 'file': file.id })"
            :cancel-url="route('provinces.file.show', { 'province': province.id, 'file': file.id })"
          />
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import FileForm from '@/Components/admin/FileForm.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  file: {
    type: Object,
    required: true
  },
  province: {
    type: Object,
    required: true
  },
  subTypes: {
    type: Array,
    required: true
  }
})
</script>
