<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

interface MediaItem {
    id: number;
    path: string;
    url: string;
    filename: string;
    mime_type: string;
    type: string;
    created_at: string | null;
}

const props = defineProps<{
    media: MediaItem[];
    filters: { type?: string; search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Media Library', href: '/admin/media' },
];

const filters = reactive({
    type: props.filters.type ?? '',
    search: props.filters.search ?? '',
});

const fileInput = ref<HTMLInputElement | null>(null);

const uploadForm = useForm<{ file: File | null }>({ file: null });

const applyFilters = () => {
    router.get('/admin/media', {
        type: filters.type || undefined,
        search: filters.search || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

watch(() => filters.type, applyFilters);

const typeBadge: Record<string, string> = {
    image: 'bg-info',
    video: 'bg-danger',
    document: 'bg-primary',
};

const typeLabels: Record<string, string> = {
    image: 'Image',
    video: 'Video',
    document: 'Document',
};

const upload = (file: File) => {
    uploadForm.file = file;
    uploadForm.post('/admin/media/upload', {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => uploadForm.reset(),
    });
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files?.[0]) upload(target.files[0]);
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer?.files[0]) upload(event.dataTransfer.files[0]);
};

const deleteMedia = (id: number) => {
    if (!confirm('Are you sure you want to delete this file?')) return;
    router.delete(`/admin/media/${id}`, { preserveScroll: true });
};

const isImage = (type: string) => type.startsWith('image/');
const isVideo = (type: string) => type.startsWith('video/');
</script>

<template>
    <Head title="Media Library" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-4">Media Library</h1>

                    <div
                        class="dropzone border border-2 border-dashed rounded p-5 text-center mb-4"
                        @dragover.prevent
                        @drop.prevent="handleDrop"
                    >
                        <input ref="fileInput" type="file" class="d-none" @change="handleFileSelect" accept="image/*,video/*,.pdf" />

                        <template v-if="!uploadForm.processing">
                            <i class="bi bi-cloud-arrow-up display-3 text-body-secondary"></i>
                            <p class="mt-2 small mb-1">
                                <button type="button" class="btn btn-link p-0 fw-medium" @click="fileInput?.click()">Upload a file</button>
                                or drag and drop
                            </p>
                            <p class="small text-body-secondary mb-0">PNG, JPG, GIF, WEBP, MP4, PDF up to 10MB</p>
                            <p v-if="uploadForm.errors.file" class="small text-danger mb-0 mt-2">{{ uploadForm.errors.file }}</p>
                        </template>
                        <template v-else>
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Uploading...</span>
                            </div>
                            <p class="mt-2 small text-body-secondary mb-0">Uploading...</p>
                        </template>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <input v-model="filters.search" type="text" class="form-control" placeholder="Search files..." @keyup.enter="applyFilters" />
                        </div>
                        <div class="col-12 col-md-6">
                            <select v-model="filters.type" class="form-select">
                                <option value="">All Types</option>
                                <option value="image">Images</option>
                                <option value="video">Videos</option>
                                <option value="document">Documents</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="media.length > 0" class="row g-3">
                        <div v-for="item in media" :key="item.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <div class="card h-100 shadow-sm media-card">
                                <div class="ratio ratio-1x1 bg-body-tertiary">
                                    <img v-if="isImage(item.mime_type)" :src="item.url" :alt="item.filename" class="object-fit-cover" />
                                    <video v-else-if="isVideo(item.mime_type)" :src="item.url" controls class="object-fit-cover"></video>
                                    <div v-else class="d-flex flex-column align-items-center justify-content-center p-3">
                                        <i class="bi bi-file-earmark display-6 text-body-secondary"></i>
                                        <p class="small text-body-secondary text-truncate w-100 mt-1 mb-0">{{ item.filename }}</p>
                                    </div>
                                </div>
                                <div class="card-body p-2">
                                    <p class="small text-truncate mb-1">{{ item.filename }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge" :class="typeBadge[item.mime_type.split('/')[0]] ?? 'bg-secondary'">
                                            {{ typeLabels[item.mime_type.split('/')[0]] ?? item.mime_type.split('/')[0] }}
                                        </span>
                                        <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="deleteMedia(item.id)" :title="`Delete ${item.filename}`">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-5">
                        <i class="bi bi-images display-1 text-body-secondary"></i>
                        <p class="mt-2 text-body-secondary mb-0">No media files found.</p>
                        <p class="small text-body-secondary">Upload your first file to get started.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.dropzone:hover {
    border-color: var(--bs-primary) !important;
}
</style>
