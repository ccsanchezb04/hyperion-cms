<script setup lang="ts">
import PermissionMatrix from '@/components/PermissionMatrix.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

interface PermissionItem { slug: string; name: string; description: string | null }

defineProps<{ permissions: Record<string, PermissionItem[]> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Roles', href: '/admin/roles' },
    { title: 'Crear', href: '/admin/roles/create' },
];

const form = useForm({
    name: '',
    slug: '',
    permissions: [] as string[],
});

// Auto-slug desde name
watch(() => form.name, (v) => {
    if (!form.slug) {
        form.slug = v.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }
});

const submit = () => {
    form.post('/admin/roles', { preserveScroll: true });
};
</script>

<template>
    <Head title="Crear rol" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4" style="max-width: 920px;">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-4">Crear rol</h1>

                    <form @submit.prevent="submit" class="d-flex flex-column gap-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" required />
                                <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug *</label>
                                <input v-model="form.slug" type="text" class="form-control" :class="{ 'is-invalid': form.errors.slug }" required pattern="[a-z0-9-]+" />
                                <div v-if="form.errors.slug" class="invalid-feedback d-block">{{ form.errors.slug }}</div>
                                <div class="form-text">Solo minúsculas, números y guiones.</div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Permisos</label>
                            <PermissionMatrix v-model="form.permissions" :groups="permissions" />
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <Link href="/admin/roles" class="btn btn-outline-secondary">Cancelar</Link>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">Crear rol</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
