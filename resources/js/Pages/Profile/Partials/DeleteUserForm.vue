<script setup>
import InputError from '@/Components/admin/InputError.vue';
import InputLabel from '@/Components/admin/InputLabel.vue';
import Modal from '@/Components/admin/Modal.vue';
import TextInput from '@/Components/admin/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <section class="delete-user">
        <header class="delete-user__header">
            <h2 class="delete-user__title">Eliminar Cuenta</h2>
            <p class="delete-user__description">
                Una vez que su cuenta sea eliminada, todos sus recursos y datos serán eliminados permanentemente. Antes de eliminar
                su cuenta, por favor descargue cualquier dato o información que desee conservar.
            </p>
        </header>

        <button @click="confirmUserDeletion" class="admin-button admin-button--danger delete-user__delete-btn">Eliminar Cuenta</button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="delete-user__modal">
                <h2 class="delete-user__modal-title">
                    ¿Está seguro de que desea eliminar su cuenta?
                </h2>
                <p class="delete-user__modal-description">
                    Una vez que su cuenta sea eliminada, todos sus recursos y datos serán eliminados permanentemente. Por favor,
                    ingrese su contraseña para confirmar que desea eliminar permanentemente su cuenta.
                </p>
                <div class="delete-user__modal-input">
                    <InputLabel for="password" value="Contraseña" class="sr-only" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="delete-user__password-input"
                        placeholder="Contraseña"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="delete-user__input-error" />
                </div>
                <div class="delete-user__modal-actions">
                    <button @click="closeModal" class="admin-button admin-button--secondary delete-user__cancel-btn"> Cancelar </button>
                    <button
                        class="admin-button admin-button--danger delete-user__confirm-btn"
                        :class="{ 'delete-user__confirm-btn--processing': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Eliminar Cuenta
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
