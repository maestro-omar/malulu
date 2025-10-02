<template>
    <AuthenticatedLayout>
        <Head title="Crear Subtipo de Archivo" />
        <h2 class="page-subtitle">
            Crear Subtipo de Archivo
        </h2>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div>
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
                                <CheckboxWithLabel v-model="form.new_overwrites">
                                    Sobrescribe archivos existentes
                                </CheckboxWithLabel>
                            </div>

                            <div class="mb-4">
                                <CheckboxWithLabel v-model="form.hidden_for_familiy">
                                    Oculto para la familia
                                </CheckboxWithLabel>
                            </div>

                            <div class="mb-4">
                                <CheckboxWithLabel v-model="form.upload_by_familiy">
                                    Puede ser subido por la familia
                                </CheckboxWithLabel>
                            </div>

                            <div class="mb-4">
                                <CheckboxWithLabel v-model="form.requires_expiration">
                                    Tiene fecha de expiración (valido hasta...)
                                </CheckboxWithLabel>
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

                            <div class="flex items-center justify-between">
                                <PrimaryButton :disabled="form.processing">
                                    Guardar
                                </PrimaryButton>
                                <CancelLink :href="route('file-subtypes.index')" />
                            </div>
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
import AuthenticatedLayout from "@/Layout/AuthenticatedLayout.vue";
import InputLabel from "@/Components/admin/InputLabel.vue";
import TextInput from "@/Components/admin/TextInput.vue";
import InputError from "@/Components/admin/InputError.vue";
import PrimaryButton from "@/Components/admin/PrimaryButton.vue";
import CheckboxWithLabel from "@/Components/admin/CheckboxWithLabel.vue";
import CancelLink from "@/Components/admin/CancelLink.vue";

defineProps({
    fileTypes: Array
});

const form = useForm({
    file_type_id: "",
    code: "",
    name: "",
    description: "",
    new_overwrites: false,
    requires_expiration: false,
    hidden_for_familiy: false,
    upload_by_familiy: false,
    order: 0
});

const submit = () => {
    form.post(route("file-subtypes.store"));
};
</script>