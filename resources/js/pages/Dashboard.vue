<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface Statistics {
    contents: {
        total: number;
        published: number;
        draft: number;
    };
    media: {
        total: number;
        images: number;
        videos: number;
    };
    categories: {
        total: number;
    };
    users: {
        total: number;
        active: number;
    };
}

const statistics = ref<Statistics>({
    contents: { total: 0, published: 0, draft: 0 },
    media: { total: 0, images: 0, videos: 0 },
    categories: { total: 0 },
    users: { total: 0, active: 0 },
});

const loading = ref(true);

const fetchStatistics = async () => {
    loading.value = true;
    try {
        // Fetch contents statistics
        const contentsResponse = await fetch('/api/v1/contents?per_page=1', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const contentsData = await contentsResponse.json();
        statistics.value.contents.total = contentsData.meta.total;

        // Fetch published contents
        const publishedResponse = await fetch('/api/v1/contents?status=published&per_page=1', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const publishedData = await publishedResponse.json();
        statistics.value.contents.published = publishedData.meta.total;

        // Fetch draft contents
        const draftResponse = await fetch('/api/v1/contents?status=draft&per_page=1', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const draftData = await draftResponse.json();
        statistics.value.contents.draft = draftData.meta.total;

        // Fetch media statistics
        const mediaResponse = await fetch('/api/v1/media?per_page=1', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const mediaData = await mediaResponse.json();
        statistics.value.media.total = mediaData.meta.total;

        // Fetch images
        const imagesResponse = await fetch('/api/v1/media?type=image&per_page=1', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const imagesData = await imagesResponse.json();
        statistics.value.media.images = imagesData.meta.total;

        // Fetch videos
        const videosResponse = await fetch('/api/v1/media?type=video&per_page=1', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const videosData = await videosResponse.json();
        statistics.value.media.videos = videosData.meta.total;

        // Fetch categories
        const categoriesResponse = await fetch('/api/v1/categories', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const categoriesData = await categoriesResponse.json();
        statistics.value.categories.total = categoriesData.data.length;

        // Fetch users statistics
        const usersResponse = await fetch('/api/v1/users/statistics', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
        });
        const usersData = await usersResponse.json();
        statistics.value.users = usersData.data;

    } catch (error) {
        console.error('Error fetching statistics:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStatistics();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Welcome to Hyperion CMS</h1>
                    <p class="mt-2 text-gray-600">Manage your content, media, and settings from here.</p>
                </div>

                <!-- Statistics Cards -->
                <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Contents Stats -->
                    <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Contents</p>
                                <p class="text-2xl font-bold text-gray-900">{{ statistics.contents.total }}</p>
                            </div>
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-green-600">{{ statistics.contents.published }} published</span>
                            <span class="text-yellow-600">{{ statistics.contents.draft }} draft</span>
                        </div>
                    </div>

                    <!-- Media Stats -->
                    <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Media</p>
                                <p class="text-2xl font-bold text-gray-900">{{ statistics.media.total }}</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-blue-600">{{ statistics.media.images }} images</span>
                            <span class="text-red-600">{{ statistics.media.videos }} videos</span>
                        </div>
                    </div>

                    <!-- Categories Stats -->
                    <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Categories</p>
                                <p class="text-2xl font-bold text-gray-900">{{ statistics.categories.total }}</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-gray-500">
                            Hierarchical structure
                        </div>
                    </div>

                    <!-- Users Stats -->
                    <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900">{{ statistics.users.total }}</p>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-lg">
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-green-600">
                            {{ statistics.users.active }} active
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>

                <!-- Quick Actions -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <Link
                        href="/admin/contents"
                        class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200"
                    >
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Contents</h3>
                                <p class="text-sm text-gray-500">Manage your content</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        href="/admin/media"
                        class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200"
                    >
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-lg">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Media Library</h3>
                                <p class="text-sm text-gray-500">Upload and manage files</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        href="/admin/categories"
                        class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200"
                    >
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Categories</h3>
                                <p class="text-sm text-gray-500">Organize your content</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        href="/admin/settings"
                        class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200"
                    >
                        <div class="flex items-center">
                            <div class="p-3 bg-orange-100 rounded-lg">
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Settings</h3>
                                <p class="text-sm text-gray-500">Configure your site</p>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">System Status</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Database Connected</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">API Running</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Storage Configured</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-600">Cache Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
