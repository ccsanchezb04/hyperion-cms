<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Media Library',
        href: '/admin/media',
    },
    {
        title: 'Batch Upload',
        href: '/admin/media/upload',
    },
];

const selectedFiles = ref<File[]>([]);
const uploading = ref(false);
const uploadProgress = ref(0);
const uploadResults = ref<{ success: number; failed: number; errors: string[] }>({
    success: 0,
    failed: 0,
    errors: [],
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        selectedFiles.value = Array.from(target.files);
    }
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer.files) {
        selectedFiles.value = Array.from(event.dataTransfer.files);
    }
};

const removeFile = (index: number) => {
    selectedFiles.value.splice(index, 1);
};

const uploadFiles = async () => {
    if (selectedFiles.value.length === 0) return;

    uploading.value = true;
    uploadProgress.value = 0;
    uploadResults.value = { success: 0, failed: 0, errors: [] };

    const formData = new FormData();
    selectedFiles.value.forEach((file) => {
        formData.append('files[]', file);
    });

    try {
        const response = await fetch('/api/v1/media/batch-upload', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok) {
            uploadResults.value.success = data.data.length;
            uploadResults.value.failed = data.errors.length;
            uploadResults.value.errors = data.errors.map((e: any) => e.error);
            selectedFiles.value = [];
        } else {
            uploadResults.value.failed = selectedFiles.value.length;
            uploadResults.value.errors = [data.message || 'Upload failed'];
        }
    } catch (error) {
        console.error('Error uploading files:', error);
        uploadResults.value.failed = selectedFiles.value.length;
        uploadResults.value.errors = ['Network error during upload'];
    } finally {
        uploading.value = false;
        uploadProgress.value = 100;
    }
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const isImage = (file: File) => file.type.startsWith('image/');
const isVideo = (file: File) => file.type.startsWith('video/');
</script>

<template>
    <Head title="Batch Upload" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-6">Batch Upload</h1>

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
                                multiple
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
                                        Select files
                                    </button>
                                    or drag and drop
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Upload up to 10 files at once (PNG, JPG, GIF, WEBP, MP4, PDF up to 10MB each)
                                </p>
                            </div>
                            <div v-else class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                <span class="ml-2 text-gray-600">Uploading {{ uploadProgress }}%...</span>
                            </div>
                        </div>

                        <!-- Selected Files -->
                        <div v-if="selectedFiles.length > 0" class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Selected Files ({{ selectedFiles.length }})</h3>
                            <div class="space-y-2">
                                <div
                                    v-for="(file, index) in selectedFiles"
                                    :key="index"
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                            <svg v-if="isImage(file)" class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <svg v-else-if="isVideo(file)" class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <svg v-else class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
                                            <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                    </div>
                                    <button
                                        @click="removeFile(index)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <div class="mt-4 flex justify-end space-x-4">
                                <Link
                                    href="/admin/media"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                >
                                    Cancel
                                </Link>
                                <button
                                    @click="uploadFiles"
                                    :disabled="uploading"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{ uploading ? 'Uploading...' : `Upload ${selectedFiles.length} Files` }}
                                </button>
                            </div>
                        </div>

                        <!-- Upload Results -->
                        <div v-if="uploadResults.success > 0 || uploadResults.failed > 0" class="mb-6">
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-2">
                                {{ uploadResults.success }} files uploaded successfully
                            </div>
                            <div v-if="uploadResults.failed > 0" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                                {{ uploadResults.failed }} files failed to upload
                                <ul v-if="uploadResults.errors.length > 0" class="mt-2 list-disc list-inside">
                                    <li v-for="(error, index) in uploadResults.errors" :key="index">{{ error }}</li>
                                </ul>
                            </div>
                            <div class="mt-4">
                                <Link
                                    href="/admin/media"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    View Media Library
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
