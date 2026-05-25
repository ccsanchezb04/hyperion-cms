<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Contents',
        href: '/admin/contents',
    },
    {
        title: 'Create Content',
        href: '/admin/contents/create',
    },
];

const form = ref({
    title: '',
    slug: '',
    type: 'post',
    status: 'draft',
    body: '',
    body_type: 'html',
    summary: '',
    categories: [] as number[],
    media: [] as number[],
});

const categories = ref([]);
const media = ref([]);
const loading = ref(false);
const errors = ref<Record<string, string>>({});

const generateSlug = () => {
    form.value.slug = form.value.title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

const fetchCategories = async () => {
    try {
        const response = await fetch('/api/v1/categories/tree', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        categories.value = data.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const response = await fetch('/api/v1/contents', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.errors) {
                errors.value = data.errors;
            } else {
                errors.value = { general: data.message || 'An error occurred' };
            }
            return;
        }

        router.visit('/admin/contents');
    } catch (error) {
        console.error('Error creating content:', error);
        errors.value = { general: 'An error occurred while creating the content' };
    } finally {
        loading.value = false;
    }
};

// Initialize
fetchCategories();
</script>

<template>
    <Head title="Create Content" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-6">Create Content</h1>

                        <!-- Error Messages -->
                        <div v-if="errors.general" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            {{ errors.general }}
                        </div>

                        <form @submit.prevent="submitForm">
                            <!-- Title -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Title *
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    @input="generateSlug"
                                    required
                                />
                                <div v-if="errors.title" class="text-red-600 text-sm mt-1">
                                    {{ errors.title }}
                                </div>
                            </div>

                            <!-- Slug -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Slug *
                                </label>
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    required
                                />
                                <div v-if="errors.slug" class="text-red-600 text-sm mt-1">
                                    {{ errors.slug }}
                                </div>
                            </div>

                            <!-- Type -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Type
                                </label>
                                <select
                                    v-model="form.type"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                >
                                    <option value="post">Post</option>
                                    <option value="page">Page</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select
                                    v-model="form.status"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>

                            <!-- Body Type -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Body Format
                                </label>
                                <select
                                    v-model="form.body_type"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                >
                                    <option value="html">HTML</option>
                                    <option value="markdown">Markdown</option>
                                    <option value="plain">Plain Text</option>
                                </select>
                            </div>

                            <!-- Body -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Content Body
                                </label>
                                <textarea
                                    v-model="form.body"
                                    rows="10"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    placeholder="Write your content here..."
                                ></textarea>
                            </div>

                            <!-- Summary -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Summary
                                </label>
                                <textarea
                                    v-model="form.summary"
                                    rows="3"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    placeholder="Brief summary of the content..."
                                ></textarea>
                                <div v-if="errors.summary" class="text-red-600 text-sm mt-1">
                                    {{ errors.summary }}
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Categories
                                </label>
                                <div class="space-y-2">
                                    <div v-for="category in categories" :key="category.id">
                                        <label class="flex items-center">
                                            <input
                                                v-model="form.categories"
                                                type="checkbox"
                                                :value="category.id"
                                                class="mr-2"
                                            />
                                            <span>{{ category.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-4">
                                <Link
                                    href="/admin/contents"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{ loading ? 'Creating...' : 'Create Content' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
