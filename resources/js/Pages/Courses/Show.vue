<template>

  <Head :title="`Detalle del Curso ${course.number}º ${course.letter}`" />

  <AuthenticatedLayout>
    <template #header>
      <AdminHeader :breadcrumbs="breadcrumbs" :title="`Detalle del Curso ${course.number}º ${course.letter}`" 
      :edit="{
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

    <div class="container">
      <div class="admin-detail__wrapper">
        <div class="admin-detail__card">
          <div class="admin-detail__content">
            <div>
              <div class="admin-detail__section">
                <h2 class="admin-detail__section-title">Información del Curso</h2>
                <div class="admin-detail__field-grid">
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Número de Curso:</label>
                    <div class="admin-detail__value">{{ course.number }}</div>
                  </div>
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Letra de Curso:</label>
                    <div class="admin-detail__value">{{ course.letter }}</div>
                  </div>
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Escuela:</label>
                    <div class="admin-detail__value">{{ course.school.name }} ({{ course.school.cue }})</div>
                  </div>
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Nivel Escolar:</label>
                    <div class="admin-detail__badge-container">
                      <SchoolLevelBadge :level="course.school_level" />
                    </div>
                  </div>
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Turno:</label>
                    <div class="admin-detail__badge-container">
                      <SchoolShiftBadge :shift="course.school_shift" />
                    </div>
                  </div>
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Fecha de Inicio:</label>
                    <div class="admin-detail__value">{{ formatDate(course.start_date) }}</div>
                  </div>
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Fecha de Fin:</label>
                    <div class="admin-detail__value">{{ course.end_date ? formatDate(course.end_date) : '-' }}</div>
                  </div>
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Estado:</label>
                    <div class="admin-detail__value">
                      <span :class="{
                        'admin-detail__status': true,
                        'admin-detail__status--active': course.active,
                        'admin-detail__status--inactive': !course.active,
                      }">
                        {{ course.active ? 'Activo' : 'Inactivo' }}
                      </span>
                    </div>
                  </div>
                  <div class="admin-detail__field">
                    <label class="admin-detail__label">Curso Anterior:</label>
                    <div class="admin-detail__value">
                      <Link v-if="course.previous_course"
                        :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(course.previous_course) })"
                        class="admin-detail__link">
                      {{ course.previous_course.number }} º {{ course.previous_course.letter }}
                      </Link>
                      <span v-else>-</span>
                    </div>
                  </div>
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
import { Link, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import { formatDate } from '../../utils/date'
import { hasPermission } from '@/utils/permissions';
import { getCourseSlug } from '../../utils/strings'

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
  breadcrumbs: Array,
})
</script>