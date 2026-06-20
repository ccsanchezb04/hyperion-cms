<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface RoleOption { slug: string; name: string }

interface UserData {
    id: number;
    name: string;
    email: string;
    status: 'active' | 'inactive';
    roles: string[];
    created_at: string | null;
}

const props = defineProps<{
    user: UserData;
    roles: RoleOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Users', href: '/admin/users' },
    { title: props.user.name, href: `/admin/users/${props.user.id}/edit` },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    status: props.user.status,
    roles: [...props.user.roles],
});

const submit = () => {
    form.put(`/admin/users/${props.user.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Editar ${user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4" style="max-width: 720px;">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-4">Editar usuario</h1>

                    <form @submit.prevent="submit" class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Nombre *</label>
                            <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" required />
                            <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="form-label">Email *</label>
                            <input v-model="form.email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.email }" required />
                            <div v-if="form.errors.email" class="invalid-feedback d-block">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="form-label">Nueva contraseña</label>
                            <input v-model="form.password" type="password" class="form-control" :class="{ 'is-invalid': form.errors.password }" autocomplete="new-password" />
                            <div v-if="form.errors.password" class="invalid-feedback d-block">{{ form.errors.password }}</div>
                            <div class="form-text">Dejar vacío para conservar la contraseña actual.</div>
                        </div>

                        <div>
                            <label class="form-label">Estado</label>
                            <select v-model="form.status" class="form-select">
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Roles</label>
                            <div class="d-flex flex-column gap-1">
                                <div v-for="r in roles" :key="r.slug" class="form-check">
                                    <input
                                        :id="`role-${r.slug}`"
                                        v-model="form.roles"
                                        type="checkbox"
                                        :value="r.slug"
                                        class="form-check-input"
                                    />
                                    <label :for="`role-${r.slug}`" class="form-check-label">{{ r.name }} <code class="small text-body-secondary">{{ r.slug }}</code></label>
                                </div>
                            </div>
                            <div v-if="form.errors.roles" class="invalid-feedback d-block">{{ form.errors.roles }}</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <Link href="/admin/users" class="btn btn-outline-secondary">Cancelar</Link>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
