<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="school" size="sm" color="secondary" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>
    </template>

    <div class="row q-col-gutter-sm">
      <div class="col-12 col-md-6">
        <div class="row q-col-gutter-sm">
          <!-- <div class="col-lg-2 col-md-3 col-4">
            <DataFieldShow label="Nivel Escolar">
              <template #slotValue>
                <SchoolLevelBadge :level="course.school_level" />
              </template>
            </DataFieldShow>
          </div> -->
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
          <!-- <div class="col-lg-2 col-md-3 col-4">
            <DataFieldShow label="Nombre" :value="course.name" type="text" />
          </div> -->
        </div>
        <div class="row q-col-gutter-sm">
          <div class="col-md-4 col-6">
            <DataFieldShow label="Fecha de Inicio" :value="course.start_date" type="date" />
          </div>
          <div class="col-md-4 col-6">
            <DataFieldShow label="Fecha de Fin" :value="course.end_date" type="date" />
          </div>
        </div>
        <div class="row q-col-gutter-sm">
          <div class="col-md-4 col-6">
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
          <div class="col-md-4 col-6">
            <DataFieldShow label="Curso/s siguiente/s">
              <template #slotValue>
                <span v-if="!(course.next_courses && course.next_courses.length)">-</span>
                <Link v-for="oneNext in (course.next_courses || [])"
                  :href="route('school.course.show', { 'school': school.slug, 'schoolLevel': selectedLevel.code, 'idAndLabel': getCourseSlug(oneNext) })"
                  class="text-primary">
                {{ oneNext.nice_name }}
                </Link>
              </template>
            </DataFieldShow>
          </div>
        </div>
        <div class="row q-col-gutter-sm">
          <div class="col-lg-2 col-md-3 col-4">
            <DataFieldShow label="Estado" :value="course.active" type="status" />
          </div>
        </div>
      </div>
      <div v-if="schedule" class="col-12 col-md-6 course-schedule-col">
        <CourseSchedule :schedule="schedule" :container="false" class="q-mb-md" />
      </div>

    </div>
  </q-expansion-item>

</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import DataFieldShow from '@/Components/DataFieldShow.vue'
import CourseSchedule from '@/Components/admin/CourseSchedule.vue'
// import SchoolLevelBadge from '@/Components/Badges/SchoolLevelBadge.vue'
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue'
import { getCourseSlug } from '@/Utils/strings'

const props = defineProps({
  title: { type: String, default: 'Datos básicos' },
  course: { type: Object, required: true },
  schedule: { type: Object, required: false },
  school: { type: Object, required: true },
  selectedLevel: { ty0e: Object, required: true },
})
</script>
