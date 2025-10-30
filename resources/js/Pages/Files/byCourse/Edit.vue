<template>
  <Head :title="`Editar Archivo ${file.nice_name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Editar Archivo ${file.nice_name}`" :cancel="{
        href: route('school.course.file.show', { 
          'school': course.school.slug, 
          'schoolLevel': course.school_level.code, 
          'idAndLabel': course.id_and_label, 
          'file': file.id 
        }),
        label: 'Cancelar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper admin-form__wrapper--full-width">
          <FileForm
            :sub-types="subTypes"
            context="course"
            :context-id="course.id"
            :existing-file="file"
            :update-url="route('school.course.file.update', { 
              'school': course.school.slug, 
              'schoolLevel': course.school_level.code, 
              'idAndLabel': course.id_and_label, 
              'file': file.id 
            })"
            :cancel-url="route('school.course.file.show', { 
              'school': course.school.slug, 
              'schoolLevel': course.school_level.code, 
              'idAndLabel': course.id_and_label, 
              'file': file.id 
            })"
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
  course: {
    type: Object,
    required: true
  },
  subTypes: {
    type: Array,
    required: true
  }
})
</script>
