<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface CategoryOption {
    id: number;
    name: string;
    slug: string;
    level: number;
}

defineProps<{
    categories: CategoryOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Categorías', href: '/admin/categories' },
    { title: 'Crear categoría', href: '/admin/categories/create' },
];

const form = useForm({
    name: '',
    slug: '',
    parent_id: null as number | null,
});

const generateSlug = () => {
    form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
};

const submit = () => form.post('/admin/categories');
</script>

<template>
    <Head title="Crear categoría" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-4">Crear categoría</h1>

                    <form @submit.prevent="submit" class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Nombre *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.name }"
                                @input="generateSlug"
                                required
                            />
                            <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="form-label">Slug *</label>
                            <input v-model="form.slug" type="text" class="form-control" :class="{ 'is-invalid': form.errors.slug }" required />
                            <div v-if="form.errors.slug" class="invalid-feedback d-block">{{ form.errors.slug }}</div>
                        </div>

                        <div>
                            <label class="form-label">Categoría padre</label>
                            <select v-model="form.parent_id" class="form-select" :class="{ 'is-invalid': form.errors.parent_id }">
                                <option :value="null">Sin padre (Categoría raíz)</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div v-if="form.errors.parent_id" class="invalid-feedback d-block">{{ form.errors.parent_id }}</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <Link href="/admin/categories" class="btn btn-outline-secondary">Cancelar</Link>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                {{ form.processing ? 'Guardando...' : 'Crear categoría' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
