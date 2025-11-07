<template>

  <Head :title="`Diagnósticos de ${props.user.name}`" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader :title="`Diagnósticos de Usuario: ${props.user.name}`"></AdminHeader>
    </template>

    <template #main-page-content>
      <div class="container">

        <UserInformation :user="user" :genders="genders" :editable-picture="true" />

        <form @submit.prevent="submit" class="admin-form__container">
          <q-card class="admin-form__card">
            <q-card-section>
              <h3 class="admin-form__card-title">Diagnósticos</h3>

              <q-table class="mll-table mll-table--diagnoses striped-table" dense :rows="tableRows" :columns="columns"
                row-key="id" :pagination="{ rowsPerPage: 0 }" hide-pagination>
                <!-- Category column -->
                <template #body-cell-category="props">
                  <q-td :props="props">
                    <DiagnosisCategoryBadge
                      v-if="props.row.category"
                      :category="props.row.category"
                      :category-name="props.row.category_name"
                      size="sm"
                    />
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
                    <TextInput v-if="props.row.isAssigned && !props.row.isRemoved" :id="`diagnosed_at_${props.row.id}`"
                      v-model="props.row.diagnosed_at" type="date" class="admin-form__input"
                      :disabled="props.row.isRemoved" @update:modelValue="updateSortDate(props.row)" />
                    <TextInput v-else-if="props.row.isAssigned && props.row.isRemoved"
                      :id="`diagnosed_at_${props.row.id}`" v-model="props.row.diagnosed_at" type="date"
                      class="admin-form__input" disabled />
                  </q-td>
                </template>

                <!-- Notes input column -->
                <template #body-cell-notes="props">
                  <q-td :props="props">
                    <TextInput v-if="props.row.isAssigned && !props.row.isRemoved" :id="`notes_${props.row.id}`"
                      v-model="props.row.notes" type="text" class="admin-form__input" placeholder="Notas"
                      :disabled="props.row.isRemoved" />
                    <TextInput v-else-if="props.row.isAssigned && props.row.isRemoved" :id="`notes_${props.row.id}`"
                      v-model="props.row.notes" type="text" class="admin-form__input" placeholder="Notas" disabled />
                  </q-td>
                </template>

                <!-- Actions column -->
                <template #body-cell-actions="props">
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
            </q-card-section>
          </q-card>

          <ActionButtons button-label="Guardar Cambios" :cancel-href="route('users.show', props.user.id)"
            :disabled="form.processing" />
        </form>

      </div>
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import AuthenticatedLayout from '@/Layout/AuthenticatedLayout.vue';
import AdminHeader from '@/Sections/AdminHeader.vue';
import UserInformation from '@/Components/admin/UserInformation.vue';
import DiagnosisCategoryBadge from '@/Components/Badges/DiagnosisCategoryBadge.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { formatDateForInput } from '@/Utils/date';

const props = defineProps({
  user: Object,
  genders: Object,
  userDiagnoses: Array,
  diagnoses: Array,
});

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
  row.isAssigned = true;
  row.isRemoved = false;
  row.diagnosed_at_sort = row.diagnosed_at || null;
  // Move to the top of assigned section
  // The computed property will handle the ordering
};

// Remove a diagnosis
const removeDiagnosis = (row) => {
  row.isRemoved = true;
};

// Restore a diagnosis
const restoreDiagnosis = (row) => {
  row.isRemoved = false;
};

// Table columns
const columns = [
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
    field: 'diagnosed_at_sort', // Use sortable field for sorting
    sortable: true,
    style: 'width: 180px'
  },
  {
    name: 'notes',
    label: 'Notas',
    align: 'left',
    field: 'notes',
    sortable: false
  },
  {
    name: 'actions',
    label: 'Acciones',
    align: 'center',
    field: 'actions',
    sortable: false,
    classes: 'mll-table__cell-actions',
    headerClasses: 'mll-table__cell-actions-header',
    style: 'width: 150px'
  }
];

const form = useForm({
  diagnoses: []
});

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
  form.put(route('users.update-diagnoses', props.user.id));
};
</script>
