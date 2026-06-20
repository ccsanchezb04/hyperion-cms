<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    children: Category[];
}

defineProps<{
    categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Categories', href: '/admin/categories' },
];

const expanded = ref<Set<number>>(new Set());
const searchQuery = ref('');

const toggleExpand = (id: number) => {
    if (expanded.value.has(id)) expanded.value.delete(id);
    else expanded.value.add(id);
};

const isExpanded = (id: number) => expanded.value.has(id);

const deleteCategory = (id: number) => {
    if (!confirm('Are you sure you want to delete this category? Subcategories must be removed first.')) return;
    router.delete(`/admin/categories/${id}`, { preserveScroll: true });
};

const filterTree = (list: Category[]): Category[] => {
    if (!searchQuery.value) return list;
    const q = searchQuery.value.toLowerCase();
    return list.reduce<Category[]>((acc, cat) => {
        const matches = cat.name.toLowerCase().includes(q) || cat.slug.toLowerCase().includes(q);
        const children = filterTree(cat.children ?? []);
        if (matches || children.length) acc.push({ ...cat, children });
        return acc;
    }, []);
};
</script>

<template>
    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h4 fw-bold mb-0">Categories</h1>
                        <Link href="/admin/categories/create" class="btn btn-primary">Create Category</Link>
                    </div>

                    <div class="mb-3">
                        <input v-model="searchQuery" type="text" placeholder="Search categories..." class="form-control" />
                    </div>

                    <div v-if="filterTree(categories).length > 0" class="d-flex flex-column gap-2">
                        <div v-for="category in filterTree(categories)" :key="category.id">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                <div class="d-flex align-items-center gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-link text-body p-0"
                                        :class="{ invisible: !category.children?.length }"
                                        @click="toggleExpand(category.id)"
                                    >
                                        <i class="bi bi-chevron-right" :class="{ 'rotate-90': isExpanded(category.id) }"></i>
                                    </button>
                                    <div>
                                        <p class="fw-medium mb-0">{{ category.name }}</p>
                                        <p class="small text-body-secondary mb-0">{{ category.slug }}</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 small">
                                    <Link :href="`/admin/categories/${category.id}/edit`" class="link-primary text-decoration-none">Edit</Link>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="deleteCategory(category.id)">Delete</button>
                                </div>
                            </div>

                            <div v-if="category.children?.length && isExpanded(category.id)" class="ms-4 mt-2 d-flex flex-column gap-2">
                                <div
                                    v-for="child in category.children"
                                    :key="child.id"
                                    class="d-flex justify-content-between align-items-center p-3 border rounded bg-body-tertiary"
                                >
                                    <div>
                                        <p class="fw-medium mb-0">{{ child.name }}</p>
                                        <p class="small text-body-secondary mb-0">{{ child.slug }}</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 small">
                                        <Link :href="`/admin/categories/${child.id}/edit`" class="link-primary text-decoration-none">Edit</Link>
                                        <button type="button" class="btn btn-link btn-sm text-danger p-0" @click="deleteCategory(child.id)">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-5">
                        <i class="bi bi-tags display-1 text-body-secondary"></i>
                        <p class="mt-2 text-body-secondary mb-0">No categories found.</p>
                        <p class="small text-body-secondary">Create your first category to get started.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.bi.rotate-90 { transform: rotate(90deg); transition: transform 0.15s ease; }
.bi { transition: transform 0.15s ease; }
</style>
