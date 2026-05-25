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
        title: 'Contents',
        href: '/admin/contents',
    },
];

interface Content {
    id: number;
    title: string;
    slug: string;
    type: string;
    status: string;
    published_at: string | null;
    created_at: string;
    author: {
        name: string;
    };
}

const contents = ref<Content[]>([]);
const loading = ref(true);
const filters = ref({
    type: '',
    status: '',
    search: '',
});

const statusColors = {
    draft: 'bg-yellow-100 text-yellow-800',
    published: 'bg-green-100 text-green-800',
    archived: 'bg-gray-100 text-gray-800',
};

const typeLabels = {
    post: 'Post',
    page: 'Page',
    custom: 'Custom',
};

const statusLabels = {
    draft: 'Draft',
    published: 'Published',
    archived: 'Archived',
};

const fetchContents = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/v1/contents?' + new URLSearchParams({
            ...(filters.value.type && { type: filters.value.type }),
            ...(filters.value.status && { status: filters.value.status }),
            ...(filters.value.search && { search: filters.value.search }),
        }), {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        contents.value = data.data;
    } catch (error) {
        console.error('Error fetching contents:', error);
    } finally {
        loading.value = false;
    }
};

const deleteContent = (id: number) => {
    if (confirm('Are you sure you want to delete this content?')) {
        router.delete(`/api/v1/contents/${id}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
            onSuccess: () => {
                fetchContents();
            },
        });
    }
};

const publishContent = (id: number) => {
    router.post(`/api/v1/contents/${id}/publish`, {}, {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        onSuccess: () => {
            fetchContents();
        },
    });
};

const archiveContent = (id: number) => {
    router.post(`/api/v1/contents/${id}/archive`, {}, {
        headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        onSuccess: () => {
            fetchContents();
        },
    });
};

onMounted(() => {
    fetchContents();
});
</script>

<template>
    <Head title="Contents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold text-gray-900">Contents</h1>
                            <Link
                                href="/admin/contents/create"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Create Content
                            </Link>
                        </div>

                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search contents..."
                                class="border border-gray-300 rounded-md px-4 py-2"
                                @keyup.enter="fetchContents"
                            />
                            <select
                                v-model="filters.type"
                                class="border border-gray-300 rounded-md px-4 py-2"
                                @change="fetchContents"
                            >
                                <option value="">All Types</option>
                                <option value="post">Post</option>
                                <option value="page">Page</option>
                                <option value="custom">Custom</option>
                            </select>
                            <select
                                v-model="filters.status"
                                class="border border-gray-300 rounded-md px-4 py-2"
                                @change="fetchContents"
                            >
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>

                        <!-- Contents Table -->
                        <div v-else-if="contents.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Title
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Author
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="content in contents" :key="content.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ content.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ content.slug }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ typeLabels[content.type] || content.type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColors[content.status]}`">
                                                {{ statusLabels[content.status] || content.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ content.author?.name || 'Unknown' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(content.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link
                                                    :href="`/admin/contents/${content.id}/edit`"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    v-if="content.status === 'draft'"
                                                    @click="publishContent(content.id)"
                                                    class="text-green-600 hover:text-green-900"
                                                >
                                                    Publish
                                                </button>
                                                <button
                                                    v-if="content.status === 'published'"
                                                    @click="archiveContent(content.id)"
                                                    class="text-yellow-600 hover:text-yellow-900"
                                                >
                                                    Archive
                                                </button>
                                                <button
                                                    @click="deleteContent(content.id)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500">No contents found.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
