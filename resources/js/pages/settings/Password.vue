<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbItems: BreadcrumbItem[] = [{ title: 'Cambiar contraseña', href: '/admin/settings/password' }];

const passwordInput = ref<HTMLInputElement>();
const currentPasswordInput = ref<HTMLInputElement>();

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: Record<string, string>) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Cambiar contraseña" />

        <SettingsLayout>
            <div class="d-flex flex-column gap-4">
                <HeadingSmall title="Cambiar contraseña" description="Usa una contraseña larga y aleatoria para mantener tu cuenta segura" />

                <form @submit.prevent="updatePassword" class="d-flex flex-column gap-3">
                    <div>
                        <label for="current_password" class="form-label">Contraseña actual</label>
                        <input
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.current_password }"
                            autocomplete="current-password"
                            placeholder="Contraseña actual"
                        />
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div>
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.password }"
                            autocomplete="new-password"
                            placeholder="Nueva contraseña"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.password_confirmation }"
                            autocomplete="new-password"
                            placeholder="Confirmar contraseña"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Guardar contraseña</button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition-opacity"
                            enter-from="opacity-0"
                            leave="transition-opacity"
                            leave-to="opacity-0"
                        >
                            <p class="small text-body-secondary mb-0">Guardado</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
.transition-opacity { transition: opacity 0.2s ease-in-out; }
</style>
