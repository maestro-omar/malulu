<template>

  <Head :title="`Ciclo Lectivo ${academicYear.year}`" />

  <AuthenticatedLayout pageClass="q-pa-md row justify-center">
    <template #admin-header>
      <AdminHeader :title="`Detalles del Ciclo Lectivo ${academicYear.year}`" :edit="{
        show: hasPermission($page.props, 'academic-year.manage'),
        href: route('academic-years.edit', { 'academicYear': academicYear.id }),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'academic-year.manage'),
        onClick: destroy,
        label: 'Eliminar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="q-pa-md">
        <div class="row q-col-gutter-md">
          <div class="col-12">
            <!-- Academic Year Information Card -->
            <q-card class="q-mb-md">
              <q-card-section>
                <div class="text-h3 q-mb-md">Información del Ciclo Lectivo</div>
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Año" :value="academicYear.year" type="text" />
                  </div>
                </div>
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Fecha de Inicio" :value="academicYear.start_date" type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Fecha de Fin" :value="academicYear.end_date" type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Inicio de Vacaciones de Invierno" :value="academicYear.winter_break_start"
                      type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Fin de Vacaciones de Invierno" :value="academicYear.winter_break_end"
                      type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Estado" :value="academicYear.active" type="status" />
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- System Information Card -->
            <q-card>
              <q-card-section>
                <div class="text-h3 q-mb-md">Información del Sistema</div>
                <div class="row q-col-gutter-md">
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Fecha de Creación" :value="academicYear.created_at" type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Última Actualización" :value="academicYear.updated_at" type="date" />
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import DataFieldShow from '@/Components/DataFieldShow.vue'
import { hasPermission } from '@/Utils/permissions'
import { Head, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  academicYear: {
    type: Object,
    required: true
  },
})

const $page = usePage()

const destroy = () => {
  if (confirm("¿Está seguro que desea eliminar este ciclo lectivo?")) {
    router.delete(route("academic-years.destroy", props.academicYear.id))
  }
}
</script>