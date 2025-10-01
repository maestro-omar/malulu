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

      <StudentsTable :students="students" :courseId="getCourseSlug(course)" :schoolLevel="selectedLevel.code"
        :school="school" />

      <FilesTable :files="files" title="Archivos del curso"
        :newFileUrl="route('school.course.file.create', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course) })"
        :showFileBaseUrl="route('school.course.file.show', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' })"
        :editFileBaseUrl="route('school.course.file.edit', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' })"
        :replaceFileBaseUrl="route('school.course.file.replace', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: getCourseSlug(course), file: '##' })"
        :canDownload="true" />

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
  teachers: {
    type: Array,
    required: true,
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