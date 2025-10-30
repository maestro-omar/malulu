<template>
  <Head :title="`Editar Archivo ${file.nice_name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Editar Archivo ${file.nice_name}`" :cancel="{
        href: route('school.file.show', { 'school': school.slug, 'file': file.id }),
        label: 'Cancelar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper admin-form__wrapper--full-width">
          <FileForm
            :sub-types="subTypes"
            context="school"
            :context-id="school.id"
            :existing-file="file"
            :update-url="route('school.file.update', { 'school': school.slug, 'file': file.id })"
            :cancel-url="route('school.file.show', { 'school': school.slug, 'file': file.id })"
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
  school: {
    type: Object,
    required: true
  },
  subTypes: {
    type: Array,
    required: true
  }
})
</script>
