<template>
    <AuthenticatedLayout>
        <Head title="Editar Subtipo de Archivo" />
        <h2 class="page-subtitle">
            Editar Subtipo de Archivo
        </h2>

        <div class="container">
            <div class="admin-form__wrapper">
                <form @submit.prevent="submit" class="admin-form__container">
                    <div class="admin-form__card">
                        <div class="admin-form__card-content">
                            <div class="admin-form__field">
                                <InputLabel for="file_type_id" value="Tipo de Archivo" />
                                <select
                                    id="file_type_id"
                                    v-model="form.file_type_id"
                                    class="admin-form__select"
                                    required
                                >
                                    <option value="">Seleccione un tipo</option>
                                    <option v-for="fileType in fileTypes" :key="fileType.id" :value="fileType.id">
                                        {{ fileType.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.file_type_id" class="admin-form__error" />
                            </div>

                            <div class="admin-form__field">
                                <InputLabel for="code" value="Clave" />
                                <TextInput
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    class="admin-form__input"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.code" class="admin-form__error" />
                            </div>

                            <div class="admin-form__field">
                                <InputLabel for="name" value="Nombre" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="admin-form__input"
                                    required
                                />
                                <InputError :message="form.errors.name" class="admin-form__error" />
                            </div>

                            <div class="admin-form__field">
                                <InputLabel for="description" value="Descripción" />
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    class="admin-form__textarea"
                                    rows="3"
                                ></textarea>
                                <InputError :message="form.errors.description" class="admin-form__error" />
                            </div>

                            <div class="admin-form__field">
                                <CheckboxWithLabel v-model="form.new_overwrites">
                                    Sobrescribe archivos existentes
                                </CheckboxWithLabel>
                            </div>

                            <div class="admin-form__field">
                                <CheckboxWithLabel v-model="form.hidden_for_familiy">
                                    Oculto para la familia
                                </CheckboxWithLabel>
                            </div>

                            <div class="admin-form__field">
                                <CheckboxWithLabel v-model="form.upload_by_familiy">
                                    Puede ser subido por la familia
                                </CheckboxWithLabel>
                            </div>

                            <div class="admin-form__field">
                                <InputLabel for="order" value="Orden" />
                                <TextInput
                                    id="order"
                                    v-model="form.order"
                                    type="number"
                                    class="admin-form__input"
                                    required
                                />
                                <InputError :message="form.errors.order" class="admin-form__error" />
                            </div>

                            <ActionButtons
                                button-label="Guardar"
                                :cancel-href="route('file-subtypes.index')"
                                :disabled="form.processing"
                            />
                        </div>
                    </div>
                </form>
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
import CheckboxWithLabel from "@/Components/admin/CheckboxWithLabel.vue";
import ActionButtons from "@/Components/admin/ActionButtons.vue";

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