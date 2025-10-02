<template>
  <AuthenticatedLayout>
    <Head title="Diagnósticos" />
    <template #admin-header>
      <AdminHeader :title="`Editar Diagnóstico: ${props.diagnosis.name}`"></AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">
        <div class="admin-form__wrapper">
          <form @submit.prevent="submit" class="admin-form__container">
            <q-card class="admin-form__card">
              <q-card-section>
                <div class="admin-form__card-content">
                  <div class="admin-form__field">
                    <InputLabel for="name" value="Nombre" />
                    <TextInput type="text" id="name" v-model="form.name"
                      class="admin-form__input" required />
                    <InputError class="admin-form__error" :message="form.errors.name" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="category" value="Categoría" />
                    <q-select
                      v-model="form.category"
                      :options="categoryOptions"
                      option-value="value"
                      option-label="label"
                      emit-value
                      map-options
                      outlined
                      dense
                      placeholder="Seleccione una categoría"
                      class="admin-form__input"
                      required
                    />
                    <InputError class="admin-form__error" :message="form.errors.category" />
                  </div>

                  <div class="admin-form__field">
                    <InputLabel for="code" value="Código" />
                    <TextInput type="text" id="code" v-model="form.code"
                      class="admin-form__input" />
                    <InputError class="admin-form__error" :message="form.errors.code" />
                  </div>

                  <div class="admin-form__field">
                    <q-checkbox v-model="form.active" label="Activo" />
                    <InputError class="admin-form__error" :message="form.errors.active" />
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <ActionButtons button-label="Guardar Cambios" :cancel-href="route('diagnoses.index')"
              :disabled="form.processing" />
          </form>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import ActionButtons from '@/Components/admin/ActionButtons.vue'
import InputError from '@/Components/admin/InputError.vue'
import InputLabel from '@/Components/admin/InputLabel.vue'
import TextInput from '@/Components/admin/TextInput.vue'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  diagnosis: {
    type: Object,
    required: true
  },
  categories: {
    type: Object,
    required: true
  }
})

// Convert categories object to options array for q-select
const categoryOptions = Object.entries(props.categories).map(([value, label]) => ({
  value,
  label
}))

const form = useForm({
  code: props.diagnosis.code,
  name: props.diagnosis.name,
  category: props.diagnosis.category,
  active: props.diagnosis.active,
})

const submit = () => {
  form.put(route('diagnoses.update', props.diagnosis.id))
}
</script>
