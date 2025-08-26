<template>
  <Head :title="`Detalle del Curso ${course.nice_name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Detalle del Curso ${course.nice_name}`" :edit="{
        show: hasPermission($page.props, 'course.manage'),
        href: route('school.course.edit', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course) }),
        label: 'Editar'
      }" :del="{
        show: hasPermission($page.props, 'course.manage'),
        onClick: destroy,
        label: 'Eliminar'
      }">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="q-pa-md">
        <div class="row q-col-gutter-sm">
          <div class="col-12">
            <!-- Course Information Card -->
            <q-card class="q-mb-md">
              <q-card-section>
                <div class="text-h3 q-mb-md">Información del Curso</div>
                <div class="row q-col-gutter-sm">
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Curso" :value="course.nice_name" type="text" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Escuela" :value="`${course.school.name} (${course.school.cue})`" type="text" />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-item>
                      <q-item-section>
                        <q-item-label caption class="text-h5">Nivel Escolar</q-item-label>
                        <q-item-label class="text-h4">
                          <SchoolLevelBadge :level="course.school_level" />
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </div>
                  <div class="col-12 col-md-6">
                    <q-item>
                      <q-item-section>
                        <q-item-label caption class="text-h5">Turno</q-item-label>
                        <q-item-label class="text-h4">
                          <SchoolShiftBadge :shift="course.school_shift" />
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Fecha de Inicio" :value="course.start_date" type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Fecha de Fin" :value="course.end_date" type="date" />
                  </div>
                  <div class="col-12 col-md-6">
                    <DataFieldShow label="Estado" :value="course.active" type="status" />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-item>
                      <q-item-section>
                        <q-item-label caption class="text-h5">Curso Anterior</q-item-label>
                        <q-item-label class="text-h4">
                          <Link v-if="course.previous_course"
                            :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course.previous_course) })"
                            class="text-primary">
                            {{ course.previous_course.nice_name }}
                          </Link>
                          <span v-else>-</span>
                        </q-item-label>
                      </q-item-section>
                    </q-item>
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- Teachers Card -->
            <q-card class="q-mb-md">
              <q-card-section>
                <div class="text-h3 q-mb-md">Docentes</div>
                <TeachersTable :teachers="teachers" />
              </q-card-section>
            </q-card>

            <!-- Students Card -->
            <q-card>
              <q-card-section>
                <div class="text-h3 q-mb-md">Estudiantes</div>
                <StudentsTable 
                  :students="students" 
                  :courseId="getCourseSlug(course)"
                  :schoolLevel="selectedLevel.code"
                  :schoolSlug="school.slug"
                />
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import DataFieldShow from '@/Components/DataFieldShow.vue'
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import { hasPermission } from '@/Utils/permissions';
import { getCourseSlug } from '@/Utils/strings';
import StudentsTable from '@/Components/admin/StudentsTable.vue';
import TeachersTable from '@/Components/admin/TeachersTable.vue';

const props = defineProps({
  course: {
    type: Object,
    required: true,
  },
  school: {
    type: Object,
    required: true,
  },
  selectedLevel: {
    type: Object,
    required: true,
  },
  students:{  
    type: Array,  
    required: true,
  },
  teachers: {
    type: Array,
    required: true,
  },
})

const destroy = () => {
  if (confirm("¿Está seguro que desea eliminar este curso?")) {
    router.delete(route("school.course.destroy", { 
      'school': props.school.slug, 
      'schoolLevel': props.selectedLevel.code, 
      'idAndLabel': getCourseSlug(props.course) 
    }))
  }
}
</script>