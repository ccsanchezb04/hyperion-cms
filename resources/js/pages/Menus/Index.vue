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
        title: 'Menus',
        href: '/admin/menus',
    },
];

interface MenuItem {
    id: number;
    title: string;
    type: string;
    link: string;
    parent_id: number | null;
    order: number;
    css_class: string;
    target: string;
    enabled: boolean;
    children?: MenuItem[];
}

interface Menu {
    id: number;
    name: string;
    slug: string;
    items: MenuItem[];
}

const menus = ref<Menu[]>([]);
const loading = ref(true);
const selectedMenu = ref<Menu | null>(null);
const showCreateModal = ref(false);
const showCreateItemModal = ref(false);
const newMenuName = ref('');
const newMenuSlug = ref('');
const newItem = ref({
    title: '',
    type: 'url',
    link: '',
    parent_id: null as number | null,
    order: 0,
    css_class: '',
    target: '_self',
    enabled: true,
});

const fetchMenus = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/v1/menus', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        menus.value = data.data;
        if (menus.value.length > 0) {
            selectedMenu.value = menus.value[0];
        }
    } catch (error) {
        console.error('Error fetching menus:', error);
    } finally {
        loading.value = false;
    }
};

const selectMenu = (menu: Menu) => {
    selectedMenu.value = menu;
};

const createMenu = async () => {
    try {
        const response = await fetch('/api/v1/menus', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: newMenuName.value,
                slug: newMenuSlug.value,
            }),
        });

        if (response.ok) {
            showCreateModal.value = false;
            newMenuName.value = '';
            newMenuSlug.value = '';
            fetchMenus();
        } else {
            alert('Error creating menu');
        }
    } catch (error) {
        console.error('Error creating menu:', error);
        alert('Error creating menu');
    }
};

const createMenuItem = async () => {
    if (!selectedMenu.value) return;

    try {
        const response = await fetch(`/api/v1/menus/${selectedMenu.value.id}/items`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(newItem.value),
        });

        if (response.ok) {
            showCreateItemModal.value = false;
            newItem.value = {
                title: '',
                type: 'url',
                link: '',
                parent_id: null,
                order: 0,
                css_class: '',
                target: '_self',
                enabled: true,
            };
            fetchMenus();
        } else {
            alert('Error creating menu item');
        }
    } catch (error) {
        console.error('Error creating menu item:', error);
        alert('Error creating menu item');
    }
};

const deleteMenuItem = async (itemId: number) => {
    if (confirm('Are you sure you want to delete this menu item?')) {
        try {
            const response = await fetch(`/api/v1/menus/items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                fetchMenus();
            } else {
                alert('Error deleting menu item');
            }
        } catch (error) {
            console.error('Error deleting menu item:', error);
            alert('Error deleting menu item');
        }
    }
};

const reorderItems = async (items: any[]) => {
    if (!selectedMenu.value) return;

    try {
        const response = await fetch(`/api/v1/menus/${selectedMenu.value.id}/items/reorder`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ items }),
        });

        if (!response.ok) {
            alert('Error reordering menu items');
        }
    } catch (error) {
        console.error('Error reordering menu items:', error);
        alert('Error reordering menu items');
    }
};

onMounted(() => {
    fetchMenus();
});
</script>

<template>
    <Head title="Menus" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold text-gray-900">Menus</h1>
                            <button
                                @click="showCreateModal = true"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Create Menu
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>

                        <!-- Menu Selection -->
                        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Menu List -->
                            <div class="lg:col-span-1">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Select Menu</h2>
                                <div class="space-y-2">
                                    <div
                                        v-for="menu in menus"
                                        :key="menu.id"
                                        @click="selectMenu(menu)"
                                        :class="[
                                            selectedMenu?.id === menu.id
                                                ? 'bg-indigo-50 border-indigo-500'
                                                : 'bg-white border-gray-200 hover:border-indigo-300',
                                            'p-4 border rounded-lg cursor-pointer transition-colors'
                                        ]"
                                    >
                                        <p class="font-medium text-gray-900">{{ menu.name }}</p>
                                        <p class="text-sm text-gray-500">{{ menu.slug }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Menu Items -->
                            <div class="lg:col-span-2">
                                <div v-if="selectedMenu" class="flex justify-between items-center mb-4">
                                    <h2 class="text-lg font-medium text-gray-900">
                                        {{ selectedMenu.name }} Items
                                    </h2>
                                    <button
                                        @click="showCreateItemModal = true"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Add Item
                                    </button>
                                </div>

                                <div v-if="selectedMenu.items && selectedMenu.items.length > 0" class="space-y-2">
                                    <div
                                        v-for="item in selectedMenu.items"
                                        :key="item.id"
                                        class="p-4 bg-gray-50 border border-gray-200 rounded-lg hover:shadow-md transition-shadow"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ item.title }}</p>
                                                <p class="text-sm text-gray-500">{{ item.link }}</p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span :class="[
                                                    'px-2 py-1 text-xs rounded',
                                                    item.enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                                ]">
                                                    {{ item.enabled ? 'Enabled' : 'Disabled' }}
                                                </span>
                                                <button
                                                    @click="deleteMenuItem(item.id)"
                                                    class="text-red-600 hover:text-red-800"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-8 text-gray-500">
                                    <p>No items in this menu.</p>
                                    <button
                                        @click="showCreateItemModal = true"
                                        class="text-indigo-600 hover:text-indigo-800"
                                    >
                                        Add your first item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Menu Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Create Menu</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input
                            v-model="newMenuName"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-4 py-2"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                        <input
                            v-model="newMenuSlug"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-4 py-2"
                        />
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button
                        @click="showCreateModal = false"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                    >
                        Cancel
                    </button>
                    <button
                        @click="createMenu"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Create
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Menu Item Modal -->
        <div v-if="showCreateItemModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Add Menu Item</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input
                            v-model="newItem.title"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-4 py-2"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select
                            v-model="newItem.type"
                            class="w-full border border-gray-300 rounded-md px-4 py-2"
                        >
                            <option value="url">URL</option>
                            <option value="route">Route</option>
                            <option value="separator">Separator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Link</label>
                        <input
                            v-model="newItem.link"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-4 py-2"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">CSS Class</label>
                        <input
                            v-model="newItem.css_class"
                            type="text"
                            class="w-full border border-gray-300 rounded-md px-4 py-2"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target</label>
                        <select
                            v-model="newItem.target"
                            class="w-full border border-gray-300 rounded-md px-4 py-2"
                        >
                            <option value="_self">Same Window</option>
                            <option value="_blank">New Window</option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input
                            v-model="newItem.enabled"
                            type="checkbox"
                            class="mr-2"
                        />
                        <label class="text-sm font-medium text-gray-700">Enabled</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-4 mt-6">
                    <button
                        @click="showCreateItemModal = false"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                    >
                        Cancel
                    </button>
                    <button
                        @click="createMenuItem"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Add Item
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>