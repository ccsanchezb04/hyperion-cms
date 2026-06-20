<script setup lang="ts">
import PermissionMatrix from '@/components/PermissionMatrix.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface PermissionItem { slug: string; name: string; description: string | null }

interface RoleData {
    id: number;
    name: string;
    slug: string;
    is_protected: boolean;
    permissions: string[];
}

const props = defineProps<{
    role: RoleData;
    permissions: Record<string, PermissionItem[]>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Roles', href: '/admin/roles' },
    { title: props.role.name, href: `/admin/roles/${props.role.id}/edit` },
];

const form = useForm({
    name: props.role.name,
    slug: props.role.slug,
    permissions: [...props.role.permissions],
});

const submit = () => {
    form.put(`/admin/roles/${props.role.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Editar ${role.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4" style="max-width: 920px;">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-1">Editar rol</h1>
                    <p v-if="role.is_protected" class="alert alert-warning small">
                        Este es un <strong>rol del sistema</strong>. Puedes ajustar sus permisos pero no se puede borrar.
                    </p>

                    <form @submit.prevent="submit" class="d-flex flex-column gap-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" required />
                                <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug *</label>
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    class="form-control"
                                    :class="{ 'is-invalid': form.errors.slug }"
                                    pattern="[a-z0-9-]+"
                                    :disabled="role.is_protected"
                                    required
                                />
                                <div v-if="form.errors.slug" class="invalid-feedback d-block">{{ form.errors.slug }}</div>
                                <div v-if="role.is_protected" class="form-text">El slug de un rol del sistema no se puede modificar.</div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Permisos</label>
                            <PermissionMatrix v-model="form.permissions" :groups="permissions" />
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <Link href="/admin/roles" class="btn btn-outline-secondary">Cancelar</Link>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
