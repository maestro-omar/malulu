<template>
  <Head :title="`Reemplazar Archivo ${file.nice_name}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Reemplazar Archivo ${file.nice_name}`" :cancel="{
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
      <div class="admin-form__wrapper">
        <div class="admin-form__container">
          <div class="admin-form__card">
            <div class="admin-form__section">
              <div class="admin-form__card-title">Reemplazar Archivo</div>
              
              <form @submit.prevent="onSubmit">
                <div class="admin-form__field">
                  <InputLabel for="input_type" value="Tipo de Entrada" />
                  <q-radio-group v-model="form.inputType" inline>
                    <q-radio val="file" label="Subir archivo" />
                    <q-radio val="url" label="URL externa" />
                  </q-radio-group>
                  <InputError :message="form.errors.inputType" />
                </div>

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

                <div class="admin-form__field" v-if="form.inputType === 'file'">
                  <InputLabel for="file" value="Archivo" />
                  <q-file
                    id="file"
                    v-model="form.file"
                    accept="*/*"
                    :error="!!form.errors.file"
                    :error-message="form.errors.file"
                    class="admin-form__input"
                  />
                  <InputError :message="form.errors.file" />
                </div>

                <div class="admin-form__field" v-if="form.inputType === 'url'">
                  <InputLabel for="external_url" value="URL Externa" />
                  <TextInput
                    id="external_url"
                    v-model="form.external_url"
                    type="url"
                    class="admin-form__input"
                    :class="{ 'admin-form__error': form.errors.external_url }"
                    placeholder="https://ejemplo.com/archivo.pdf"
                  />
                  <InputError :message="form.errors.external_url" />
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
                    :submit-text="'Reemplazar Archivo'"
                    :cancel-href="route('school.course.file.show', { 
                      'school': course.school.slug, 
                      'schoolLevel': course.school_level.code, 
                      'idAndLabel': course.id_and_label, 
                      'file': file.id 
                    })"
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

const form = useForm({
  inputType: 'file',
  subtype_id: props.file.subtype_id,
  nice_name: props.file.nice_name,
  description: props.file.description || '',
  file: null,
  external_url: ''
})

const subtypeOptions = computed(() => props.subTypes)

const onSubmit = () => {
  if (form.inputType === 'file' && form.file) {
    const formData = new FormData()
    formData.append('subtype_id', form.subtype_id)
    formData.append('nice_name', form.nice_name)
    formData.append('description', form.description || '')
    formData.append('file', form.file)

    form.post(route('school.course.file.replace', { 
      'school': props.course.school.slug, 
      'schoolLevel': props.course.school_level.code, 
      'idAndLabel': props.course.id_and_label, 
      'file': props.file.id 
    }), {
      forceFormData: true
    })
  } else if (form.inputType === 'url' && form.external_url) {
    const formData = {
      subtype_id: form.subtype_id,
      nice_name: form.nice_name,
      description: form.description || '',
      external_url: form.external_url
    }

    form.post(route('school.course.file.replace', { 
      'school': props.course.school.slug, 
      'schoolLevel': props.course.school_level.code, 
      'idAndLabel': props.course.id_and_label, 
      'file': props.file.id 
    }), {
      data: formData
    })
  }
}
</script>
