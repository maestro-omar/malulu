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

    <div class="container">
      <!-- Flash Messages -->
      <FlashMessages :error="flash?.error" :success="flash?.success" />

      <div class="table__wrapper">
        <div class="table__container">
          <!-- Desktop Table View -->
          <div class="table__desktop">
            <table class="table__table">
              <thead class="table__thead">
                <tr>
                  <th class="table__th">Año</th>
                  <th class="table__th">Fecha de Inicio</th>
                  <th class="table__th">Fecha de Fin</th>
                  <th class="table__th">Vacaciones de Invierno</th>
                  <th class="table__th">Acciones</th>
                </tr>
              </thead>
              <tbody class="table__tbody">
                <tr
                  v-for="(year, index) in academicYears"
                  :key="year.id"
                  :class="{
                    'table__tr--even': index % 2 === 0,
                    'table__tr--odd': index % 2 === 1
                  }"
                >
                  <td class="table__td table__year">{{ year.year }}</td>
                  <td class="table__td table__date">{{ formatDate(year.start_date) }}</td>
                  <td class="table__td table__date">{{ formatDate(year.end_date) }}</td>
                  <td class="table__td table__break">
                    {{ formatDate(year.winter_break_start) }} - {{ formatDate(year.winter_break_end) }}
                  </td>
                  <td class="table__td table__actions">
                    <Link :href="route('academic-years.edit', year.id)">
                      Editar
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View -->
          <div class="table__mobile">
            <div
              v-for="(year, index) in academicYears"
              :key="year.id"
              :class="{
                'table__card--even': index % 2 === 0,
                'table__card--odd': index % 2 === 1
              }"
              class="table__card"
            >
              <div class="table__card-header">
                <div class="table__card-user">
                  <div class="table__card-info">
                    <h3>{{ year.year }}</h3>
                    <p>Ciclo Lectivo</p>
                  </div>
                </div>
                <div class="table__card-actions">
                  <Link :href="route('academic-years.edit', year.id)">
                    Editar
                  </Link>
                </div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Fecha de Inicio:</div>
                <div class="table__card-content">{{ formatDate(year.start_date) }}</div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Fecha de Fin:</div>
                <div class="table__card-content">{{ formatDate(year.end_date) }}</div>
              </div>
              <div class="table__card-section">
                <div class="table__card-label">Vacaciones de Invierno:</div>
                <div class="table__card-content">
                  {{ formatDate(year.winter_break_start) }} - {{ formatDate(year.winter_break_end) }}
                </div>
              </div>
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
import FlashMessages from '@/Components/admin/FlashMessages.vue';

defineProps({
  academicYears: {
    type: Array,
    required: true
  },
  breadcrumbs: Array
})
</script>