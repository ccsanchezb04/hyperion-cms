<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CategoriesTab from './Tabs/Categories.vue';
import ChromeTab from './Tabs/Chrome.vue';
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

export interface SeoI18n {
    active: string;
    default: string;
    supported: string[];
}

export interface SolutionCategory {
    slug: string;
    name: string;
}

const props = defineProps<{
    settings: SeoSettings;
    meta: SeoMeta;
    i18n: SeoI18n;
    solutionCategories: SolutionCategory[];
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
    { id: 'categories', label: 'Categorías OG (i18n)', icon: 'tags-fill' },
    { id: 'chrome', label: 'Chrome (i18n)', icon: 'translate' },
];

const activeTab = ref<string>('general');

const localeLabels: Record<string, string> = {
    es: '🇪🇸 Español',
    en: '🇬🇧 English',
};

const switchLang = (lang: string) => {
    if (lang === props.i18n.active) return;
    const params = lang === props.i18n.default ? {} : { lang };
    router.get('/admin/seo', params, { preserveScroll: true });
};
</script>

<template>
    <Head title="SEO" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container-xl py-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div>
                            <h1 class="h4 fw-bold mb-1">SEO</h1>
                            <p class="text-body-secondary small mb-0">
                                Configuración de SEO público del sitio.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-body-secondary small">Editando idioma:</span>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Idioma">
                                <button
                                    v-for="lang in i18n.supported"
                                    :key="lang"
                                    type="button"
                                    class="btn"
                                    :class="lang === i18n.active ? 'btn-primary' : 'btn-outline-secondary'"
                                    @click="switchLang(lang)"
                                >
                                    {{ localeLabels[lang] ?? lang.toUpperCase() }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="i18n.active !== i18n.default" class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Editando traducciones <strong>{{ localeLabels[i18n.active] }}</strong>.
                        Los campos vacíos caen al valor por defecto (Español).
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
                            <GeneralTab v-if="activeTab === 'general'" :settings="settings" :active-lang="i18n.active" />
                            <OpenGraphTab v-else-if="activeTab === 'og'" :settings="settings" :active-lang="i18n.active" />
                            <SchemaTab v-else-if="activeTab === 'schema'" :settings="settings" :active-lang="i18n.active" />
                            <SitemapTab v-else-if="activeTab === 'sitemap'" :settings="settings" :meta="meta" />
                            <RobotsTab v-else-if="activeTab === 'robots'" :settings="settings" :meta="meta" :active-lang="i18n.active" />
                            <IntegrationsTab v-else-if="activeTab === 'integrations'" :settings="settings" :active-lang="i18n.active" />
                            <CategoriesTab v-else-if="activeTab === 'categories'" :settings="settings" :active-lang="i18n.active" :categories="solutionCategories" />
                            <ChromeTab v-else-if="activeTab === 'chrome'" :settings="settings" :active-lang="i18n.active" />
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
