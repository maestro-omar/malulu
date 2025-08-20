<template>
  <div class="ag-theme-alpine" style="height: 500px; width: 100%;">
    <ag-grid-vue
      :rowData="students"
      :columnDefs="columnDefs"
      :defaultColDef="defaultColDef"
      :pagination="true"
      :paginationPageSize="10"
      :domLayout="'autoHeight'"
      @grid-ready="onGridReady"
    >
    </ag-grid-vue>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { router } from '@inertiajs/vue3';

// Import AG Grid styles
import 'ag-grid-community/styles/ag-grid.css';
import 'ag-grid-community/styles/ag-theme-alpine.css';

const props = defineProps({
  students: { type: Array, default: () => [] },
  courseId: { type: [String, Number], required: true },
  schoolLevel: { type: String, required: true },
  schoolSlug: { type: String, required: true },
});

const gridApi = ref(null);

const defaultColDef = ref({
  sortable: true,
  filter: true,
  resizable: true,
  minWidth: 100,
});

const columnDefs = computed(() => [
  {
    headerName: 'Foto',
    field: 'photo',
    width: 80,
    cellRenderer: (params) => {
      if (params.value) {
        return `<img src="${params.value}" alt="Foto" class="w-8 h-8 rounded-full object-cover" />`;
      }
      return `<img src="/img/no-image-person.png" alt="Sin foto" class="w-8 h-8 rounded-full object-cover" />`;
    },
    sortable: false,
    filter: false,
  },
  {
    headerName: 'Apellido',
    field: 'lastname',
    width: 150,
  },
  {
    headerName: 'Nombre',
    field: 'firstname',
    width: 150,
  },
  {
    headerName: 'Número de Identificación',
    field: 'id_number',
    width: 180,
  },
  {
    headerName: 'Género',
    field: 'gender',
    width: 100,
    cellRenderer: (params) => {
      return params.value === 'M' ? 'Masculino' : params.value === 'F' ? 'Femenino' : params.value;
    },
  },
  {
    headerName: 'Fecha de Nacimiento',
    field: 'birthdate',
    width: 150,
    valueFormatter: (params) => {
      if (params.value) {
        return new Date(params.value).toLocaleDateString('es-ES');
      }
      return '';
    },
  },
  {
    headerName: 'Edad',
    field: 'age',
    width: 80,
    type: 'numericColumn',
  },
  {
    headerName: 'Fecha de Inicio',
    field: 'rel_start_date',
    width: 150,
    valueFormatter: (params) => {
      if (params.value) {
        return new Date(params.value).toLocaleDateString('es-ES');
      }
      return '';
    },
  },
  {
    headerName: 'Motivo de Salida',
    field: 'rel_end_reason',
    width: 150,
  },
  {
    headerName: 'Acciones',
    field: 'id',
    width: 120,
    cellRenderer: (params) => {
      const studentId = params.data.id;
      const studentName = `${params.data.lastname} ${params.data.firstname}`;
      const url = route('school.course.view-student', {
        school: props.schoolSlug,
        schoolLevel: props.schoolLevel,
        idAndLabel: props.courseId,
        userIdAndName: `${studentId}-${encodeURIComponent(studentName)}`
      });
      
      return `<button 
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs"
        onclick="window.location.href='${url}'"
      >
        Ver Detalle
      </button>`;
    },
    sortable: false,
    filter: false,
  },
]);

const onGridReady = (params) => {
  gridApi.value = params.api;
  params.api.sizeColumnsToFit();
};

// Expose grid API for parent components if needed
defineExpose({
  gridApi,
});
</script>

<style scoped>
/* Custom styles for the grid */
:deep(.ag-theme-alpine) {
  --ag-header-height: 50px;
  --ag-row-height: 50px;
  --ag-header-background-color: #f8fafc;
  --ag-header-foreground-color: #374151;
  --ag-border-color: #e5e7eb;
  --ag-row-hover-color: #f3f4f6;
}

:deep(.ag-header-cell) {
  font-weight: 600;
  font-size: 14px;
}

:deep(.ag-cell) {
  font-size: 14px;
  padding: 8px;
}

:deep(.ag-row) {
  border-bottom: 1px solid #e5e7eb;
}

:deep(.ag-row:hover) {
  background-color: #f3f4f6;
}
</style>