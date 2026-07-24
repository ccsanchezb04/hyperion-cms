<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { BModal } from 'bootstrap-vue-next';
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const showModal = ref(false);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
    showModal.value = false;
};
</script>

<template>
    <div class="d-flex flex-column gap-3">
        <HeadingSmall title="Eliminar cuenta" description="Elimina tu cuenta y todos sus recursos" />

        <div class="alert alert-danger d-flex flex-column gap-3">
            <div>
                <p class="fw-medium mb-1">Advertencia</p>
                <p class="small mb-0">Procede con cuidado, esta acción no se puede deshacer.</p>
            </div>
            <div>
                <button type="button" class="btn btn-danger" @click="showModal = true">Eliminar cuenta</button>
            </div>
        </div>

        <BModal v-model="showModal" title="¿Estás seguro de que deseas eliminar tu cuenta?" hide-footer @hidden="closeModal">
            <form class="d-flex flex-column gap-3" @submit="deleteUser">
                <p class="small text-body-secondary mb-0">
                    Una vez eliminada tu cuenta, todos sus recursos y datos también serán eliminados permanentemente.
                    Por favor ingresa tu contraseña para confirmar que deseas eliminar tu cuenta permanentemente.
                </p>

                <div>
                    <label for="password" class="visually-hidden">Contraseña</label>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        name="password"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.password }"
                        placeholder="Contraseña"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" @click="closeModal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" :disabled="form.processing">Eliminar cuenta</button>
                </div>
            </form>
        </BModal>
    </div>
</template>
