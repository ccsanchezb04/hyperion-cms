<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import GeneralTab from './Tabs/General.vue';
import IntegrationsTab from './Tabs/Integrations.vue';
import OpenGraphTab from './Tabs/OpenGraph.vue';
import RobotsTab from './Tabs/Robots.vue';
import SchemaTab from './Tabs/Schema.vue';
import SitemapTab from './Tabs/Sitemap.vue';

export interface SeoSettings {
    seo: Record<string, string>;
    organization: Record<string, string>;
    integrations: Record<string, string>;
    site: Record<string, string>;
}

export interface SeoMeta {
    sitemap_url_count: number;
    sitemap_xml: string;
    robots_txt: string;
}

defineProps<{
    settings: SeoSettings;
    meta: SeoMeta;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'SEO', href: '/admin/seo' },
];

const tabs = [
    { id: 'general', label: 'General', icon: 'gear-fill' },
    { id: 'og', label: 'Open Graph & Social', icon: 'share-fill' },
    { id: 'schema', label: 'Schema.org / Organization', icon: 'building' },
    { id: 'sitemap', label: 'Sitemap', icon: 'diagram-3-fill' },
    { id: 'robots', label: 'Robots.txt', icon: 'robot' },
    { id: 'integrations', label: 'Integraciones', icon: 'plug-fill' },
];

const activeTab = ref<string>('general');
</script>

<template>
    <Head title="SEO" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="h4 fw-bold mb-1">SEO</h1>
                            <p class="text-body-secondary small mb-0">
                                Configuración de SEO público del sitio.
                            </p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <aside class="col-md-3">
                            <nav class="nav flex-column nav-pills gap-1">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    type="button"
                                    class="nav-link text-start d-inline-flex align-items-center gap-2"
                                    :class="{ active: activeTab === tab.id }"
                                    @click="activeTab = tab.id"
                                >
                                    <i :class="`bi bi-${tab.icon}`" aria-hidden="true"></i>
                                    <span>{{ tab.label }}</span>
                                </button>
                            </nav>
                        </aside>

                        <section class="col-md-9">
                            <GeneralTab v-if="activeTab === 'general'" :settings="settings" />
                            <OpenGraphTab v-else-if="activeTab === 'og'" :settings="settings" />
                            <SchemaTab v-else-if="activeTab === 'schema'" :settings="settings" />
                            <SitemapTab v-else-if="activeTab === 'sitemap'" :settings="settings" :meta="meta" />
                            <RobotsTab v-else-if="activeTab === 'robots'" :settings="settings" :meta="meta" />
                            <IntegrationsTab v-else-if="activeTab === 'integrations'" :settings="settings" />
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
