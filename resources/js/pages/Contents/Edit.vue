<script setup lang="ts">
import GoogleSnippet from '@/components/SeoPreviews/GoogleSnippet.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface ContentSeoForm {
    meta_title: string;
    meta_description: string;
    og_image: string;
    canonical: string;
    noindex: boolean;
}

interface ContentDetail {
    id: number;
    title: string;
    slug: string;
    type: string;
    status: string;
    body: string;
    categories: number[];
    seo: ContentSeoForm;
}

interface CategoryOption {
    id: number;
    name: string;
}

const props = defineProps<{
    content: ContentDetail;
    categories: CategoryOption[];
    canonicalHost?: string;
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
    seo: {
        meta_title:       props.content.seo.meta_title ?? '',
        meta_description: props.content.seo.meta_description ?? '',
        og_image:         props.content.seo.og_image ?? '',
        canonical:        props.content.seo.canonical ?? '',
        noindex:          props.content.seo.noindex ?? false,
    },
});

const generateSlug = () => {
    form.slug = form.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
};

const submit = () => form.put(`/admin/contents/${props.content.id}`);

// Preview-only: arma la URL final usando canonical override o el host configurado
const previewUrl = computed(() => {
    if (form.seo.canonical) return form.seo.canonical;
    const host = props.canonicalHost ?? 'https://juanferseguros.com';
    return `${host.replace(/\/$/, '')}/soluciones/${form.slug}`;
});

const previewTitle = computed(() => form.seo.meta_title || form.title || 'Title');
const previewDesc = computed(() =>
    form.seo.meta_description || (form.body ? form.body.slice(0, 160) : 'Description'),
);
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

                        <details class="border rounded-3 p-3 mt-2" open>
                            <summary class="fw-semibold mb-0" style="cursor: pointer;">
                                <i class="bi bi-search me-1"></i>
                                SEO overrides (per-page)
                            </summary>
                            <p class="text-body-secondary small mt-2 mb-3">
                                Dejar vacío usa los defaults globales (configurables en /admin/seo)
                                y la meta description de la categoría. Estos campos pisan esos defaults
                                solo para esta página.
                            </p>

                            <div class="mb-3">
                                <label class="form-label">Meta title override</label>
                                <input v-model="form.seo.meta_title" type="text" class="form-control" :class="{ 'is-invalid': form.errors['seo.meta_title'] }" maxlength="255" />
                                <div class="form-text">Si vacío, se usa el título de la página con el template global.</div>
                                <div v-if="form.errors['seo.meta_title']" class="invalid-feedback d-block">{{ form.errors['seo.meta_title'] }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta description override</label>
                                <textarea v-model="form.seo.meta_description" class="form-control" :class="{ 'is-invalid': form.errors['seo.meta_description'] }" rows="2" maxlength="320"></textarea>
                                <div class="form-text">Si vacío, se usa la meta description de la categoría.</div>
                                <div v-if="form.errors['seo.meta_description']" class="invalid-feedback d-block">{{ form.errors['seo.meta_description'] }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">OG image override</label>
                                <input v-model="form.seo.og_image" type="text" class="form-control" :class="{ 'is-invalid': form.errors['seo.og_image'] }" placeholder="/storage/site/seo/og-custom.jpg" />
                                <div class="form-text">Path o URL absoluta. Si vacío se usa la OG de la categoría.</div>
                                <div v-if="form.errors['seo.og_image']" class="invalid-feedback d-block">{{ form.errors['seo.og_image'] }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Canonical URL</label>
                                <input v-model="form.seo.canonical" type="url" class="form-control" :class="{ 'is-invalid': form.errors['seo.canonical'] }" placeholder="https://juanferseguros.com/ruta-custom" />
                                <div class="form-text">Si vacío se calcula automáticamente.</div>
                                <div v-if="form.errors['seo.canonical']" class="invalid-feedback d-block">{{ form.errors['seo.canonical'] }}</div>
                            </div>

                            <div class="form-check mb-3">
                                <input v-model="form.seo.noindex" type="checkbox" id="seo-noindex" class="form-check-input" />
                                <label for="seo-noindex" class="form-check-label">
                                    No indexar esta página (<code>noindex,nofollow</code>)
                                </label>
                                <div class="form-text">Pisa la política global de robots solo para esta página.</div>
                            </div>

                            <div class="mt-3">
                                <div class="text-body-secondary small mb-1">Preview en Google:</div>
                                <GoogleSnippet :title="previewTitle" :description="previewDesc" :url="previewUrl" />
                            </div>
                        </details>

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
