<template>
    <AuthenticatedLayout>
        <Head title="Editar Subtipo de Archivo" />
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Subtipo de Archivo
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <InputLabel for="file_type_id" value="Tipo de Archivo" />
                                <select
                                    id="file_type_id"
                                    v-model="form.file_type_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Seleccione un tipo</option>
                                    <option v-for="fileType in fileTypes" :key="fileType.id" :value="fileType.id">
                                        {{ fileType.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.file_type_id" class="mt-2" />
                            </div>

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
                                <InputLabel for="description" value="Descripción" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="3"
                                ></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <Checkbox v-model:checked="form.new_overwrites" />
                                    <span class="ml-2">Sobrescribe archivos existentes</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <Checkbox v-model:checked="form.hidden_for_familiy" />
                                    <span class="ml-2">Oculto para la familia</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <Checkbox v-model:checked="form.upload_by_familiy" />
                                    <span class="ml-2">Puede ser subido por la familia</span>
                                </label>
                            </div>

                            <div class="mb-4">
                                <InputLabel for="order" value="Orden" />
                                <TextInput
                                    id="order"
                                    v-model="form.order"
                                    type="number"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.order" class="mt-2" />
                            </div>

                            <ActionButtons 
                                button-label="Guardar"
                                :cancel-href="route('file-subtypes.index')"
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
import { useForm } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Checkbox from "@/Components/Checkbox.vue";
import ActionButtons from "@/Components/ActionButtons.vue";

const props = defineProps({
    fileSubtype: Object,
    fileTypes: Array
});

const form = useForm({
    file_type_id: props.fileSubtype.file_type_id,
    code: props.fileSubtype.code,
    name: props.fileSubtype.name,
    description: props.fileSubtype.description,
    new_overwrites: props.fileSubtype.new_overwrites,
    hidden_for_familiy: props.fileSubtype.hidden_for_familiy,
    upload_by_familiy: props.fileSubtype.upload_by_familiy,
    order: props.fileSubtype.order
});

const submit = () => {
    form.put(route("file-subtypes.update", props.fileSubtype.id));
};
</script> 