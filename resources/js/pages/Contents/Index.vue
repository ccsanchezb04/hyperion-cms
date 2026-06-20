<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

interface ContentRow {
    id: number;
    title: string;
    slug: string;
    type: string;
    status: string;
    published_at: string | null;
    created_at: string | null;
    author: { name: string } | null;
}

const props = defineProps<{
    contents: ContentRow[];
    filters: { type?: string; status?: string; search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Contents', href: '/admin/contents' },
];

const filters = reactive({
    type: props.filters.type ?? '',
    status: props.filters.status ?? '',
    search: props.filters.search ?? '',
});

const statusBadge: Record<string, string> = {
    draft: 'bg-warning text-dark',
    published: 'bg-success',
    archived: 'bg-secondary',
};

const typeLabels: Record<string, string> = { post: 'Post', page: 'Page', custom: 'Custom' };
const statusLabels: Record<string, string> = { draft: 'Draft', published: 'Published', archived: 'Archived' };

const applyFilters = () => {
    router.get('/admin/contents', {
        type: filters.type || undefined,
        status: filters.status || undefined,
        search: filters.search || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch(() => [filters.type, filters.status], applyFilters);

const deleteContent = (id: number) => {
    if (!confirm('Are you sure you want to delete this content?')) return;
    router.delete(`/admin/contents/${id}`, { preserveScroll: true });
};

const publishContent = (id: number) => router.post(`/admin/contents/${id}/publish`, {}, { preserveScroll: true });
const archiveContent = (id: number) => router.post(`/admin/contents/${id}/archive`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Contents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h4 fw-bold mb-0">Contents</h1>
                        <Link href="/admin/contents/create" class="btn btn-primary">Create Content</Link>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <input v-model="filters.search" type="text" placeholder="Search contents..." class="form-control" @keyup.enter="applyFilters" />
                        </div>
                        <div class="col-12 col-md-4">
                            <select v-model="filters.type" class="form-select">
                                <option value="">All Types</option>
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <select v-model="filters.status" class="form-select">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="contents.length > 0" class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Author</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="content in contents" :key="content.id">
                                    <td>
                                        <div class="fw-medium">{{ content.title }}</div>
                                        <div class="small text-body-secondary">{{ content.slug }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ typeLabels[content.type] ?? content.type }}</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="statusBadge[content.status]">{{ statusLabels[content.status] ?? content.status }}</span>
                                    </td>
                                    <td class="small text-body-secondary">{{ content.author?.name || 'Unknown' }}</td>
                                    <td class="small text-body-secondary">{{ content.created_at ? new Date(content.created_at).toLocaleDateString() : '' }}</td>
                                    <td class="text-end small">
                                        <div class="d-inline-flex gap-2">
                                            <Link :href="`/admin/contents/${content.id}/edit`" class="link-primary text-decoration-none">Edit</Link>
                                            <button v-if="content.status === 'draft'" type="button" class="btn btn-link btn-sm text-success p-0" @click="publishContent(content.id)">Publish</button>
                                            <button v-if="content.status === 'published'" type="button" class="btn btn-link btn-sm text-warning p-0" @click="archiveContent(content.id)">Archive</button>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="deleteContent(content.id)">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-5 text-body-secondary">
                        <p class="mb-0">No contents found.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
