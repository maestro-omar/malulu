<template>

  <Head title="Crear Escuela" />

  <AuthenticatedLayout>
    <template #admin-header>
      <AdminHeader  :title="`Crear Nueva Escuela`">
      </AdminHeader>
    </template>

    <template #main-page-content>
      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
              <form @submit.prevent="submit">
                <div>
                  <InputLabel for="name" value="Nombre" />
                  <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                  <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <InputLabel for="logo" value="Logo" />
                    <input type="file" id="logo" @change="form.logo = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-50 file:text-indigo-700
                        hover:file:bg-indigo-100" accept="image/*" />
                    <InputError :message="form.errors.logo" class="mt-2" />
                  </div>

                  <div>
                    <InputLabel for="picture" value="Imagen Principal" />
                    <input type="file" id="picture" @change="form.picture = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-indigo-50 file:text-indigo-700
                        hover:file:bg-indigo-100" accept="image/*" />
                    <InputError :message="form.errors.picture" class="mt-2" />
                  </div>
                </div>

                <div>
                  <InputLabel for="short" value="Nombre Corto" />
                  <TextInput id="short" type="text" class="mt-1 block w-full" v-model="form.short" required
                    @blur="handleShortBlur" />
                  <InputError :message="form.errors.short" class="mt-2" />
                </div>

                <div>
                  <InputLabel for="slug" value="Slug" />
                  <TextInput id="slug" type="text" class="mt-1 block w-full" v-model="form.slug" required
                    @input="manualSlugEdit = true" />
                  <InputError :message="form.errors.slug" class="mt-2" />
                </div>

                <div>
                  <InputLabel for="cue" value="CUE" />
                  <TextInput id="cue" type="text" class="mt-1 block w-full" v-model="form.cue" required />
                  <InputError :message="form.errors.cue" class="mt-2" />
                </div>

                <div>
                  <InputLabel for="locality" value="Localidad" />
                  <SearchableDropdown id="locality" v-model="form.locality_id" :options="localities" required />
                  <InputError :message="form.errors.locality_id" class="mt-2" />
                </div>

                <div>
                  <InputLabel for="management_type" value="Tipo de Gestión" />
                  <select id="management_type" v-model="form.management_type_id"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required>
                    <option value="">Seleccionar tipo de gestión</option>
                    <option v-for="type in managementTypes" :key="type.id" :value="type.id">
                      {{ type.name }}
                    </option>
                  </select>
                  <InputError :message="form.errors.management_type_id" class="mt-2" />
                </div>

                <div>
                  <InputLabel value="Niveles" />
                  <div class="mt-2 space-y-2">
                    <div v-for="level in schoolLevels" :key="level.id" class="flex items-center">
                      <Checkbox :id="'level-' + level.id" :value="level.id" v-model:checked="form.school_levels" />
                      <label :for="'level-' + level.id" class="ml-2 text-sm text-gray-600">
                        {{ level.name }}
                      </label>
                    </div>
                  </div>
                  <InputError :message="form.errors.school_levels" class="mt-2" />
                </div>

                <div>
                  <InputLabel value="Turnos" />
                  <div class="mt-2 space-y-2">
                    <div v-for="shift in shifts" :key="shift.id" class="flex items-center">
                      <Checkbox :id="'shift-' + shift.id" :value="shift.id" v-model:checked="form.shifts" />
                      <label :for="'shift-' + shift.id" class="ml-2 text-sm text-gray-600">
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
    </template>
  </AuthenticatedLayout>
</template>

<script setup>
import CancelLink from "@/Components/admin/CancelLink.vue";
import Checkbox from "@/Components/admin/Checkbox.vue";
import InputError from "@/Components/admin/InputError.vue";
import InputLabel from "@/Components/admin/InputLabel.vue";
import PrimaryButton from "@/Components/admin/PrimaryButton.vue";
import SearchableDropdown from "@/Components/admin/SearchableDropdown.vue";
import TextInput from "@/Components/admin/TextInput.vue";
import AuthenticatedLayout from "@/Layout/AuthenticatedLayout.vue";
import AdminHeader from "@/Sections/AdminHeader.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from 'vue';

const props = defineProps({
  localities: Array,
  schoolLevels: Array,
  managementTypes: Array,
  shifts: Array,
  
});

const manualSlugEdit = ref(false);

function slugify(text) {
  return text
    .toString()
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '') // Remove accents
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .replace(/--+/g, '-');
}

function handleShortBlur() {
  if (!manualSlugEdit.value && form.short) {
    form.slug = slugify(form.short);
  }
}

const form = useForm({
  name: "",
  short: "",
  slug: "",
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
  logo: null,
  picture: null
});

const submit = () => {
  form.post(route("schools.store"));
};
</script>