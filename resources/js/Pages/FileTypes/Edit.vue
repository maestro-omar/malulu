<template>
    <AuthenticatedLayout>
        <Head title="Editar Tipo de Archivo" />
        <template #header>
            <h2 class="page-subtitle">
                Editar Tipo de Archivo
            </h2>
        </template>

        <div class="container">
            <div class="form__wrapper">
                <form @submit.prevent="submit" class="form__container">
                    <div class="form__card">
                        <div class="form__card-content">
                            <div class="form__field">
                                <InputLabel for="code" value="Clave" />
                                <TextInput
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    class="form__input"
                                    required
                                    autofocus
                                />
                                <InputError :message="form.errors.code" class="form__error" />
                            </div>

                            <div class="form__field">
                                <InputLabel for="name" value="Nombre" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="form__input"
                                    required
                                />
                                <InputError :message="form.errors.name" class="form__error" />
                            </div>

                            <div class="form__field">
                                <InputLabel for="relate_with" value="Relacionado Con" />
                                <select
                                    id="relate_with"
                                    v-model="form.relate_with"
                                    class="form__select"
                                >
                                    <option value="">Seleccione una opción</option>
                                    <option v-for="(label, value) in relateWithOptions" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.relate_with" class="form__error" />
                            </div>

                            <div class="form__field">
                                <CheckboxWithLabel v-model="form.active">
                                    Activo
                                </CheckboxWithLabel>
                            </div>

                            <ActionButtons 
                                button-label="Guardar"
                                :cancel-href="route('file-types.index')"
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
import { useForm, Link, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import InputError from '@/Components/admin/InputError.vue';
import CheckboxWithLabel from '@/Components/admin/CheckboxWithLabel.vue';
import ActionButtons from '@/Components/admin/ActionButtons.vue';

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
    code: props.fileType.code,
    name: props.fileType.name,
    relate_with: props.fileType.relate_with,
    active: props.fileType.active
});

const submit = () => {
    form.put(route('file-types.update', props.fileType.id));
};
</script>