<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
}

interface CategoryOption {
    id: number;
    name: string;
    slug: string;
    level: number;
}

const props = defineProps<{
    category: Category;
    categories: CategoryOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Categories', href: '/admin/categories' },
    { title: 'Edit Category', href: `/admin/categories/${props.category.id}/edit` },
];

const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
    parent_id: props.category.parent_id,
});

const generateSlug = () => {
    form.slug = form.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
};

const submit = () => form.put(`/admin/categories/${props.category.id}`);
</script>

<template>
    <Head title="Edit Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-4">Edit Category</h1>

                    <form @submit.prevent="submit" class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Name *</label>
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
                            <label class="form-label">Parent Category</label>
                            <select v-model="form.parent_id" class="form-select" :class="{ 'is-invalid': form.errors.parent_id }">
                                <option :value="null">No Parent (Root Category)</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div v-if="form.errors.parent_id" class="invalid-feedback d-block">{{ form.errors.parent_id }}</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <Link href="/admin/categories" class="btn btn-outline-secondary">Cancel</Link>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Category' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
