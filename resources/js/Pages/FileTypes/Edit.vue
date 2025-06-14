<template>
    <AuthenticatedLayout>
        <Head title="Editar Tipo de Archivo" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Tipo de Archivo
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <InputLabel for="key" value="Clave" />
                                <TextInput
                                    id="key"
                                    v-model="form.key"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.key" class="mt-2" />
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
                                <label class="flex items-center">
                                    <Checkbox v-model:checked="form.active" />
                                    <span class="ml-2">Activo</span>
                                </label>
                            </div>

                            <ActionButtons 
                                button-label="Guardar"
                                :cancel-href="route('file-types.index')"
                                :disabled="form.processing"
                            />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import ActionButtons from '@/Components/ActionButtons.vue';

const props = defineProps({
    fileType: Object
});

const relateWithOptions = {
    school: 'Escuela',
    course: 'Grupo',
    teacher: 'Docente',
    student: 'Alumna/o',
    user: 'Usuario'
};

const form = useForm({
    key: props.fileType.key,
    name: props.fileType.name,
    relate_with: props.fileType.relate_with,
    active: props.fileType.active
});

const submit = () => {
    form.put(route('file-types.update', props.fileType.id));
};
</script>