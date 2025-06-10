<template>
  <AuthenticatedLayout>
    <Head title="Crear Escuela" />
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Crear Escuela
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit" class="space-y-6">
              <div>
                <InputLabel for="name" value="Nombre" />
                <TextInput
                  id="name"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.name"
                  required
                  autofocus
                />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="short" value="Nombre Corto" />
                <TextInput
                  id="short"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.short"
                  required
                />
                <InputError :message="form.errors.short" class="mt-2" />
              </div>

              <div>
                <InputLabel for="cue" value="CUE" />
                <TextInput
                  id="cue"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.cue"
                  required
                />
                <InputError :message="form.errors.cue" class="mt-2" />
              </div>

              <div>
                <InputLabel for="locality" value="Localidad" />
                <SearchableDropdown
                  id="locality"
                  v-model="form.locality_id"
                  :options="localities"
                  required
                />
                <InputError :message="form.errors.locality_id" class="mt-2" />
              </div>

              <div>
                <InputLabel for="management_type" value="Tipo de Gestión" />
                <select
                  id="management_type"
                  v-model="form.management_type_id"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  required
                >
                  <option value="">Seleccionar tipo de gestión</option>
                  <option
                    v-for="type in managementTypes"
                    :key="type.id"
                    :value="type.id"
                  >
                    {{ type.name }}
                  </option>
                </select>
                <InputError
                  :message="form.errors.management_type_id"
                  class="mt-2"
                />
              </div>

              <div>
                <InputLabel value="Niveles" />
                <div class="mt-2 space-y-2">
                  <div
                    v-for="level in schoolLevels"
                    :key="level.id"
                    class="flex items-center"
                  >
                    <Checkbox
                      :id="'level-' + level.id"
                      :value="level.id"
                      v-model:checked="form.school_levels"
                    />
                    <label
                      :for="'level-' + level.id"
                      class="ml-2 text-sm text-gray-600"
                    >
                      {{ level.name }}
                    </label>
                  </div>
                </div>
                <InputError :message="form.errors.school_levels" class="mt-2" />
              </div>

              <div>
                <InputLabel value="Turnos" />
                <div class="mt-2 space-y-2">
                  <div
                    v-for="shift in shifts"
                    :key="shift.id"
                    class="flex items-center"
                  >
                    <Checkbox
                      :id="'shift-' + shift.id"
                      :value="shift.id"
                      v-model:checked="form.shifts"
                    />
                    <label
                      :for="'shift-' + shift.id"
                      class="ml-2 text-sm text-gray-600"
                    >
                      {{ shift.name }}
                    </label>
                  </div>
                </div>
                <InputError :message="form.errors.shifts" class="mt-2" />
              </div>

              <div class="flex items-center justify-between">
                <PrimaryButton :disabled="form.processing">
                  Crear Escuela
                </PrimaryButton>
                <CancelLink :href="route('schools.index')" />
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import SearchableDropdown from "@/Components/SearchableDropdown.vue";
import CancelLink from "@/Components/CancelLink.vue";

const props = defineProps({
  localities: Array,
  schoolLevels: Array,
  managementTypes: Array,
  shifts: Array,
});

const form = useForm({
  name: "",
  short: "",
  cue: "",
  locality_id: "",
  management_type_id: "",
  school_levels: [],
  shifts: [],
  address: "",
  zip_code: "",
  phone: "",
  email: "",
  coordinates: "",
  social: [],
});

const submit = () => {
  form.post(route("schools.store"));
};
</script>