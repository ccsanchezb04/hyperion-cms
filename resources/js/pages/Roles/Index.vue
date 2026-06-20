<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

interface RoleRow {
    id: number;
    name: string;
    slug: string;
    is_protected: boolean;
    users_count: number;
    permissions_count: number;
}

defineProps<{ roles: RoleRow[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Roles', href: '/admin/roles' },
];

const destroy = (role: RoleRow) => {
    if (role.is_protected) return;
    if (role.users_count > 0) {
        alert('No se puede borrar un rol con usuarios asignados.');
        return;
    }
    if (!confirm(`¿Borrar el rol "${role.name}"?`)) return;
    router.delete(`/admin/roles/${role.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h4 fw-bold mb-1">Roles</h1>
                            <p class="text-body-secondary small mb-0">
                                Los roles agrupan permisos. Asignar un rol a un usuario le otorga todos los permisos del rol.
                            </p>
                        </div>
                        <Link href="/admin/roles/create" class="btn btn-primary">Crear rol</Link>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Slug</th>
                                    <th>Usuarios</th>
                                    <th>Permisos</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="role in roles" :key="role.id">
                                    <td class="fw-medium">
                                        {{ role.name }}
                                        <span v-if="role.is_protected" class="badge bg-warning text-dark ms-1" title="Rol del sistema">
                                            sistema
                                        </span>
                                    </td>
                                    <td><code>{{ role.slug }}</code></td>
                                    <td>{{ role.users_count }}</td>
                                    <td>{{ role.permissions_count }}</td>
                                    <td class="text-end">
                                        <Link
                                            :href="`/admin/roles/${role.id}/edit`"
                                            class="btn btn-sm btn-outline-primary me-1"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            :disabled="role.is_protected || role.users_count > 0"
                                            :title="role.is_protected ? 'Rol protegido' : (role.users_count > 0 ? 'Tiene usuarios asignados' : '')"
                                            @click="destroy(role)"
                                        >
                                            Borrar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
