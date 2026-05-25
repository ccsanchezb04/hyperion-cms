<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Media Library',
        href: '/admin/media',
    },
];

interface MediaItem {
    id: number;
    path: string;
    type: string;
    created_at: string;
    uploaded_by: {
        name: string;
    };
    url: string;
    filename: string;
}

const mediaItems = ref<MediaItem[]>([]);
const loading = ref(true);
const uploading = ref(false);
const selectedFile = ref<File | null>(null);
const filters = ref({
    type: '',
    search: '',
});

const typeColors = {
    image: 'bg-purple-100 text-purple-800',
    video: 'bg-red-100 text-red-800',
    document: 'bg-blue-100 text-blue-800',
};

const typeLabels = {
    image: 'Image',
    video: 'Video',
    document: 'Document',
};

const fetchMedia = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        if (filters.value.type) params.append('type', filters.value.type);
        if (filters.value.search) params.append('search', filters.value.search);

        const response = await fetch(`/api/v1/media?${params}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        mediaItems.value = data.data.map((item: any) => ({
            ...item,
            url: item.path ? `/storage/${item.path}` : '',
            filename: item.path ? item.path.split('/').pop() : '',
        }));
    } catch (error) {
        console.error('Error fetching media:', error);
    } finally {
        loading.value = false;
    }
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        selectedFile.value = target.files[0];
        uploadFile();
    }
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer.files && event.dataTransfer.files[0]) {
        selectedFile.value = event.dataTransfer.files[0];
        uploadFile();
    }
};

const uploadFile = async () => {
    if (!selectedFile.value) return;

    uploading.value = true;
    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const response = await fetch('/api/v1/media/upload', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (response.ok) {
            selectedFile.value = null;
            fetchMedia();
        } else {
            alert('Error uploading file');
        }
    } catch (error) {
        console.error('Error uploading file:', error);
        alert('Error uploading file');
    } finally {
        uploading.value = false;
    }
};

const deleteMedia = (id: number) => {
    if (confirm('Are you sure you want to delete this file?')) {
        router.delete(`/api/v1/media/${id}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
            onSuccess: () => {
                fetchMedia();
            },
        });
    }
};

const isImage = (type: string) => type.startsWith('image/');
const isVideo = (type: string) => type.startsWith('video/');

onMounted(() => {
    fetchMedia();
});
</script>

<template>
    <Head title="Media Library" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold text-gray-900">Media Library</h1>
                        </div>

                        <!-- Upload Area -->
                        <div
                            class="mb-6 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-500 transition-colors"
                            @dragover.prevent
                            @drop.prevent="handleDrop"
                        >
                            <input
                                type="file"
                                ref="fileInput"
                                class="hidden"
                                @change="handleFileSelect"
                                accept="image/*,video/*,.pdf"
                            />
                            <div v-if="!uploading">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="mt-1 text-sm text-gray-600">
                                    <button
                                        type="button"
                                        class="font-medium text-indigo-600 hover:text-indigo-500"
                                        @click="$refs.fileInput.click()"
                                    >
                                        Upload a file
                                    </button>
                                    or drag and drop
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    PNG, JPG, GIF, WEBP, MP4, PDF up to 10MB
                                </p>
                            </div>
                            <div v-else class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                <span class="ml-2 text-gray-600">Uploading...</span>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search files..."
                                class="border border-gray-300 rounded-md px-4 py-2"
                                @keyup.enter="fetchMedia"
                            />
                            <select
                                v-model="filters.type"
                                class="border border-gray-300 rounded-md px-4 py-2"
                                @change="fetchMedia"
                            >
                                <option value="">All Types</option>
                                <option value="image">Images</option>
                                <option value="video">Videos</option>
                                <option value="document">Documents</option>
                            </select>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>

                        <!-- Media Grid -->
                        <div v-else-if="mediaItems.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <div
                                v-for="item in mediaItems"
                                :key="item.id"
                                class="relative group border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
                            >
                                <!-- Preview -->
                                <div class="aspect-square bg-gray-100 flex items-center justify-center">
                                    <img
                                        v-if="isImage(item.type)"
                                        :src="item.url"
                                        :alt="item.filename"
                                        class="w-full h-full object-cover"
                                    />
                                    <video
                                        v-else-if="isVideo(item.type)"
                                        :src="item.url"
                                        class="w-full h-full object-cover"
                                        controls
                                    />
                                    <div v-else class="text-center p-4">
                                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="mt-2 text-xs text-gray-500 truncate">{{ item.filename }}</p>
                                    </div>
                                </div>

                                <!-- Overlay -->
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="deleteMedia(item.id)"
                                            class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full"
                                            title="Delete"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="p-2">
                                    <p class="text-xs text-gray-500 truncate">{{ item.filename }}</p>
                                    <div class="flex items-center justify-between mt-1">
                                        <span :class="`px-1 inline-flex text-xs leading-4 font-semibold rounded-full ${typeColors[item.type.split('/')[0]] || 'bg-gray-100 text-gray-800'}`">
                                            {{ typeLabels[item.type.split('/')[0]] || item.type.split('/')[0] }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ new Date(item.created_at).toLocaleDateString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-gray-500">No media files found.</p>
                            <p class="mt-1 text-sm text-gray-400">Upload your first file to get started.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
