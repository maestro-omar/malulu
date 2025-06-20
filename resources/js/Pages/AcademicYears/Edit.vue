<template>

  <Head title="Ciclos lectivos" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Editar Ciclo Lectivo ${props.academicYear.year}`"></AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
              <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="year">
                  Año
                </label>
                <input type="number" id="year" v-model="form.year"
                  class="shadow appearance-none border rounded w-32 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  disabled />
              </div>

              <div class="mb-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">
                      Fecha de Inicio
                    </label>
                    <input type="date" id="start_date" v-model="form.start_date"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                    <div v-if="form.errors.start_date" class="text-red-500 text-xs mt-1">
                      {{ form.errors.start_date }}
                    </div>
                  </div>

                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="end_date">
                      Fecha de Fin
                    </label>
                    <input type="date" id="end_date" v-model="form.end_date"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                    <div v-if="form.errors.end_date" class="text-red-500 text-xs mt-1">
                      {{ form.errors.end_date }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-6">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="winter_break_start">
                      Inicio de Vacaciones de Invierno
                    </label>
                    <input type="date" id="winter_break_start" v-model="form.winter_break_start"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                    <div v-if="form.errors.winter_break_start" class="text-red-500 text-xs mt-1">
                      {{ form.errors.winter_break_start }}
                    </div>
                  </div>

                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="winter_break_end">
                      Fin de Vacaciones de Invierno
                    </label>
                    <input type="date" id="winter_break_end" v-model="form.winter_break_end"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
                    <div v-if="form.errors.winter_break_end" class="text-red-500 text-xs mt-1">
                      {{ form.errors.winter_break_end }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <ActionButtons button-label="Guardar Cambios" :cancel-href="route('academic-years.index')"
            :disabled="form.processing" class="mt-4" />
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ActionButtons from '@/Components/admin/ActionButtons.vue'

const props = defineProps({
  academicYear: {
    type: Object,
    required: true
  }
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