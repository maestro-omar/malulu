<template>
  <AuthenticatedLayout>

    <Head :title="`Horario - ${course.nice_name}`" />
    <template #admin-header>
      <AdminHeader :title="`Editar horario - ${course.nice_name}`" />
    </template>

    <template #main-page-content>
      <div class="container q-pb-lg">
        <CourseBasicData :course="course" :school="school" :selectedLevel="selectedLevel" class="q-mb-md" />

        <q-card class="admin-form__card">
          <q-card-section>
            <form @submit.prevent="submit" class="admin-form__container">
              <div class="schedule-edit">
                <div class="schedule-edit__table-wrapper">
                  <div class="schedule-edit__table">
                      <!-- Header -->
                      <div class="schedule-edit__header row">
                        <div class="schedule-edit__header-cell schedule-edit__header-cell--period">#</div>
                        <div class="schedule-edit__header-cell schedule-edit__header-cell--time">Horario</div>
                        <div v-for="day in days" :key="`h-${day}`"
                          class="schedule-edit__header-cell schedule-edit__header-cell--day col">
                          {{ dayNamesFullSchedule[day] || '' }}
                        </div>
                      </div>

                      <!-- Body: one row per time slot -->
                      <div v-for="row in scheduleRows" :key="row.id" class="schedule-edit__body-row row"
                        :class="{ 'schedule-edit__body-row--break': isBreakRow(row) }">
                        <!-- Break row: single merged cell -->
                        <template v-if="isBreakRow(row)">
                          <div class="schedule-edit__break-cell col-12">
                            {{ row.period }}: {{ row.time }}
                          </div>
                        </template>

                        <!-- Class period row: period, time, then one cell per day -->
                        <template v-else>
                          <div class="schedule-edit__period-cell">{{ row.period }}</div>
                          <div class="schedule-edit__time-cell">{{ row.time }}</div>
                          <div v-for="day in days" :key="`${row.id}-${day}`" class="schedule-edit__day-cell col">
                            <div class="schedule-edit__cell-fields">
                              <q-select :model-value="cellSubjectCode(day, row.id) || ''"
                                :options="subjectOptionsWithEmpty" option-value="code" option-label="name" emit-value
                                map-options dense outlined hide-bottom-space
                                :class="['schedule-edit__select', 'schedule-edit__select--subject', (cellSubjectCode(day, row.id)) && 'schedule-edit__select--' + cellSubjectCode(day, row.id)]"
                                :label="cellSubjectCode(day, row.id) ? '' : 'Materia'"
                                @update:model-value="(v) => onSubjectChange(day, row.id, v === '' ? null : v)" />
                              <q-select :model-value="cellTeacherId(day, row.id)"
                                :options="teacherOptionsForSubject(cellSubjectCode(day, row.id))" option-value="id"
                                option-label="name" emit-value map-options dense outlined hide-bottom-space
                                class="schedule-edit__select" label="Docente" clearable
                                @update:model-value="(v) => setCellTeacher(day, row.id, v)" />
                            </div>
                          </div>
                        </template>
                      </div>
                    </div>
                </div>

                <div v-if="scheduleSummary.length" class="schedule-edit__summary q-mt-md">
                  <div class="schedule-edit__summary-chips">
                    <span v-for="item in scheduleSummary" :key="item.code === '' ? '_empty' : item.code"
                      :class="['schedule-edit__summary-chip', item.code === '' ? 'schedule-edit__summary-chip--empty' : 'schedule-edit__summary-chip--' + item.code]">
                      {{ item.name }} ({{ item.count }})
                    </span>
                  </div>
                </div>

                <div class="q-mt-md row justify-end">
                  <q-btn type="button" flat color="grey"
                    :href="route('school.course.show', { school: school.slug, schoolLevel: selectedLevel.code, idAndLabel: course.id_and_label })">
                    Cancelar
                  </q-btn>
                  <q-btn type="submit" color="primary" :loading="form.processing" unelevated>
                    Guardar horario
                  </q-btn>
                </div>
              </div>
            </form>
          </q-card-section>
        </q-card>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue'
import AdminHeader from '@/Sections/AdminHeader.vue'
import CourseBasicData from '@/Components/admin/CourseBasicData.vue'
import { dayNamesFullSchedule } from '@/Utils/date'
import { getCourseSlug } from '@/Utils/strings'

const props = defineProps({
  course: { type: Object, required: true },
  school: { type: Object, required: true },
  selectedLevel: { type: Object, required: true },
  timeSlots: { type: Object, required: true },
  days: { type: Array, default: () => [1, 2, 3, 4, 5] },
  subjects: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  schedule: { type: Object, default: () => ({}) },
})

const form = useForm({
  schedule: {},
})

const subjectOptionsWithEmpty = computed(() => [
  { code: '', name: '—' },
  ...(props.subjects || []).map((s) => ({ code: s.code, name: s.name })),
])

const scheduleSummary = computed(() => {
  const counts = {}
  props.days.forEach((day) => {
    scheduleRows.value.forEach((row) => {
      if (isBreakRow(row)) return
      const code = cellSubjectCode(day, row.id) ?? ''
      counts[code] = (counts[code] || 0) + 1
    })
  })
  const result = []
  if (counts[''] !== undefined) {
    result.push({ code: '', name: 'Sin asignar', count: counts[''] })
  }
  ; (props.subjects || []).forEach((s) => {
    if (counts[s.code] !== undefined) {
      result.push({ code: s.code, name: s.name, count: counts[s.code] })
    }
  })
  return result
})


function buildScheduleRows() {
  const timeSlotsData = props.timeSlots || {}
  const slots = []
  Object.keys(timeSlotsData).forEach((key) => {
    const times = timeSlotsData[key]
    if (Array.isArray(times) && times.length >= 2) {
      slots.push({
        id: key,
        start: times[0],
        end: times[1],
      })
    }
  })
  slots.sort((a, b) => {
    const [hA, mA] = a.start.split(':').map(Number)
    const [hB, mB] = b.start.split(':').map(Number)
    return hA * 60 + mA - (hB * 60 + mB)
  })
  return slots.map((slot) => ({
    id: slot.id,
    period: formatPeriodLabel(slot.id),
    time: `${slot.start} - ${slot.end}`,
  }))
}

const scheduleRows = computed(() => buildScheduleRows())

function formatPeriodLabel(periodKey) {
  if (String(periodKey).includes('break')) return 'Recreo'
  if (String(periodKey).includes('lunch')) return 'Almuerzo'
  return periodKey
}

function isBreakRow(row) {
  return String(row.id).includes('break') || String(row.id).includes('lunch')
}

function ensureScheduleShape() {
  const s = form.schedule
  props.days.forEach((day) => {
    if (!s[day]) s[day] = {}
    scheduleRows.value.forEach((row) => {
      if (!isBreakRow(row) && s[day][row.id] === undefined) {
        const existing = props.schedule[day] && props.schedule[day][row.id]
        s[day][row.id] = existing
          ? {
            subject: existing.subject ? { code: existing.subject.code, name: existing.subject.name } : null,
            teacher: existing.teacher || null,
          }
          : null
      }
    })
  })
}

onMounted(() => {
  const initial = {}
  const raw = props.schedule || {}
  props.days.forEach((day) => {
    const dayKey = raw[day] !== undefined ? day : String(day)
    initial[day] = raw[dayKey] ? JSON.parse(JSON.stringify(raw[dayKey])) : {}
  })
  form.schedule = initial
  ensureScheduleShape()
})

function cellSubjectCode(day, slotId) {
  const cell = form.schedule[day] && form.schedule[day][slotId]
  return cell && cell.subject ? cell.subject.code : null
}

function cellTeacherId(day, slotId) {
  const cell = form.schedule[day] && form.schedule[day][slotId]
  return cell && cell.teacher && cell.teacher.id != null ? cell.teacher.id : null
}

function teacherOptionsForSubject(subjectCode) {
  const list = (props.teachers || []).slice()
  if (!subjectCode) return list
  list.sort((a, b) => {
    const aMatch = (a.rel_subject && a.rel_subject.code === subjectCode) ? 1 : 0
    const bMatch = (b.rel_subject && b.rel_subject.code === subjectCode) ? 1 : 0
    return bMatch - aMatch
  })
  return list
}

function onSubjectChange(day, slotId, subjectCode) {
  if (!form.schedule[day]) form.schedule[day] = {}
  const cell = form.schedule[day][slotId]
  const subject = subjectCode
    ? (props.subjects || []).find((s) => s.code === subjectCode)
    : null
  const subjectObj = subject ? { code: subject.code, name: subject.name } : null

  const existingTeacherForSubject = subjectCode
    ? findTeacherAlreadyUsedForSubject(subjectCode, day, slotId)
    : null

  form.schedule[day][slotId] = {
    subject: subjectObj,
    teacher: existingTeacherForSubject || (cell && cell.teacher) || null,
  }
}

function findTeacherAlreadyUsedForSubject(subjectCode, excludeDay, excludeSlotId) {
  for (const d of props.days) {
    for (const row of scheduleRows.value) {
      if (isBreakRow(row)) continue
      if (d === excludeDay && row.id === excludeSlotId) continue
      const cell = form.schedule[d] && form.schedule[d][row.id]
      if (cell && cell.subject && cell.subject.code === subjectCode && cell.teacher) {
        return cell.teacher
      }
    }
  }
  return null
}

function setCellTeacher(day, slotId, teacherId) {
  if (!form.schedule[day]) form.schedule[day] = {}
  const cell = form.schedule[day][slotId] || {}
  const teacher = teacherId
    ? (props.teachers || []).find((t) => t.id === teacherId) || null
    : null
  form.schedule[day][slotId] = {
    subject: cell.subject || null,
    teacher,
  }
}

function submit() {
  const payload = {}
  props.days.forEach((day) => {
    payload[day] = {}
    scheduleRows.value.forEach((row) => {
      if (!isBreakRow(row)) {
        const cell = form.schedule[day] && form.schedule[day][row.id]
        if (cell && (cell.subject || cell.teacher)) {
          payload[day][row.id] = {
            subject: cell.subject || null,
            teacher: cell.teacher || null,
          }
        } else {
          payload[day][row.id] = null
        }
      }
    })
  })
  form.schedule = payload
  form.put(
    route('school.course.schedule.update', {
      school: props.school.slug,
      schoolLevel: props.selectedLevel.code,
      idAndLabel: props.course.id_and_label,
    })
  )
}
</script>
