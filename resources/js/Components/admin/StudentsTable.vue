<template>
  <div class="ag-theme-alpine" style="height: 500px; width: 100%;">
    <ag-grid-vue
      :rowData="students"
      :columnDefs="columnDefs"
      :defaultColDef="defaultColDef"
      :pagination="true"
      :paginationPageSize="30"
      :domLayout="'autoHeight'"
      :rowSelection="'single'"
      :suppressRowClickSelection="true"
      @grid-ready="onGridReady"
    >
    </ag-grid-vue>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { AgGridVue } from 'ag-grid-vue3';
import { router } from '@inertiajs/vue3';
import noImage from "@images/no-image-person.png";

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
    headerName: '#',
    field: 'rowNumber',
    width: 50,
    valueGetter: (params) => {
      // Get the current display position (1-based)
      if (params.node && typeof params.node.rowIndex === 'number') {
        return params.node.rowIndex + 1;
      }
      return 1;
    },
    sortable: false,
    filter: false,
    pinned: 'left',
    cellClass: 'row-number-cell',
  },
  {
    headerName: 'Foto',
    field: 'photo',
    width: 80,
    cellRenderer: 'agImageCellRenderer',
    cellRendererParams: {
      defaultImage: noImage,
      width: 40,
      height: 40,
      borderRadius: '50%',
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
    headerName: 'DNI',
    field: 'id_number',
    width: 180,
  },
  {
    headerName: 'Género',
    field: 'gender',
    width: 100,
  },
  {
    headerName: 'Fec nac',
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
  },
  {
    headerName: 'Inscripto',
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
  
  // Refresh row numbers when data changes
  params.api.addEventListener('sortChanged', () => {
    params.api.refreshCells({ force: true });
  });
  
  params.api.addEventListener('filterChanged', () => {
    params.api.refreshCells({ force: true });
  });
};

// Expose grid API for parent components if needed
defineExpose({
  gridApi,
});
</script>