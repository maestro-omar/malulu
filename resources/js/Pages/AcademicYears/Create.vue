<template>

  <Head title="Ciclos lectivos" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nuevo Ciclo Lectivo</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit">
              <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="year">
                  Año
                </label>
                <input type="number" id="year" v-model="form.year"
                  class="shadow appearance-none border rounded w-32 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                  required />
                <div v-if="form.errors.year" class="text-red-500 text-xs mt-1">
                  {{ form.errors.year }}
                </div>
              </div>

              <div class="mb-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">
                      Fecha de Inicio
                    </label>
                    <input type="date" id="start_date" v-model="form.start_date"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                      required />
                    <div v-if="form.errors.start_date" class="text-red-500 text-xs mt-1">
                      {{ form.errors.start_date }}
                    </div>
                  </div>

                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="end_date">
                      Fecha de Fin
                    </label>
                    <input type="date" id="end_date" v-model="form.end_date"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                      required />
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
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                      required />
                    <div v-if="form.errors.winter_break_start" class="text-red-500 text-xs mt-1">
                      {{ form.errors.winter_break_start }}
                    </div>
                  </div>

                  <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="winter_break_end">
                      Fin de Vacaciones de Invierno
                    </label>
                    <input type="date" id="winter_break_end" v-model="form.winter_break_end"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                      required />
                    <div v-if="form.errors.winter_break_end" class="text-red-500 text-xs mt-1">
                      {{ form.errors.winter_break_end }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <button type="submit"
                  class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                  :disabled="form.processing">
                  Crear Ciclo Lectivo
                </button>
                <Link :href="route('academic-years.index')" class="text-gray-600 hover:text-gray-800">
                  Cancelar
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { formatDateForInput } from '@/utils/date'

const form = useForm({
  year: '',
  start_date: '',
  end_date: '',
  winter_break_start: '',
  winter_break_end: ''
})

const submit = () => {
  form.post(route('academic-years.store'))
}
</script>