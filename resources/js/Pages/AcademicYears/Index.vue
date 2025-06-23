<template>

  <Head title="Ciclos lectivos" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Ciclos Lectivos`" :add="{
        show: hasPermission($page.props, 'superadmin', null),
        href: route('academic-years.create'),
        label: 'Nuevo'
      }">
      </AdminHeader>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Año
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha
                      de
                      Inicio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha
                      de Fin
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Vacaciones de
                      Invierno</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acciones</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="year in academicYears" :key="year.id">
                    <td class="px-6 py-4 whitespace-nowrap">{{ year.year }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(year.start_date) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(year.end_date) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      {{ formatDate(year.winter_break_start) }} - {{ formatDate(year.winter_break_end) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <Link :href="route('academic-years.edit', year.id)"
                        class="text-indigo-600 hover:text-indigo-900 mr-4">
                      Editar
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { formatDate } from '../../utils/date'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue';
import { hasPermission } from '@/utils/permissions';

defineProps({
  academicYears: {
    type: Array,
    required: true
  },
  breadcrumbs: Array
})
</script>