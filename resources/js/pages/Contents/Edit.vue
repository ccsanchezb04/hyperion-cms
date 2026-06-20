<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface ContentDetail {
    id: number;
    title: string;
    slug: string;
    type: string;
    status: string;
    body: string;
    categories: number[];
}

interface CategoryOption {
    id: number;
    name: string;
}

const props = defineProps<{
    content: ContentDetail;
    categories: CategoryOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Contents', href: '/admin/contents' },
    { title: 'Edit Content', href: `/admin/contents/${props.content.id}/edit` },
];

const form = useForm({
    title: props.content.title,
    slug: props.content.slug,
    type: props.content.type,
    status: props.content.status,
    body: props.content.body,
    categories: [...props.content.categories],
});

const generateSlug = () => {
    form.slug = form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
};

const submit = () => form.put(`/admin/contents/${props.content.id}`);
</script>

<template>
    <Head title="Edit Content" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-4">Edit Content</h1>

                    <form @submit.prevent="submit" class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Title *</label>
                            <input v-model="form.title" type="text" class="form-control" :class="{ 'is-invalid': form.errors.title }" @input="generateSlug" required />
                            <div v-if="form.errors.title" class="invalid-feedback d-block">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <label class="form-label">Slug *</label>
                            <input v-model="form.slug" type="text" class="form-control" :class="{ 'is-invalid': form.errors.slug }" required />
                            <div v-if="form.errors.slug" class="invalid-feedback d-block">{{ form.errors.slug }}</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">Type</label>
                                <select v-model="form.type" class="form-select">
                                    <option value="post">Post</option>
                                    <option value="page">Page</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Status</label>
                                <select v-model="form.status" class="form-select">
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Content Body</label>
                            <textarea v-model="form.body" rows="10" class="form-control" placeholder="Write your content here..."></textarea>
                        </div>

                        <div v-if="categories.length">
                            <label class="form-label">Categories</label>
                            <div class="d-flex flex-column gap-2">
                                <div v-for="category in categories" :key="category.id" class="form-check">
                                    <input v-model="form.categories" type="checkbox" :value="category.id" :id="`cat-${category.id}`" class="form-check-input" />
                                    <label :for="`cat-${category.id}`" class="form-check-label">{{ category.name }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <Link href="/admin/contents" class="btn btn-outline-secondary">Cancel</Link>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Content' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
