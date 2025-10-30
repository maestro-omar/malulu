<template>
  <Head :title="`Reemplazar Archivo ${file.nice_name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Reemplazar Archivo ${file.nice_name}`" :cancel="{
        href: route('school.student.file.show', { 'school': school.slug, 'user': user.id, 'file': file.id }),
        label: 'Cancelar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper admin-form__wrapper--full-width">
          <FileForm
            :sub-types="subTypes"
            context="user"
            :context-id="user.id"
            :existing-file="file"
            :store-url="route('school.student.file.replace', { 'school': school.slug, 'user': user.id, 'file': file.id })"
            :cancel-url="route('school.student.file.show', { 'school': school.slug, 'user': user.id, 'file': file.id })"
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
  user: {
    type: Object,
    required: true
  },
  subTypes: {
    type: Array,
    required: true
  }
})
</script>
