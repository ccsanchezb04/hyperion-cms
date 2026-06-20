<script setup lang="ts">
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Statistics {
    contents: { total: number; published: number; draft: number };
    media: { total: number; images: number; videos: number };
    categories: { total: number };
    users: { total: number; active: number };
}

defineProps<{
    statistics: Statistics;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/admin/dashboard' }];

const { can } = usePermissions();

interface QuickAction {
    href: string;
    icon: string;
    title: string;
    description: string;
    permission?: string;
}

const allQuickActions: QuickAction[] = [
    { href: '/admin/contents', icon: 'file-earmark-text-fill', title: 'Contents', description: 'Create, edit and publish your content', permission: 'view-content' },
    { href: '/admin/categories', icon: 'tags-fill', title: 'Categories', description: 'Organize content into a hierarchy', permission: 'view-category' },
    { href: '/admin/media', icon: 'images', title: 'Media Library', description: 'Upload and manage files', permission: 'view-media' },
    { href: '/admin/menus', icon: 'list-nested', title: 'Menus', description: 'Build site navigation menus', permission: 'view-menu' },
    { href: '/admin/settings', icon: 'gear-fill', title: 'Settings', description: 'Configure your site', permission: 'view-settings' },
];

const quickActions = computed(() => allQuickActions.filter((a) => can(a.permission)));
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="mb-4">
                <h1 class="h2 fw-bold mb-1">Welcome to Hyperion CMS</h1>
                <p class="text-body-secondary mb-0">Manage your content, media, and settings from here.</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="small text-body-secondary mb-1">Total Contents</p>
                                    <p class="h3 fw-bold mb-0">{{ statistics.contents.total }}</p>
                                </div>
                                <i class="bi bi-file-earmark-text-fill display-6 text-primary"></i>
                            </div>
                            <div class="d-flex justify-content-between small mt-3">
                                <span class="text-success">{{ statistics.contents.published }} published</span>
                                <span class="text-warning">{{ statistics.contents.draft }} draft</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="small text-body-secondary mb-1">Total Media</p>
                                    <p class="h3 fw-bold mb-0">{{ statistics.media.total }}</p>
                                </div>
                                <i class="bi bi-images display-6 text-primary"></i>
                            </div>
                            <div class="d-flex justify-content-between small mt-3">
                                <span class="text-info">{{ statistics.media.images }} images</span>
                                <span class="text-danger">{{ statistics.media.videos }} videos</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="small text-body-secondary mb-1">Categories</p>
                                    <p class="h3 fw-bold mb-0">{{ statistics.categories.total }}</p>
                                </div>
                                <i class="bi bi-tags-fill display-6 text-primary"></i>
                            </div>
                            <p class="small text-body-secondary mb-0 mt-3">Hierarchical structure</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="small text-body-secondary mb-1">Total Users</p>
                                    <p class="h3 fw-bold mb-0">{{ statistics.users.total }}</p>
                                </div>
                                <i class="bi bi-people-fill display-6 text-primary"></i>
                            </div>
                            <p class="small text-success mb-0 mt-3">{{ statistics.users.active }} active</p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 v-if="quickActions.length" class="h5 fw-bold mb-3">Modules</h2>
            <div v-if="quickActions.length" class="row g-3 mb-4">
                <div v-for="action in quickActions" :key="action.href" class="col-12 col-md-6 col-lg-4">
                    <Link :href="action.href" class="card shadow-sm h-100 text-decoration-none text-body link-action">
                        <div class="card-body d-flex align-items-start gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary flex-shrink-0" style="width: 2.75rem; height: 2.75rem;">
                                <i :class="`bi bi-${action.icon} fs-4`"></i>
                            </span>
                            <div>
                                <h3 class="h6 fw-semibold mb-1">{{ action.title }}</h3>
                                <p class="small text-body-secondary mb-0">{{ action.description }}</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-3">System Status</h2>
                    <div class="row g-3">
                        <div v-for="status in ['Database Connected', 'API Running', 'Storage Configured', 'Cache Active']" :key="status" class="col-12 col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle bg-success" style="width: 0.75rem; height: 0.75rem;"></span>
                                <span class="small text-body-secondary">{{ status }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.link-action {
    transition: box-shadow 0.15s ease, transform 0.15s ease;
}
.link-action:hover {
    box-shadow: var(--bs-box-shadow);
    transform: translateY(-2px);
}
</style>
