<template>
  <AuthenticatedLayout>

    <Head title="Ciclos lectivos" />
    <AdminHeader :breadcrumbs="breadcrumbs" :title="`Nuevo Ciclo Lectivo`"></AdminHeader>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div>
            <form @submit.prevent="submit">
              <!-- Flash Messages -->
              <FlashMessages :flash="flash" />
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
                <PrimaryButton :disabled="form.processing">
                  Crear Ciclo Lectivo
                </PrimaryButton>
                <CancelLink :href="route('academic-years.index')" />
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import CancelLink from "@/Components/admin/CancelLink.vue";
import FlashMessages from '@/Components/admin/FlashMessages.vue';
import PrimaryButton from "@/Components/admin/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layout/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";

const form = useForm({
  year: "",
  start_date: "",
  end_date: "",
  winter_break_start: "",
  winter_break_end: "",
});

const submit = () => {
  form.post(route("academic-years.store"));
};
</script>