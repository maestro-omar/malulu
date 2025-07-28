<template>

  <Head title="Ciclos lectivos" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Editar Ciclo Lectivo ${props.academicYear.year}`"></AdminHeader>
    </template>

    <div class="container">
      <div class="admin-form__wrapper">
        <form @submit.prevent="submit" class="admin-form__container">
          <!-- Flash Messages -->
          <FlashMessages :error="flash?.error" :success="flash?.success" />
          <div class="admin-form__card">
            <div class="admin-form__card-content">
              <div class="admin-form__field">
                <InputLabel for="year" value="Año" />
                <TextInput type="number" id="year" v-model="form.year" class="admin-form__input form__input--disabled" disabled />
              </div>

              <div class="admin-form__grid form__grid--2">
                <div class="admin-form__field">
                  <InputLabel for="start_date" value="Fecha de Inicio" />
                  <TextInput type="date" id="start_date" v-model="form.start_date" class="admin-form__input" />
                  <InputError class="admin-form__error" :message="form.errors.start_date" />
                </div>

                <div class="admin-form__field">
                  <InputLabel for="end_date" value="Fecha de Fin" />
                  <TextInput type="date" id="end_date" v-model="form.end_date" class="admin-form__input" />
                  <InputError class="admin-form__error" :message="form.errors.end_date" />
                </div>
              </div>

              <div class="admin-form__grid form__grid--2">
                <div class="admin-form__field">
                  <InputLabel for="winter_break_start" value="Inicio de Vacaciones de Invierno" />
                  <TextInput type="date" id="winter_break_start" v-model="form.winter_break_start" class="admin-form__input" />
                  <InputError class="admin-form__error" :message="form.errors.winter_break_start" />
                </div>

                <div class="admin-form__field">
                  <InputLabel for="winter_break_end" value="Fin de Vacaciones de Invierno" />
                  <TextInput type="date" id="winter_break_end" v-model="form.winter_break_end" class="admin-form__input" />
                  <InputError class="admin-form__error" :message="form.errors.winter_break_end" />
                </div>
              </div>
            </div>
          </div>

          <ActionButtons button-label="Guardar Cambios" :cancel-href="route('academic-years.index')"
            :disabled="form.processing" />
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ActionButtons from '@/Components/admin/ActionButtons.vue'
import InputLabel from '@/Components/admin/InputLabel.vue'
import TextInput from '@/Components/admin/TextInput.vue'
import InputError from '@/Components/admin/InputError.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import FlashMessages from '@/Components/admin/FlashMessages.vue';

const props = defineProps({
  academicYear: {
    type: Object,
    required: true
  },
  breadcrumbs: Array
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toISOString().split('T')[0]
}

const form = useForm({
  year: props.academicYear.year,
  start_date: formatDate(props.academicYear.start_date),
  end_date: formatDate(props.academicYear.end_date),
  winter_break_start: formatDate(props.academicYear.winter_break_start),
  winter_break_end: formatDate(props.academicYear.winter_break_end)
})

const submit = () => {
  form.put(route('academic-years.update', props.academicYear.id))
}
</script>