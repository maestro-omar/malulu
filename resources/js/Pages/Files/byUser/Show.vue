<template>

  <Head :title="`Archivo ${file.nice_name}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Detalles del Archivo ${file.nice_name}`" :edit="{
        show: context === 'profile' || hasPermission($page.props, 'file.manage'),
        href: context === 'profile' ? route('profile.file.edit', { file: file.id }) : route('users.file.edit', { 'user': getUserSlug(user), 'file': file.id }),
        label: 'Editar'
      }" :replace="{
        show: context === 'profile' || hasPermission($page.props, 'file.manage'),
        href: context === 'profile' ? route('profile.file.replace', { file: file.id }) : route('users.file.replace', { 'user': getUserSlug(user), 'file': file.id }),
        label: 'Reemplazar'
      }" :del="{
        show: context !== 'profile' && hasPermission($page.props, 'file.manage'),
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
  history: {
    type: Array,
    required: false,
    default: () => []
  },
  context: {
    type: String,
    default: 'user'
  }
})

const $page = usePage()

const destroy = () => {
  if (props.context === 'profile') return
  if (confirm("¿Está seguro que desea eliminar este archivo?")) {
    router.delete(route("users.file.destroy", { 'user': props.user, 'file': props.file.id }))
  }
}
</script>