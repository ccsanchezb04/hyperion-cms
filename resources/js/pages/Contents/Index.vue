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
    { title: 'Contenidos', href: '/admin/contents' },
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

const typeLabels: Record<string, string> = { post: 'Post', page: 'Página', custom: 'Personalizado' };
const statusLabels: Record<string, string> = { draft: 'Borrador', published: 'Publicado', archived: 'Archivado' };

const applyFilters = () => {
    router.get('/admin/contents', {
        type: filters.type || undefined,
        status: filters.status || undefined,
        search: filters.search || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch(() => [filters.type, filters.status], applyFilters);

const deleteContent = (id: number) => {
    if (!confirm('¿Estás seguro de que deseas eliminar este contenido?')) return;
    router.delete(`/admin/contents/${id}`, { preserveScroll: true });
};

const publishContent = (id: number) => router.post(`/admin/contents/${id}/publish`, {}, { preserveScroll: true });
const archiveContent = (id: number) => router.post(`/admin/contents/${id}/archive`, {}, { preserveScroll: true });
</script>

<template>
    <Head title="Contenidos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h4 fw-bold mb-0">Contenidos</h1>
                        <Link href="/admin/contents/create" class="btn btn-primary">Crear contenido</Link>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <input v-model="filters.search" type="text" placeholder="Buscar contenidos..." class="form-control" @keyup.enter="applyFilters" />
                        </div>
                        <div class="col-12 col-md-4">
                            <select v-model="filters.type" class="form-select">
                                <option value="">Todos los tipos</option>
                                <option value="post">Post</option>
                                <option value="page">Página</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <select v-model="filters.status" class="form-select">
                                <option value="">Todos los estados</option>
                                <option value="draft">Borrador</option>
                                <option value="published">Publicado</option>
                                <option value="archived">Archivado</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="contents.length > 0" class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Autor</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acciones</th>
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
                                    <td class="small text-body-secondary">{{ content.author?.name || 'Desconocido' }}</td>
                                    <td class="small text-body-secondary">{{ content.created_at ? new Date(content.created_at).toLocaleDateString() : '' }}</td>
                                    <td class="text-end small">
                                        <div class="d-inline-flex gap-2">
                                            <Link :href="`/admin/contents/${content.id}/edit`" class="link-primary text-decoration-none">Editar</Link>
                                            <button v-if="content.status === 'draft'" type="button" class="btn btn-link btn-sm text-success p-0" @click="publishContent(content.id)">Publicar</button>
                                            <button v-if="content.status !== 'archived'" type="button" class="btn btn-link btn-sm text-warning p-0" @click="archiveContent(content.id)">Archivar</button>
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="deleteContent(content.id)">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-5 text-body-secondary">
                        <p class="mb-0">No se encontraron contenidos.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
