<template>
  <AuthenticatedLayout>
    <Head title="Crear Tipo de Archivo" />
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Crear Tipo de Archivo
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <form @submit.prevent="submit">
              <div class="mb-4">
                <InputLabel for="code" value="Clave" />
                <TextInput
                  id="code"
                  v-model="form.code"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                />
                <InputError :message="form.errors.code" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="name" value="Nombre" />
                <TextInput
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                />
                <InputError :message="form.errors.name" class="mt-2" />
              </div>

              <div class="mb-4">
                <InputLabel for="relate_with" value="Relacionado Con" />
                <select
                  id="relate_with"
                  v-model="form.relate_with"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                  <option value="">Seleccione una opción</option>
                  <option v-for="(label, value) in relateWithOptions" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <InputError :message="form.errors.relate_with" class="mt-2" />
              </div>

              <div class="mb-4">
                <CheckboxWithLabel v-model="form.active">
                  Activo
                </CheckboxWithLabel>
              </div>

              <div class="flex items-center justify-between">
                <PrimaryButton :disabled="form.processing">
                  Guardar
                </PrimaryButton>
                <CancelLink :href="route('file-types.index')" />
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/admin/InputLabel.vue";
import TextInput from "@/Components/admin/TextInput.vue";
import InputError from "@/Components/admin/InputError.vue";
import PrimaryButton from "@/Components/admin/PrimaryButton.vue";
import CheckboxWithLabel from "@/Components/admin/CheckboxWithLabel.vue";
import CancelLink from "@/Components/admin/CancelLink.vue";

const relateWithOptions = {
  school: 'Escuela',
  course: 'Grupo',
  teacher: 'Docente',
  student: 'Alumna/o',
  user: 'Usuario'
};

const form = useForm({
  code: "",
  name: "",
  relate_with: "",
  active: true,
});

const submit = () => {
  form.post(route("file-types.store"));
};
</script>