<script setup lang="ts">
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Configuración de perfil', href: '/admin/settings/profile' }];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Configuración de perfil" />

        <SettingsLayout>
            <div class="d-flex flex-column gap-4">
                <HeadingSmall title="Información del perfil" description="Actualiza tu nombre y correo electrónico" />

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
                            autocomplete="username"
                            placeholder="Correo electrónico"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at" class="alert alert-warning py-2">
                        <p class="small mb-2">
                            Tu correo electrónico no está verificado.
                            <Link :href="route('verification.send')" method="post" as="button" class="btn btn-link p-0 text-decoration-underline">
                                Haz clic aquí para reenviar el correo de verificación.
                            </Link>
                        </p>
                        <div v-if="status === 'verification-link-sent'" class="small text-success fw-medium mb-0">
                            Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Guardar</button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition-opacity"
                            enter-from="opacity-0"
                            leave="transition-opacity"
                            leave-to="opacity-0"
                        >
                            <p class="small text-body-secondary mb-0">Guardado.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>

<style scoped>
.transition-opacity {
    transition: opacity 0.2s ease-in-out;
}
</style>
