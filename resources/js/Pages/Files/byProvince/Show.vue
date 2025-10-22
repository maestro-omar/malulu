<template>
  <Head :title="`Archivo ${file.nice_name}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Detalles del Archivo ${file.nice_name}`" :edit="{
        show: hasPermission($page.props, 'file.manage'),
        href: route('provinces.file.edit', { 'province': province.id, 'file': file.id }),
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
  province: {
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
    router.delete(route("provinces.file.destroy", { 'province': props.province.id, 'file': props.file.id }))
  }
}
</script>
