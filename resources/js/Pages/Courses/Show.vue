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
      <!-- Course Information Card -->
      <q-card class="q-mb-md">
        <q-card-section>
          <div class="row">
            <div class="col-6">
              <div class="text-h3 q-mb-md">Datos básicos</div>
              <div class="row q-col-gutter-sm">
                <div class="col-lg-2 col-md-3 col-4">
                  <DataFieldShow label="Nivel Escolar">
                    <template #slotValue>
                      <SchoolLevelBadge :level="course.school_level" />
                    </template>
                  </DataFieldShow>
                </div>
                <div class="col-lg-2 col-md-3 col-4">
                  <DataFieldShow label="Turno">
                    <template #slotValue>
                      <SchoolShiftBadge :shift="course.school_shift" />
                    </template>
                  </DataFieldShow>
                </div>
                <div class="col-lg-2 col-md-3 col-4">
                  <DataFieldShow label="Número" :value="course.number" type="text" />
                </div>
                <div class="col-lg-2 col-md-3 col-4">
                  <DataFieldShow label="Letra" :value="course.letter" type="text" />
                </div>
                <div class="col-lg-2 col-md-3 col-4">
                  <DataFieldShow label="Nombre" :value="course.name" type="text" />
                </div>
              </div>
              <div class="row q-col-gutter-sm">
                <div class="col-md-3 col-4">
                  <DataFieldShow label="Fecha de Inicio" :value="course.start_date" type="date" />
                </div>
                <div class="col-md-3 col-4">
                  <DataFieldShow label="Fecha de Fin" :value="course.end_date" type="date" />
                </div>
                <div class="col-md-3 col-4">
                  <DataFieldShow label="Curso Anterior">
                    <template #slotValue>
                      <span v-if="!course.previous_course">-</span>
                      <Link v-if="course.previous_course"
                        :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course.previous_course) })"
                        class="text-primary">
                      {{ course.previous_course.nice_name }}
                      </Link>
                    </template>
                  </DataFieldShow>
                </div>
                <div class="col-md-3 col-4">
                  <DataFieldShow label="Curso/s siguiente/s">
                    <template #slotValue>
                      <span v-if="course.next_courses.length === 0">-</span>
                      <Link v-for="oneNext in course.next_courses"
                        :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(oneNext) })"
                        class="text-primary">
                      {{ oneNext.nice_name }}
                      </Link>
                    </template>
                  </DataFieldShow>
                </div>
                <div class="col-lg-2 col-md-3 col-4">
                  <DataFieldShow label="Estado" :value="course.active" type="status" />
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="text-h3 q-mb-md">Horarios</div>
              <div class="row q-col-gutter-sm">
                LU MA MI JU VI
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Teachers Card -->
      <TeachersTable :teachers="teachers" :courseId="getCourseSlug(course)" :schoolLevel="selectedLevel.code"
        :school="school" />

      <StudentsTable :students="students" :courseId="getCourseSlug(course)" :schoolLevel="selectedLevel.code"
        :school="school" />

      <FilesTable :files="files" title="Archivos del curso"
        :newFileUrl="route('school.course.file.create', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course) })"
        :showFileBaseUrl="route('school.course.file.show', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' })"
        :editFileBaseUrl="route('school.course.file.edit', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' })"
        :replaceFileBaseUrl="route('school.course.file.replace', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' })"
        canDownload="true" />

      <SystemTimestamp :row="course" />
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head } from '@inertiajs/vue3'
import FilesTable from '@/Components/admin/FilesTable.vue';
import SystemTimestamp from '@/Components/admin/SystemTimestamp.vue';
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
  students: {
    type: Array,
    required: true,
  },
  teachers: {
    type: Array,
    required: true,
  },
  files: {
    type: Array
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