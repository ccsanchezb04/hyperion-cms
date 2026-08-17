<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { usePermissions } from '@/composables/usePermissions';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

interface UserRow {
    id: number;
    name: string;
    email: string;
    status: 'active' | 'inactive';
    roles: string[];
    created_at: string | null;
}

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface RoleOption { slug: string; name: string }

const props = defineProps<{
    users: Paginated<UserRow>;
    filters: { status: string; role: string; search: string };
    roles: RoleOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Users', href: '/admin/users' },
];

const { can } = usePermissions();

const filters = reactive({
    status: props.filters.status ?? '',
    role: props.filters.role ?? '',
    search: props.filters.search ?? '',
});

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
watch(filters, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get('/admin/users', filters, { preserveScroll: true, preserveState: true });
    }, 300);
});

const roleName = (slug: string) => props.roles.find((r) => r.slug === slug)?.name ?? slug;

const destroy = (id: number) => {
    if (!confirm('¿Borrar este usuario? Se conserva soft-deleted.')) return;
    router.delete(`/admin/users/${id}`, { preserveScroll: true });
};

const toggleStatus = (u: UserRow) => {
    const url = u.status === 'active' ? `/admin/users/${u.id}/deactivate` : `/admin/users/${u.id}/activate`;
    router.post(url, {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h4 fw-bold mb-0">Users</h1>
                        <Link v-if="can('create-user')" href="/admin/users/create" class="btn btn-primary">Crear usuario</Link>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <input v-model="filters.search" type="text" class="form-control" placeholder="Buscar por nombre o email…" />
                        </div>
                        <div class="col-md-3">
                            <select v-model="filters.status" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="active">Activos</option>
                                <option value="inactive">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select v-model="filters.role" class="form-select">
                                <option value="">Todos los roles</option>
                                <option v-for="r in roles" :key="r.slug" :value="r.slug">{{ r.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="users.data.length === 0" class="text-center py-5 text-body-secondary">
                        <p class="mb-0">No hay usuarios con esos criterios.</p>
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th>Roles</th>
                                    <th>Creado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="u in users.data" :key="u.id">
                                    <td class="fw-medium">{{ u.name }}</td>
                                    <td><a :href="`mailto:${u.email}`" class="text-decoration-none">{{ u.email }}</a></td>
                                    <td>
                                        <span class="badge" :class="u.status === 'active' ? 'bg-success' : 'bg-secondary'">
                                            {{ u.status === 'active' ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-for="r in u.roles" :key="r" class="badge bg-info text-dark me-1">
                                            {{ roleName(r) }}
                                        </span>
                                        <span v-if="u.roles.length === 0" class="text-body-secondary small">— sin roles —</span>
                                    </td>
                                    <td class="text-body-secondary small">{{ u.created_at }}</td>
                                    <td class="text-end">
                                        <Link
                                            v-if="can('edit-user')"
                                            :href="`/admin/users/${u.id}/edit`"
                                            class="btn btn-sm btn-outline-primary me-1"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            v-if="can('edit-user')"
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary me-1"
                                            @click="toggleStatus(u)"
                                        >
                                            {{ u.status === 'active' ? 'Desactivar' : 'Activar' }}
                                        </button>
                                        <button
                                            v-if="can('delete-user')"
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            @click="destroy(u.id)"
                                        >
                                            Borrar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <nav v-if="users.last_page > 1" class="mt-3">
                        <ul class="pagination justify-content-center mb-0">
                            <li
                                v-for="(link, idx) in users.links"
                                :key="idx"
                                class="page-item"
                                :class="{ active: link.active, disabled: !link.url }"
                            >
                                <Link v-if="link.url" :href="link.url" class="page-link"><span v-html="link.label" /></Link>
                                <span v-else class="page-link" v-html="link.label" />
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
