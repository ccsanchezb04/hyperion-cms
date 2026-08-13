<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Footer from '../../components/Footer.vue';
import Hero from '../../components/Hero.vue';
import Navbar from '../../components/Navbar.vue';
import SiteHead from '../../components/SiteHead.vue';
import { useEmbed } from '../../composables/useEmbed';
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

const embedUrlRef = computed(() => props.solution.embed_url);
const { embedSrc, isLinkedIn, externalUrl } = useEmbed(embedUrlRef);

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
                <div v-else-if="isLinkedIn" class="mt-4 text-center">
                    <a
                        :href="externalUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="jf-btn"
                    >
                        Ver en LinkedIn &rarr;
                    </a>
                </div>
            </div>
        </section>

        <Footer />
    </SiteLayout>
</template>
