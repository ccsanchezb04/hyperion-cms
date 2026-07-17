<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Footer from '../../components/Footer.vue';
import Hero from '../../components/Hero.vue';
import Navbar from '../../components/Navbar.vue';
import SiteHead from '../../components/SiteHead.vue';
import type { SiteSeoData } from '../../composables/useSite';
import { useSite } from '../../composables/useSite';
import SiteLayout from '../../layouts/SiteLayout.vue';

const props = defineProps<{
    solution: {
        id: number;
        title: string;
        slug: string;
        body: string;
        embed_url: string | null;
        image: string | null;
        media: Array<{ url: string; alt: string }>;
        category: string | null;
        published_at: string | null;
    };
    seo: SiteSeoData;
}>();

const { setting } = useSite();

const embedSrc = computed((): string => {
    const url = props.solution.embed_url?.trim();
    if (!url) return '';
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}`;
    return url;
});

const categoryTagline = computed(() => {
    const cat = props.solution.category;
    if (!cat) return '';
    return setting(`site.seo.category.${cat}.description`, '');
});
</script>

<template>
    <SiteHead :seo="seo" />
    <SiteLayout>
        <Hero />
        <Navbar />

        <!-- Encabezado de categoría -->
        <section class="jf-dark-bg py-5">
            <div class="container">
                <div class="row align-items-center gy-3">
                    <div class="col-12 col-lg-8">
                        <h1 class="jf-heading mb-2">{{ solution.title }}</h1>
                        <p v-if="categoryTagline" class="text-white-50 mb-0 fs-5">{{ categoryTagline }}</p>
                    </div>
                    <div class="col-12 col-lg-4 d-flex gap-2 justify-content-lg-end flex-wrap">
                        <button class="jf-btn">Cotiza ya</button>
                        <Link href="/soluciones" class="jf-btn jf-btn--secondary">&#8592; Volver</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Portafolio de productos -->
        <section class="py-5" style="background: var(--jf-bg-muted)">
            <div class="container">
                <div class="jf-solution-body" v-html="solution.body" />
                <div v-if="embedSrc" class="ratio ratio-16x9 mt-4 rounded overflow-hidden shadow-sm">
                    <iframe
                        :src="embedSrc"
                        allowfullscreen
                        loading="lazy"
                        :title="solution.title"
                        style="border: 0;"
                    />
                </div>
            </div>
        </section>

        <Footer />
    </SiteLayout>
</template>

<style scoped>
.jf-solution-body :deep(.jf-portfolio) {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.75rem;
}

.jf-solution-body :deep(.jf-portfolio__insurer) {
    background: #fff;
    border-radius: 10px;
    padding: 1.5rem 1.5rem 1rem;
    border-top: 4px solid var(--jf-secondary);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.jf-solution-body :deep(.jf-portfolio__insurer-name) {
    font-family: var(--jf-font-heading);
    color: var(--jf-primary);
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.jf-solution-body :deep(.jf-portfolio__products) {
    list-style: none;
    padding: 0;
    margin: 0;
}

.jf-solution-body :deep(.jf-portfolio__products li) {
    padding: 0.45rem 0;
    border-bottom: 1px solid var(--jf-border);
    color: #495057;
    font-size: 0.93rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.jf-solution-body :deep(.jf-portfolio__products li::before) {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--jf-secondary);
    flex-shrink: 0;
}

.jf-solution-body :deep(.jf-portfolio__products li:last-child) {
    border-bottom: none;
}

.jf-solution-body :deep(.jf-portfolio__products--cols) {
    column-count: 2;
    column-gap: 1.5rem;
}

@media (max-width: 575px) {
    .jf-solution-body :deep(.jf-portfolio__products--cols) {
        column-count: 1;
    }
}
</style>
