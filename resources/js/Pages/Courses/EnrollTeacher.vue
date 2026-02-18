<template>

  <Head :title="`Asignar docente - ${course.nice_name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Asignar docente - ${course.nice_name}`" />
    </template>

    <template #main-page-content>
      <div class="container q-pa-md">
        <CourseBasicData :course="course" :school="school" :selectedLevel="selectedLevel" class="q-mb-md" />

        <q-banner v-if="assignmentError" class="bg-negative text-white q-mb-md" rounded>
          {{ assignmentError }}
        </q-banner>

        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <p class="text-body1 q-mb-md">
              Busque personal de la escuela para asignarlo como docente a este curso.
            </p>
            <div class="row q-col-gutter-md items-center">
              <div class="col-grow">
                <q-input v-model="searchQuery" dense outlined clearable placeholder="Nombre, apellido o email..."
                  @keyup.enter="doSearch" @update:model-value="scheduleSearch">
                  <template #append>
                    <q-icon name="search" class="cursor-pointer" @click="doSearch" />
                  </template>
                </q-input>
              </div>
              <div class="col-auto">
                <q-btn color="primary" icon="person_add" :href="createStaffUrl" target="_blank" no-wrap>
                  Crear docente
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
                No se encontró personal. Escriba al menos un término para buscar o cree un nuevo docente.
              </div>
              <q-table v-else :rows="results" :columns="columns" row-key="id" flat dense hide-pagination
                class="mll-table mll-table--teachers striped-table"
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
                    <a :href="route_school_staff(school, props.row, 'show')" class="text-primary" target="_blank">
                      {{ props.row.firstname }}
                    </a>
                  </q-td>
                </template>
                <template #body-cell-email="props">
                  <q-td :props="props">
                    {{ props.row.email || '—' }}
                  </q-td>
                </template>
                <template #body-cell-role="props">
                  <q-td :props="props">
                    <RoleBadge v-if="props.row.role" :role="props.row.role" size="sm" />
                    <span v-else>—</span>
                  </q-td>
                </template>
                <template #body-cell-subject="props">
                  <q-td :props="props">{{ props.row.subject || '—' }}</q-td>
                </template>
                <template #body-cell-shifts="props">
                  <q-td :props="props">
                    <template v-if="props.row.shifts && props.row.shifts.length">
                      <SchoolShiftBadge
                        v-for="shift in props.row.shifts"
                        :key="shift.id"
                        :shift="shift"
                        size="sm"
                        class="q-mr-xs"
                      />
                    </template>
                    <span v-else>—</span>
                  </q-td>
                </template>
                <template #body-cell-courses="props">
                  <q-td :props="props">
                    <template v-if="(props.row.courses || []).length">
                      <span v-for="(c, i) in props.row.courses" :key="c.id">
                        <a v-if="c.url" :href="c.url" target="_blank" class="text-primary">{{ c.nice_name }}</a>
                        <span v-else>{{ c.nice_name }}</span>
                        <span v-if="i < props.row.courses.length - 1">, </span>
                      </span>
                    </template>
                    <span v-else>—</span>
                  </q-td>
                </template>
                <template #body-cell-actions="props">
                  <q-td :props="props">
                    <template v-if="props.row.already_assigned">
                      <q-btn size="sm" color="negative" icon="person_remove" label="Quitar" @click="openRemove(props.row)" />
                    </template>
                    <q-btn v-else size="sm" color="green" icon="person_add" label="Asignar" @click="openAssign(props.row)" />
                  </q-td>
                </template>
              </q-table>
            </template>
          </q-card-section>
        </q-card>

        <!-- Dialog: confirm assign -->
        <q-dialog v-model="showConfirmAssign" persistent>
          <q-card style="min-width: 320px">
            <q-card-section>
              <div class="text-h6">Asignar docente</div>
              <div class="q-mt-sm">
                ¿Asignar a <strong>{{ selectedTeacher ? `${selectedTeacher.firstname} ${selectedTeacher.lastname}` : '' }}</strong> a este curso?
              </div>
              <q-checkbox v-model="inCharge" label="A cargo del curso" class="q-mt-md" />
            </q-card-section>
            <q-card-actions align="right">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn unelevated color="primary" label="Asignar" @click="submitAssign" />
            </q-card-actions>
          </q-card>
        </q-dialog>

        <!-- Dialog: confirm remove -->
        <q-dialog v-model="showConfirmRemove" persistent>
          <q-card style="min-width: 320px">
            <q-card-section>
              <div class="text-h6">Quitar docente del curso</div>
              <div class="q-mt-sm">
                ¿Quitar a <strong>{{ teacherToRemove ? `${teacherToRemove.firstname} ${teacherToRemove.lastname}` : '' }}</strong> de este curso?
              </div>
            </q-card-section>
            <q-card-actions align="right">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn unelevated color="negative" label="Quitar" @click="submitRemove" />
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
import { getCourseSlug } from '@/Utils/strings';
import { route_school_staff } from '@/Utils/routes';
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import SchoolShiftBadge from '@/Components/Badges/SchoolShiftBadge.vue';
import noImage from '@images/no-image-person.png';
import axios from 'axios';

const props = defineProps({
  course: { type: Object, required: true },
  school: { type: Object, required: true },
  selectedLevel: { type: Object, required: true },
  createStaffUrl: { type: String, required: true },
});

const page = usePage();
const assignmentError = computed(() => {
  const e = page.props.errors?.assignment;
  return Array.isArray(e) ? e[0] : e ?? null;
});

const searchUrl = computed(() =>
  route('school.course.teachers.search', {
    school: props.school.slug,
    schoolLevel: props.selectedLevel.code,
    idAndLabel: getCourseSlug(props.course),
  })
);

const storeUrl = computed(() =>
  route('school.course.teacher.store', {
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
    const url = `${searchUrl.value}?search=${encodeURIComponent(q)}`;
    const { data } = await axios.post(url, { search: q });
    results.value = data.teachers || [];
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
  { name: 'email', label: 'Email', field: 'email', align: 'left' },
  { name: 'role', label: 'Rol', field: 'role', align: 'left', style: 'width: 140px' },
  { name: 'subject', label: 'Materia', field: 'subject', align: 'left', style: 'width: 120px' },
  { name: 'shifts', label: 'Turno', field: 'shifts', align: 'center', style: 'width: 120px' },
  { name: 'courses', label: 'Cursos actuales', field: 'courses', align: 'left' },
  { name: 'actions', label: '', field: 'actions', align: 'right', sortable: false },
];

const selectedTeacher = ref(null);
const showConfirmAssign = ref(false);
const inCharge = ref(false);
const teacherToRemove = ref(null);
const showConfirmRemove = ref(false);

function openAssign(row) {
  selectedTeacher.value = row;
  inCharge.value = false;
  showConfirmAssign.value = true;
}

function submitAssign() {
  if (!selectedTeacher.value) return;
  showConfirmAssign.value = false;
  router.post(storeUrl.value, {
    user_id: selectedTeacher.value.id,
    in_charge: inCharge.value,
  });
  selectedTeacher.value = null;
}

function openRemove(row) {
  teacherToRemove.value = row;
  showConfirmRemove.value = true;
}

function submitRemove() {
  if (!teacherToRemove.value?.rel_id) return;
  showConfirmRemove.value = false;
  const url = route('school.course.teacher.remove', {
    school: props.school.slug,
    schoolLevel: props.selectedLevel.code,
    idAndLabel: getCourseSlug(props.course),
    teacherCourse: teacherToRemove.value.rel_id,
  });
  router.delete(url);
  teacherToRemove.value = null;
}
</script>
