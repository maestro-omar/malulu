<template>

  <Head :title="`Archivo ${file.nice_name}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Detalles del Archivo ${file.nice_name}`" :edit="{
        show: hasPermission($page.props, 'student.edit'),
        href: route('school.student.file.edit', { 'school': school.slug, 'user': getUserSlug(user), 'file': file.id }),
        label: 'Editar'
      }" :replace="{
        show: hasPermission($page.props, 'student.edit'),
        href: route('school.student.file.replace', { 'school': school.slug, 'user': getUserSlug(user), 'file': file.id }),
        label: 'Reemplazar'
      }" :del="{
        show: hasPermission($page.props, 'student.edit'),
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
import { getUserSlug } from '@/Utils/strings'
import { Head, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  file: {
    type: Object,
    required: true
  },
  user: {
    type: Object,
    required: true
  },
  school: {
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
    router.delete(route("users.file.destroy", { 'user': props.user, 'file': props.file.id }))
  }
}
</script>