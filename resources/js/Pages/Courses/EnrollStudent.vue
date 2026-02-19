<template>

  <Head :title="`Inscribir estudiante - ${course.nice_name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Inscribir estudiante - ${course.nice_name}`" />
    </template>

    <template #main-page-content>
      <div class="container q-pa-md">
        <CourseBasicData :course="course" :school="school" :selectedLevel="selectedLevel" class="q-mb-md" />

        <q-banner v-if="enrollmentError" class="bg-negative text-white q-mb-md" rounded>
          {{ enrollmentError }}
        </q-banner>

        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <p class="text-body1 q-mb-md">
              Busque estudiantes existentes de la escuela para inscribirlos en este curso.
            </p>
            <div class="row q-col-gutter-md items-center">
              <div class="col-grow">
                <q-input v-model="searchQuery" dense outlined clearable placeholder="Nombre, apellido o DNI..."
                  @keyup.enter="doSearch" @update:model-value="scheduleSearch">
                  <template #append>
                    <q-icon name="search" class="cursor-pointer" @click="doSearch" />
                  </template>
                </q-input>
              </div>
              <div class="col-auto">
                <q-btn color="primary" icon="person_add" :href="createStudentUrl" target="_blank" no-wrap>
                  Crear estudiante
                </q-btn>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <q-card v-if="searchDone" flat bordered>
          <q-card-section>
            <div v-if="searching" class="text-center q-pa-lg">
              <q-spinner size="lg" />
              <div class="q-mt-sm">Buscando...</div>
            </div>
            <template v-else>
              <div v-if="results.length === 0" class="text-center text-grey-7 q-pa-lg">
                No se encontraron estudiantes. Escriba al menos un término para buscar o cree un nuevo estudiante.
              </div>
              <q-table v-else :rows="results" :columns="columns" row-key="id" flat dense hide-pagination
                class="mll-table mll-table--students striped-table"
                :rows-per-page-options="[0]">
                <template #body-cell-picture="props">
                  <q-td :props="props">
                    <q-avatar size="40px">
                      <img :src="props.row.picture || noImage" :alt="props.row.name" />
                    </q-avatar>
                  </q-td>
                </template>
                <template #body-cell-firstname="props">
                  <q-td :props="props">
                    <a :href="route_school_student(school, props.row, 'show')" class="text-primary" target="_blank">
                      {{ props.row.firstname }}
                    </a>
                  </q-td>
                </template>
                <template #body-cell-id_number="props">
                  <q-td :props="props">
                    {{ formatNumber(props.row.id_number) }}
                  </q-td>
                </template>
                <template #body-cell-birthdate="props">
                  <q-td :props="props">
                    <BirthdateAge v-if="props.row.birthdate" :birthdate="props.row.birthdate" />
                    <span v-else>—</span>
                  </q-td>
                </template>
                <template #body-cell-gender="props">
                  <q-td :props="props">
                    <GenderBadge :gender="props.row.gender" />
                  </q-td>
                </template>
                <template #body-cell-current_course="props">
                  <q-td :props="props">
                    {{ props.row.current_course ? props.row.current_course.nice_name : '—' }}
                  </q-td>
                </template>
                <template #body-cell-actions="props">
                  <q-td :props="props">
                    <q-btn size="sm" color="green" icon="person_add" label="Inscribir" @click="openEnroll(props.row)" />
                  </q-td>
                </template>
              </q-table>
            </template>
          </q-card-section>
        </q-card>

        <!-- Dialog: already in this course -->
        <q-dialog v-model="showAlreadyInCourse" persistent>
          <q-card style="min-width: 320px">
            <q-card-section>
              <div class="text-h6">Ya inscripto</div>
              <div class="q-mt-sm">El estudiante ya está inscripto en este curso.</div>
            </q-card-section>
            <q-card-actions align="right">
              <q-btn flat label="Cerrar" color="primary" v-close-popup />
            </q-card-actions>
          </q-card>
        </q-dialog>

        <!-- Dialog: in other course - select end reason -->
        <q-dialog v-model="showEndReasonDialog" persistent>
          <q-card style="min-width: 360px">
            <q-card-section>
              <div class="text-h6">Finalizar curso anterior</div>
              <div class="q-mt-sm q-mb-md">
                El estudiante tiene curso actual: <strong>{{ selectedStudent?.current_course?.nice_name }}</strong>.
                Indique el motivo de finalización para inscribirlo en este curso.
              </div>
              <q-select v-model="selectedEndReasonId" :options="endReasonOptions" option-value="id" option-label="name"
                emit-value map-options outlined dense label="Motivo de finalización" class="q-mb-sm" />
              <q-input v-model="enrollmentReason" type="textarea" outlined dense label="Motivo de inscripción (opcional)"
                placeholder="Ej: Viene de otra escuela, Repite el grado" rows="2" class="q-mt-sm" />
            </q-card-section>
            <q-card-actions align="right">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn unelevated color="primary" label="Inscribir en este curso" :disable="!selectedEndReasonId"
                @click="submitEnrollWithReason" />
            </q-card-actions>
          </q-card>
        </q-dialog>

        <!-- Dialog: confirm enroll (no current course) -->
        <q-dialog v-model="showConfirmEnroll" persistent>
          <q-card style="min-width: 320px">
            <q-card-section>
              <div class="text-h6">Confirmar inscripción</div>
              <div class="q-mt-sm q-mb-md">
                ¿Inscribir a <strong>{{ selectedStudent ? `${selectedStudent.firstname} ${selectedStudent.lastname}` :
                  ''
                  }}</strong> en este curso?
              </div>
              <q-input v-model="enrollmentReason" type="textarea" outlined dense label="Motivo de inscripción (opcional)"
                placeholder="Ej: Viene de otra escuela, Repite el grado" rows="2" />
            </q-card-section>
            <q-card-actions align="right">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn unelevated color="primary" label="Inscribir" @click="submitEnroll" />
            </q-card-actions>
          </q-card>
        </q-dialog>
      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import CourseBasicData from '@/Components/admin/CourseBasicData.vue';
import BirthdateAge from '@/Components/admin/BirthdateAge.vue';
import GenderBadge from '@/Components/Badges/GenderBadge.vue';
import { getCourseSlug, formatNumber } from '@/Utils/strings';
import { route_school_student } from '@/Utils/routes';
import noImage from '@images/no-image-person.png';
import axios from 'axios';

const props = defineProps({
  course: { type: Object, required: true },
  school: { type: Object, required: true },
  selectedLevel: { type: Object, required: true },
  endReasons: { type: Array, default: () => [] },
  createStudentUrl: { type: String, required: true },
});

const page = usePage();
const enrollmentError = computed(() => {
  const e = page.props.errors?.enrollment;
  return Array.isArray(e) ? e[0] : e ?? null;
});

const searchUrl = computed(() =>
  route('school.course.students.search', {
    school: props.school.slug,
    schoolLevel: props.selectedLevel.code,
    idAndLabel: getCourseSlug(props.course),
  })
);

const storeUrl = computed(() =>
  route('school.course.student.store', {
    school: props.school.slug,
    schoolLevel: props.selectedLevel.code,
    idAndLabel: getCourseSlug(props.course),
  })
);

const searchQuery = ref('');
const results = ref([]);
const searching = ref(false);
const searchDone = ref(false);

let searchTimeout = null;
function scheduleSearch() {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(doSearch, 400);
}

async function doSearch() {
  const q = (searchQuery.value || '').trim();
  searchDone.value = true;
  if (!q) {
    results.value = [];
    return;
  }
  searching.value = true;
  try {
    const { data } = await axios.post(searchUrl.value, { search: q });
    results.value = data.students || [];
  } catch {
    results.value = [];
  } finally {
    searching.value = false;
  }
}

const columns = [
  { name: 'picture', label: '', field: 'picture', align: 'center', sortable: false, style: 'width: 80px' },
  { name: 'firstname', label: 'Nombre', field: 'firstname', align: 'left' },
  { name: 'lastname', label: 'Apellido', field: 'lastname', align: 'left' },
  { name: 'id_number', label: 'DNI', field: 'id_number', align: 'left' },
  { name: 'birthdate', label: 'Fecha de Nacimiento', field: 'birthdate', align: 'center' },
  { name: 'gender', label: 'Género', field: 'gender', align: 'center', style: 'width: 100px' },
  { name: 'current_course', label: 'Curso actual', field: 'current_course', align: 'left' },
  { name: 'actions', label: '', field: 'actions', align: 'right', sortable: false },
];

const endReasonOptions = computed(() =>
  props.endReasons.map((r) => ({ id: r.id, name: r.name }))
);

const selectedStudent = ref(null);
const showAlreadyInCourse = ref(false);
const showEndReasonDialog = ref(false);
const showConfirmEnroll = ref(false);
const selectedEndReasonId = ref(null);
const enrollmentReason = ref('');

function openEnroll(row) {
  selectedStudent.value = row;
  const current = row.current_course;
  const thisCourseId = props.course.id;

  if (current && current.id === thisCourseId) {
    showAlreadyInCourse.value = true;
    return;
  }
  if (current) {
    selectedEndReasonId.value = null;
    enrollmentReason.value = '';
    showEndReasonDialog.value = true;
    return;
  }
  enrollmentReason.value = '';
  showConfirmEnroll.value = true;
}

function submitEnroll() {
  if (!selectedStudent.value) return;
  showConfirmEnroll.value = false;
  const payload = { user_id: selectedStudent.value.id };
  if (enrollmentReason.value?.trim()) payload.enrollment_reason = enrollmentReason.value.trim();
  router.post(storeUrl.value, payload);
  selectedStudent.value = null;
  enrollmentReason.value = '';
}

function submitEnrollWithReason() {
  if (!selectedStudent.value || !selectedEndReasonId.value) return;
  showEndReasonDialog.value = false;
  const payload = {
    user_id: selectedStudent.value.id,
    end_reason_id: selectedEndReasonId.value,
  };
  if (enrollmentReason.value?.trim()) payload.enrollment_reason = enrollmentReason.value.trim();
  router.post(storeUrl.value, payload);
  selectedStudent.value = null;
  selectedEndReasonId.value = null;
  enrollmentReason.value = '';
}
</script>
