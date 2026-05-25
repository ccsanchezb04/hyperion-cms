<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps<{
    id: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Categories',
        href: '/admin/categories',
    },
    {
        title: 'Edit Category',
        href: `/admin/categories/${props.id}/edit`,
    },
];

const form = ref({
    name: '',
    slug: '',
    parent_id: null as number | null,
});

const categories = ref([]);
const loading = ref(false);
const errors = ref<Record<string, string>>({});

const generateSlug = () => {
    form.value.slug = form.value.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

const fetchCategory = async () => {
    try {
        const response = await fetch(`/api/v1/categories/${props.id}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        const category = data.data;
        
        form.value = {
            name: category.name,
            slug: category.slug,
            parent_id: category.parent_id,
        };
    } catch (error) {
        console.error('Error fetching category:', error);
    }
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

const flattenCategories = (cats: any[], level = 0, excludeId: number | null = null): any[] => {
    const result: any[] = [];
    cats.forEach(cat => {
        if (cat.id !== excludeId) {
            result.push({
                ...cat,
                level,
                name: '  '.repeat(level) + cat.name,
            });
            if (cat.children && cat.children.length > 0) {
                result.push(...flattenCategories(cat.children, level + 1, excludeId));
            }
        }
    });
    return result;
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const response = await fetch(`/api/v1/categories/${props.id}`, {
            method: 'PUT',
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

        router.visit('/admin/categories');
    } catch (error) {
        console.error('Error updating category:', error);
        errors.value = { general: 'An error occurred while updating the category' };
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCategory();
    fetchCategories();
});
</script>

<template>
    <Head title="Edit Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Category</h1>

                        <!-- Error Messages -->
                        <div v-if="errors.general" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            {{ errors.general }}
                        </div>

                        <form @submit.prevent="submitForm">
                            <!-- Name -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Name *
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                    @input="generateSlug"
                                    required
                                />
                                <div v-if="errors.name" class="text-red-600 text-sm mt-1">
                                    {{ errors.name }}
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

                            <!-- Parent Category -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Parent Category
                                </label>
                                <select
                                    v-model="form.parent_id"
                                    class="w-full border border-gray-300 rounded-md px-4 py-2"
                                >
                                    <option :value="null">No Parent (Root Category)</option>
                                    <option 
                                        v-for="category in flattenCategories(categories, 0, props.id)" 
                                        :key="category.id" 
                                        :value="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                                <div v-if="errors.parent_id" class="text-red-600 text-sm mt-1">
                                    {{ errors.parent_id }}
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-4">
                                <Link
                                    href="/admin/categories"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{ loading ? 'Updating...' : 'Update Category' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
