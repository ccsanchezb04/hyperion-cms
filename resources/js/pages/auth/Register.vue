<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Crear una cuenta" description="Ingresa tus datos para crear tu cuenta">
        <Head title="Registro" />

        <form @submit.prevent="submit" class="d-flex flex-column gap-3">
            <div>
                <label for="name" class="form-label">Nombre</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.name }"
                    required
                    autofocus
                    tabindex="1"
                    autocomplete="name"
                    placeholder="Nombre completo"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <label for="email" class="form-label">Correo electrónico</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.email }"
                    required
                    tabindex="2"
                    autocomplete="email"
                    placeholder="correo@ejemplo.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="form-label">Contraseña</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.password }"
                    required
                    tabindex="3"
                    autocomplete="new-password"
                    placeholder="Contraseña"
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
                    required
                    tabindex="4"
                    autocomplete="new-password"
                    placeholder="Confirmar contraseña"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2" tabindex="5" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" :size="16" class="spinner" />
                Crear cuenta
            </button>

            <div class="text-center text-body-secondary small">
                ¿Ya tienes una cuenta?
                <TextLink :href="route('login')" :tabindex="6">Iniciar sesión</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
.spinner { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
