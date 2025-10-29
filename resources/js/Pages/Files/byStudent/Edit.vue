<template>
  <Head :title="`Editar Archivo ${file.nice_name}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Editar Archivo ${file.nice_name}`" :cancel="{
        href: route('users.file.show', { 'user': getUserSlug(user), 'file': file.id }),
        label: 'Cancelar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="admin-form__wrapper">
        <div class="admin-form__container">
          <div class="admin-form__card">
            <div class="admin-form__section">
              <div class="admin-form__card-title">Información del Archivo</div>
              
              <form @submit.prevent="onSubmit">
                <div class="admin-form__field">
                  <InputLabel for="subtype_id" value="Tipo de contenido" />
                  <q-select
                    id="subtype_id"
                    v-model="form.subtype_id"
                    :options="subtypeOptions"
                    option-value="id"
                    option-label="name"
                    emit-value
                    map-options
                    :error="!!form.errors.subtype_id"
                    :error-message="form.errors.subtype_id"
                    class="admin-form__input"
                  />
                  <InputError :message="form.errors.subtype_id" />
                </div>

                <div class="admin-form__field">
                  <InputLabel for="nice_name" value="Nombre del Archivo" />
                  <TextInput
                    id="nice_name"
                    v-model="form.nice_name"
                    type="text"
                    class="admin-form__input"
                    :class="{ 'admin-form__error': form.errors.nice_name }"
                    required
                    autofocus
                  />
                  <InputError :message="form.errors.nice_name" />
                </div>

                <div class="admin-form__field">
                  <InputLabel for="description" value="Descripción" />
                  <TextInput
                    id="description"
                    v-model="form.description"
                    type="text"
                    class="admin-form__input"
                    :class="{ 'admin-form__error': form.errors.description }"
                  />
                  <InputError :message="form.errors.description" />
                </div>

                <div class="admin-form__actions">
                  <ActionButtons 
                    :submit-text="'Actualizar Archivo'"
                    :cancel-href="route('users.file.show', { 'user': getUserSlug(user), 'file': file.id })"
                  />
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import InputLabel from '@/Components/admin/InputLabel.vue'
import TextInput from '@/Components/admin/TextInput.vue'
import InputError from '@/Components/admin/InputError.vue'
import ActionButtons from '@/Components/admin/ActionButtons.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { getUserSlug } from '@/Utils/strings'

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

const form = useForm({
  subtype_id: props.file.subtype_id,
  nice_name: props.file.nice_name,
  description: props.file.description || ''
})

const subtypeOptions = computed(() => props.subTypes)

const onSubmit = () => {
  form.put(route('school.student.file.update', {'school': props.school.slug, 'user': props.user.id, 'file': props.file.id }))
}
</script>
