<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface RoleRef { name: string; slug: string }

interface PermissionItem {
    slug: string;
    name: string;
    description: string | null;
    roles: RoleRef[];
}

defineProps<{ groups: Record<string, PermissionItem[]> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Permissions', href: '/admin/permissions' },
];

const domainTitle = (key: string): string => key.charAt(0).toUpperCase() + key.slice(1).replace('-', ' ');
</script>

<template>
    <Head title="Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h1 class="h4 fw-bold mb-1">Permissions</h1>
                        <p class="text-body-secondary small mb-0">
                            Lista de permisos definidos por el sistema (read-only). Para asignarlos a roles, edita el rol
                            correspondiente en <Link href="/admin/roles" class="text-decoration-none">/admin/roles</Link>.
                        </p>
                    </div>

                    <div class="accordion" id="permissions-accordion">
                        <div
                            v-for="(items, key, idx) in groups"
                            :key="key"
                            class="accordion-item"
                        >
                            <h2 class="accordion-header">
                                <button
                                    type="button"
                                    class="accordion-button"
                                    :class="idx === 0 ? '' : 'collapsed'"
                                    data-bs-toggle="collapse"
                                    :data-bs-target="`#group-${key}`"
                                    aria-expanded="true"
                                >
                                    {{ domainTitle(key) }}
                                    <span class="badge bg-secondary ms-2">{{ items.length }}</span>
                                </button>
                            </h2>
                            <div
                                :id="`group-${key}`"
                                class="accordion-collapse collapse"
                                :class="{ show: idx === 0 }"
                                data-bs-parent="#permissions-accordion"
                            >
                                <div class="accordion-body">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Slug</th>
                                                <th>Roles que lo tienen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="p in items" :key="p.slug">
                                                <td>
                                                    <span class="fw-medium">{{ p.name }}</span>
                                                    <p v-if="p.description" class="small text-body-secondary mb-0">{{ p.description }}</p>
                                                </td>
                                                <td><code class="small">{{ p.slug }}</code></td>
                                                <td>
                                                    <span v-for="r in p.roles" :key="r.slug" class="badge bg-info text-dark me-1">
                                                        {{ r.name }}
                                                    </span>
                                                    <span v-if="p.roles.length === 0" class="text-body-secondary small">— ninguno —</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
