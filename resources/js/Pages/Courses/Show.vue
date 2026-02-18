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
        <template #additional-buttons>
          <q-btn v-if="hasPermission($page.props, 'course.manage')" size="sm"
            :href="route('school.course.schedule.edit', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course) })"
            color="secondary">
            Horario
          </q-btn>
          <q-btn v-if="course.active" size="sm"
            :href="route('school.course.attendance.edit', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course) })"
            color="lime-7">
            Asistencia
          </q-btn>
          <CourseExportPopover :course="course" :school="school" :schoolLevel="selectedLevel" />
        </template>
      </AdminHeader>
    </template>

    <template #main-page-content>
      <!-- Course Information Components -->
      <CourseBasicData :schedule="schedule" :course="course" :school="school" :selectedLevel="selectedLevel" class="q-mb-md" />
      
      <!-- Teachers Card -->
      <TeachersTable :teachers="teachers" :courseId="getCourseSlug(course)" :schoolLevel="selectedLevel.code"
        :school="school" />

      <TeachersTable
        v-if="pastTeachers && pastTeachers.length > 0"
        title="Docentes que ya no están en el curso"
        :teachers="pastTeachers"
        :courseId="getCourseSlug(course)"
        :schoolLevel="selectedLevel.code"
        :school="school"
        :pastMode="true"
      />

      <StudentsTable :students="students" :courseId="getCourseSlug(course)" :schoolLevel="selectedLevel.code"
        :school="school" />

      <StudentsTable
        v-if="pastStudents && pastStudents.length > 0"
        title="Estudiantes que ya no están en el curso"
        :students="pastStudents"
        :courseId="getCourseSlug(course)"
        :schoolLevel="selectedLevel.code"
        :school="school"
        :pastMode="true"
      />

      <FilesTable :files="files" title="Archivos del curso"
        :newFileUrl="hasPermission($page.props, 'course.manage') ? route('school.course.file.create', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course) }) : null"
        :showFileBaseUrl="route('school.course.file.show', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' })"
        :editFileBaseUrl="hasPermission($page.props, 'course.manage') ? route('school.course.file.edit', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' }) : null"
        :replaceFileBaseUrl="hasPermission($page.props, 'course.manage') ? route('school.course.file.replace', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' }) : null"
        :canDownload="true" />

      <SystemTimestamp :row="course" />
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link, Head, router } from '@inertiajs/vue3'
import FilesTable from '@/Components/admin/FilesTable.vue';
import SystemTimestamp from '@/Components/admin/SystemTimestamp.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import CourseBasicData from '@/Components/admin/CourseBasicData.vue'
import { hasPermission } from '@/Utils/permissions';
import { getCourseSlug } from '@/Utils/strings';
import StudentsTable from '@/Components/admin/StudentsTable.vue';
import TeachersTable from '@/Components/admin/TeachersTable.vue';
import CourseExportPopover from '@/Components/admin/CourseExportPopover.vue';

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
  pastStudents: {
    type: Array,
    default: () => [],
  },
  teachers: {
    type: Array,
    required: true,
  },
  pastTeachers: {
    type: Array,
    default: () => [],
  },
  files: {
    type: Array
  },
  schedule:{
    type: Object,
  }
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