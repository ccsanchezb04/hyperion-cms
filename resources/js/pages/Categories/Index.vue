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
        title: 'Categories',
        href: '/admin/categories',
    },
];

interface Category {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    children?: Category[];
}

const categories = ref<Category[]>([]);
const loading = ref(true);
const expandedCategories = ref<Set<number>>(new Set());
const searchQuery = ref('');

const fetchCategories = async () => {
    loading.value = true;
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
    } finally {
        loading.value = false;
    }
};

const toggleExpand = (categoryId: number) => {
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
};

const isExpanded = (categoryId: number) => {
    return expandedCategories.value.has(categoryId);
};

const deleteCategory = (id: number) => {
    if (confirm('Are you sure you want to delete this category? This will also delete all subcategories.')) {
        router.delete(`/api/v1/categories/${id}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
            onSuccess: () => {
                fetchCategories();
            },
        });
    }
};

const filteredCategories = (cats: Category[]): Category[] => {
    if (!searchQuery.value) return cats;
    
    const query = searchQuery.value.toLowerCase();
    const result: Category[] = [];
    
    const filterRecursive = (categories: Category[]): Category[] => {
        return categories.reduce((acc: Category[], cat) => {
            const matchesName = cat.name.toLowerCase().includes(query);
            const matchesSlug = cat.slug.toLowerCase().includes(query);
            const filteredChildren = cat.children ? filterRecursive(cat.children) : [];
            
            if (matchesName || matchesSlug || filteredChildren.length > 0) {
                acc.push({
                    ...cat,
                    children: filteredChildren.length > 0 ? filteredChildren : cat.children,
                });
            }
            
            return acc;
        }, []);
    };
    
    return filterRecursive(cats);
};

const renderCategoryTree = (categories: Category[], level = 0) => {
    return categories.map(category => `
        <div class="category-item" style="margin-left: ${level * 20}px">
            <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg mb-2 hover:shadow-md transition-shadow">
                <div class="flex items-center space-x-3">
                    <button 
                        @click="toggleExpand(${category.id})"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        <svg v-if="${category.children && category.children.length > 0}" 
                             class="h-4 w-4 transform transition-transform ${isExpanded(${category.id}) ? 'rotate-90' : ''}" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div>
                        <p class="font-medium text-gray-900">${category.name}</p>
                        <p class="text-sm text-gray-500">${category.slug}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Link 
                        :href="'/admin/categories/' + ${category.id} + '/edit'"
                        class="text-indigo-600 hover:text-indigo-800 text-sm"
                    >
                        Edit
                    </Link>
                    <button 
                        @click="deleteCategory(${category.id})"
                        class="text-red-600 hover:text-red-800 text-sm"
                    >
                        Delete
                    </button>
                </div>
            </div>
            ${category.children && isExpanded(${category.id}) ? renderCategoryTree(category.children, level + 1) : ''}
        </div>
    `).join('');
};

onMounted(() => {
    fetchCategories();
});
</script>

<template>
    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                            <Link
                                href="/admin/categories/create"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Create Category
                            </Link>
                        </div>

                        <!-- Search -->
                        <div class="mb-6">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search categories..."
                                class="w-full border border-gray-300 rounded-md px-4 py-2"
                            />
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>

                        <!-- Categories Tree -->
                        <div v-else-if="filteredCategories(categories).length > 0" class="space-y-2">
                            <div 
                                v-for="category in filteredCategories(categories)" 
                                :key="category.id"
                                class="category-item"
                            >
                                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg mb-2 hover:shadow-md transition-shadow">
                                    <div class="flex items-center space-x-3">
                                        <button 
                                            @click="toggleExpand(category.id)"
                                            class="text-gray-400 hover:text-gray-600 focus:outline-none"
                                        >
                                            <svg 
                                                v-if="category.children && category.children.length > 0" 
                                                class="h-4 w-4 transform transition-transform"
                                                :class="{ 'rotate-90': isExpanded(category.id) }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ category.name }}</p>
                                            <p class="text-sm text-gray-500">{{ category.slug }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Link 
                                            :href="`/admin/categories/${category.id}/edit`"
                                            class="text-indigo-600 hover:text-indigo-800 text-sm"
                                        >
                                            Edit
                                        </Link>
                                        <button 
                                            @click="deleteCategory(category.id)"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Children -->
                                <div 
                                    v-if="category.children && isExpanded(category.id)" 
                                    class="ml-6 space-y-2"
                                >
                                    <div 
                                        v-for="child in category.children" 
                                        :key="child.id"
                                        class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <div class="w-4"></div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ child.name }}</p>
                                                <p class="text-sm text-gray-500">{{ child.slug }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Link 
                                                :href="`/admin/categories/${child.id}/edit`"
                                                class="text-indigo-600 hover:text-indigo-800 text-sm"
                                            >
                                                Edit
                                            </Link>
                                            <button 
                                                @click="deleteCategory(child.id)"
                                                class="text-red-600 hover:text-red-800 text-sm"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <p class="mt-2 text-gray-500">No categories found.</p>
                            <p class="text-sm text-gray-400">Create your first category to get started.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
