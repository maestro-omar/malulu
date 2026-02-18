<template>
  <Head :title="`Reemplazar Archivo ${file.nice_name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Reemplazar Archivo ${file.nice_name}`" :cancel="{
        href: context === 'profile' ? route('profile.file.show', { file: file.id }) : route('users.file.show', { 'user': getUserSlug(user), 'file': file.id }),
        label: 'Cancelar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper admin-form__wrapper--full-width">
          <FileForm
            :sub-types="subTypes"
            :context="context === 'profile' ? 'profile' : 'user'"
            :context-id="user.id"
            :existing-file="file"
            :store-url="context === 'profile' ? route('profile.file.replace', { file: file.id }) : route('users.file.replace', { 'user': user.id, 'file': file.id })"
            :cancel-url="context === 'profile' ? route('profile.file.show', { file: file.id }) : route('users.file.show', { 'user': getUserSlug(user), 'file': file.id })"
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
import { getUserSlug } from '@/Utils/strings'

const props = defineProps({
  file: {
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
  },
  context: {
    type: String,
    default: 'user'
  }
})
</script>
