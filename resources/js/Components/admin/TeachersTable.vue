<template>
  <q-expansion-item expand-separator default-opened class="mll-table-expansion q-mt-md">
    <template v-slot:header>
      <q-item-section avatar>
        <q-icon name="co_present" size="sm" color="accent" />
      </q-item-section>

      <q-item-section align="left">
        {{ title }}
      </q-item-section>
    </template>
    <!-- Quasar Table -->
    <q-table class="mll-table mll-table--techers striped-table" dense :rows="teachers" :columns="columns" row-key="id"
      binary-state- :loading-label="loading ? 'Cargando profesores...' : ''">

      <!-- Custom cell for picture -->
      <template #body-cell-picture="props">
        <q-td :props="props">
          <q-avatar size="40px">
            <img v-if="props.row.picture" :src="props.row.picture" :alt="props.row.name" />
            <img v-else :src="noImage" :alt="props.row.name" />
          </q-avatar>
        </q-td>
      </template>

      <!-- Custom cell for firstname -->
      <template #body-cell-firstname="props">
        <q-td :props="props">
          <Link :href="route_school_staff(school, props.row, 'show')" class="text-primary">
          {{ props.row.firstname }}
          </Link>
        </q-td>
      </template>

      <!-- Custom cell for lastname -->
      <template #body-cell-lastname="props">
        <q-td :props="props">
          <div class="text-weight-medium">{{ props.row.lastname }}</div>
        </q-td>
      </template>

      <!-- Custom cell for email -->
      <template #body-cell-email="props">
        <q-td :props="props">
          <EmailField :email="props.row.email" />
        </q-td>
      </template>

      <!-- Custom cell for birthdate -->
      <template #body-cell-birthdate="props">
        <q-td :props="props">
          <BirthdateAge :birthdate="props.row.birthdate" />
        </q-td>
      </template>

      <!-- Custom cell for role -->
      <template #body-cell-role="props">
        <q-td :props="props">
          <RoleBadge :role="props.row.rel_role" size="sm" />
        </q-td>
      </template>

      <!-- Custom cell for subject -->
      <template #body-cell-subject="props">
        <q-td :props="props">
          <span v-if="props.row.rel_subject" class="text-weight-medium">{{ props.row.rel_subject }}</span>
          <span v-else class="text-grey-5">-</span>
        </q-td>
      </template>

      <!-- Custom cell for in_charge -->
      <template #body-cell-in_charge="props">
        <q-td :props="props">
          <q-icon v-if="props.row.rel_in_charge" name="check_circle" color="positive" size="sm" />
        </q-td>
      </template>

      <!-- Custom cell for actions -->
      <template #body-cell-actions="props">
        <q-td :props="props">
          <q-btn flat dense color="primary" icon="visibility" :href="route_school_staff(school, props.row, 'show')"
            size="sm">
            <q-tooltip>Ver profesor</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>
  </q-expansion-item>
</template>


<script setup>
import { Link } from '@inertiajs/vue3'
import BirthdateAge from '@/Components/admin/BirthdateAge.vue';
import EmailField from '@/Components/admin/EmailField.vue';
import RoleBadge from '@/Components/Badges/RoleBadge.vue';
import noImage from "@images/no-image-person.png";
import { route_school_staff } from '@/Utils/routes';

const props = defineProps({
  title: { type: String, default: 'Docentes' },
  teachers: { type: Array, default: () => [] },
  courseId: { type: [String, Number], required: true },
  schoolLevel: { type: String, required: true },
  school: { type: Object, required: true },
  loading: { type: Boolean, default: false }
});

const columns = [
  {
    name: 'picture',
    label: '',
    field: 'picture',
    align: 'center',
    sortable: false,
    style: 'width: 60px'
  },
  {
    name: 'firstname',
    label: 'Nombre',
    field: 'firstname',
    align: 'left',
    sortable: true
  },
  {
    name: 'lastname',
    label: 'Apellido',
    field: 'lastname',
    align: 'left',
    sortable: true
  },
  {
    name: 'email',
    required: true,
    label: 'Email',
    align: 'left',
    field: 'email',
    sortable: true
  },
  {
    name: 'birthdate',
    label: 'Fecha de Nacimiento',
    field: 'birthdate',
    align: 'left',
    sortable: true
  },
  {
    name: 'role',
    label: 'Rol',
    field: 'rel_role',
    align: 'left',
    sortable: true
  },
  {
    name: 'subject',
    label: 'Materia',
    field: 'rel_subject',
    align: 'left',
    sortable: true
  },
  {
    name: 'in_charge',
    label: 'A cargo',
    field: 'rel_in_charge',
    align: 'center',
    sortable: true,
    style: 'width: 80px'
  },
  {
    name: 'actions',
    label: 'Acciones',
    field: 'actions',
    align: 'center',
    sortable: false,
    style: 'width: 80px'
  }
];

</script>
