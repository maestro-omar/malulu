<template>

  <Head :title="pageTitle" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="headerTitle"></AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">

        <UserInformation :user="user" :genders="genders" :editable-picture="editablePicture"
          :public-view="publicView" />

        <form @submit.prevent="handleSubmit" class="admin-form__container">
          <q-expansion-item expand-separator default-opened class="mll-table-expansion">
            <template v-slot:header>
              <q-item-section avatar>
                <q-icon name="medical_information" size="sm" color="green" />
              </q-item-section>

              <q-item-section align="left">
                Diagnósticos
              </q-item-section>
            </template>

            <q-table class="mll-table mll-table--diagnoses striped-table" dense :rows="tableRows" :columns="columns"
              row-key="id" :pagination="{ rowsPerPage: 0 }" hide-pagination>
              <!-- Category column -->
              <template #body-cell-category="props">
                <q-td :props="props">
                  <DiagnosisCategoryBadge v-if="props.row.category" :category="props.row.category"
                    :category-name="props.row.category_name" size="sm" />
                </q-td>
              </template>

              <!-- Name column -->
              <template #body-cell-name="props">
                <q-td :props="props">
                  {{ props.row.name }}
                </q-td>
              </template>

              <!-- Date input column -->
              <template #body-cell-diagnosed_at="props">
                <q-td :props="props">
                  <TextInput v-if="canEdit && props.row.isAssigned && !props.row.isRemoved"
                    :id="`diagnosed_at_${props.row.id}`" v-model="props.row.diagnosed_at" type="date"
                    class="admin-form__input" :disabled="props.row.isRemoved"
                    @update:modelValue="updateSortDate(props.row)" />
                  <TextInput v-else-if="canEdit && props.row.isAssigned && props.row.isRemoved"
                    :id="`diagnosed_at_${props.row.id}`" v-model="props.row.diagnosed_at" type="date"
                    class="admin-form__input" disabled />
                  <div v-else class="text-body2">
                    {{ displayDiagnosedAt(props.row) }}
                  </div>
                </q-td>
              </template>

              <!-- Notes input column -->
              <template #body-cell-notes="props">
                <q-td :props="props">
                  <TextInput v-if="canEdit && props.row.isAssigned && !props.row.isRemoved"
                    :id="`notes_${props.row.id}`" v-model="props.row.notes" type="text"
                    class="admin-form__input" placeholder="Notas" :disabled="props.row.isRemoved" />
                  <TextInput v-else-if="canEdit && props.row.isAssigned && props.row.isRemoved"
                    :id="`notes_${props.row.id}`" v-model="props.row.notes" type="text"
                    class="admin-form__input" placeholder="Notas" disabled />
                  <div v-else class="text-body2">
                    {{ displayNotes(props.row) }}
                  </div>
                </q-td>
              </template>

              <!-- Actions column -->
              <template v-if="canEdit" #body-cell-actions="props">
                <q-td :props="props">
                  <div class="row items-center q-gutter-sm">
                    <!-- Assign button for unassigned diagnoses -->
                    <q-btn v-if="!props.row.isAssigned" flat dense color="primary" label="Asignar" size="sm"
                      @click="assignDiagnosis(props.row)" />

                    <!-- Remove button for assigned diagnoses -->
                    <q-btn v-if="props.row.isAssigned && !props.row.isRemoved" flat round color="negative"
                      icon="delete" size="sm" title="Eliminar" @click="removeDiagnosis(props.row)" />

                    <!-- Restore button for removed diagnoses -->
                    <q-btn v-if="props.row.isAssigned && props.row.isRemoved" flat dense color="positive"
                      label="Restaurar" size="sm" @click="restoreDiagnosis(props.row)" />
                  </div>
                </q-td>
              </template>
            </q-table>

          </q-expansion-item>

          <ActionButtons v-if="canEdit" button-label="Guardar Cambios" :cancel-href="cancelUrl"
            :disabled="form.processing" />
        </form>

      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import UserInformation from '@/Components/admin/UserInformation.vue';
import DiagnosisCategoryBadge from '@/Components/Badges/DiagnosisCategoryBadge.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDateForInput, formatDate } from '@/Utils/date';

const props = defineProps({
  user: Object,
  genders: Object,
  userDiagnoses: Array,
  diagnoses: Array,
  canEdit: { type: Boolean, default: true },
  pageTitle: { type: String, default: null },
  headerTitle: { type: String, default: null },
  saveUrl: { type: String, default: null },
  cancelUrl: { type: String, default: null },
  editablePicture: { type: Boolean, default: true },
  publicView: { type: Boolean, default: false },
});

const canEdit = computed(() => !!props.canEdit);
const pageTitle = computed(() => props.pageTitle ?? `Diagnósticos de ${props.user.name}`);
const headerTitle = computed(() => props.headerTitle ?? `Diagnósticos de ${props.user.name}`);
const saveUrl = computed(() => props.saveUrl ?? route('users.update-diagnoses', props.user.id));
const cancelUrl = computed(() => props.cancelUrl ?? route('users.show', props.user.id));
const editablePicture = computed(() => props.editablePicture);
const publicView = computed(() => props.publicView);

// Create a map of assigned diagnosis IDs
const assignedDiagnosisIds = computed(() => {
  return new Set(props.userDiagnoses.map(d => d.id));
});

// Create reactive rows with all diagnoses
const diagnosisRows = ref([]);

// Initialize rows on mount
onMounted(() => {
  // Create a map of user diagnoses with their pivot data
  const userDiagnosesMap = new Map();
  props.userDiagnoses.forEach(ud => {
    userDiagnosesMap.set(ud.id, {
      diagnosed_at: ud.pivot?.diagnosed_at ? formatDateForInput(ud.pivot.diagnosed_at) : '',
      notes: ud.pivot?.notes || '',
    });
  });

  // Create rows for assigned diagnoses (at the top)
  const assignedRows = props.diagnoses
    .filter(d => assignedDiagnosisIds.value.has(d.id))
    .map(d => {
      const diagnosedAt = userDiagnosesMap.get(d.id)?.diagnosed_at || '';
      return {
        ...d,
        isAssigned: true,
        isRemoved: false,
        diagnosed_at: diagnosedAt,
        diagnosed_at_sort: diagnosedAt || null, // For sorting: null if empty
        notes: userDiagnosesMap.get(d.id)?.notes || '',
      };
    });

  // Create rows for unassigned diagnoses
  const unassignedRows = props.diagnoses
    .filter(d => !assignedDiagnosisIds.value.has(d.id))
    .map(d => ({
      ...d,
      isAssigned: false,
      isRemoved: false,
      diagnosed_at: '',
      diagnosed_at_sort: null, // null for unassigned (will sort last)
      notes: '',
    }));

  // Combine: assigned first, then unassigned
  diagnosisRows.value = [...assignedRows, ...unassignedRows];
});

// Computed property for table rows (maintains order: assigned first, then unassigned)
const tableRows = computed(() => {
  const assigned = diagnosisRows.value.filter(r => r.isAssigned && !r.isRemoved);
  const removed = diagnosisRows.value.filter(r => r.isAssigned && r.isRemoved);
  const unassigned = diagnosisRows.value.filter(r => !r.isAssigned);

  // Return: active assigned, then removed, then unassigned
  return [...assigned, ...removed, ...unassigned];
});

// Update sort date when diagnosed_at changes
const updateSortDate = (row) => {
  row.diagnosed_at_sort = row.diagnosed_at || null;
};

// Assign a diagnosis
const assignDiagnosis = (row) => {
  if (!canEdit.value) return;
  row.isAssigned = true;
  row.isRemoved = false;
  row.diagnosed_at_sort = row.diagnosed_at || null;
  // The computed property will handle the ordering
};

// Remove a diagnosis
const removeDiagnosis = (row) => {
  if (!canEdit.value) return;
  row.isRemoved = true;
};

// Restore a diagnosis
const restoreDiagnosis = (row) => {
  if (!canEdit.value) return;
  row.isRemoved = false;
};

const baseColumns = Object.freeze([
  {
    name: 'category',
    label: 'Categoría',
    align: 'left',
    field: 'category',
    sortable: true,
    style: 'width: 200px'
  },
  {
    name: 'name',
    label: 'Nombre',
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'diagnosed_at',
    label: 'Fecha de diagnóstico',
    align: 'left',
    field: 'diagnosed_at_sort',
    sortable: true,
    style: 'width: 180px'
  },
  {
    name: 'notes',
    label: 'Notas',
    align: 'left',
    field: 'notes',
    sortable: false
  }
]);

const actionsColumn = {
  name: 'actions',
  label: 'Acciones',
  align: 'center',
  field: 'actions',
  sortable: false,
  classes: 'mll-table__cell-actions',
  headerClasses: 'mll-table__cell-actions-header',
  style: 'width: 150px'
};

const columns = computed(() => {
  return canEdit.value ? [...baseColumns, actionsColumn] : baseColumns;
});

const form = useForm({
  diagnoses: []
});

const displayDiagnosedAt = (row) => {
  if (!row.isAssigned || row.isRemoved) {
    return '—';
  }
  return formatDate(row.diagnosed_at) || '—';
};

const displayNotes = (row) => {
  if (!row.isAssigned || row.isRemoved) {
    return '—';
  }
  return row.notes || '—';
};

const handleSubmit = () => {
  if (!canEdit.value) {
    return;
  }
  submit();
};

const submit = () => {
  // Build diagnoses data array
  const diagnosesData = diagnosisRows.value
    .filter(row => row.isAssigned && !row.isRemoved)
    .map(row => ({
      id: row.id,
      diagnosed_at: row.diagnosed_at || null,
      notes: row.notes || null,
    }));

  form.diagnoses = diagnosesData;
  form.put(saveUrl.value);
};
</script>
