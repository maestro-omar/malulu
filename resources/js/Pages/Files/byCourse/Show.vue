<template>
  <Head :title="`Archivo ${file.nice_name}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Detalles del Archivo ${file.nice_name}`" :edit="{
        show: hasPermission($page.props, 'file.manage'),
        href: route('school.course.file.edit', { 
          'school': course.school.slug, 
          'schoolLevel': course.school_level.code, 
          'idAndLabel': course.id_and_label, 
          'file': file.id 
        }),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'file.manage'),
        onClick: destroy,
        label: 'Eliminar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <FileShow :file="file" :history="history" />
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import FileShow from '@/Components/admin/FileShow.vue'
import { hasPermission } from '@/Utils/permissions'
import { Head, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  file: {
    type: Object,
    required: true
  },
  course: {
    type: Object,
    required: true
  },
  history: {
    type: Array,
    required: false,
    default: () => []
  }
})

const $page = usePage()

const destroy = () => {
  if (confirm("¿Está seguro que desea eliminar este archivo?")) {
    router.delete(route("school.course.file.destroy", { 
      'school': props.course.school.slug, 
      'schoolLevel': props.course.school_level.code, 
      'idAndLabel': props.course.id_and_label, 
      'file': props.file.id 
    }))
  }
}
</script>
