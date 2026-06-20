<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Footer from '../../components/Footer.vue';
import Hero from '../../components/Hero.vue';
import Navbar from '../../components/Navbar.vue';
import SiteHead from '../../components/SiteHead.vue';
import type { SiteSeoData } from '../../composables/useSite';
import SiteLayout from '../../layouts/SiteLayout.vue';

defineProps<{
    solution: {
        id: number;
        title: string;
        slug: string;
        body: string;
        image: string | null;
        category: string | null;
        published_at: string | null;
    };
    seo: SiteSeoData;
}>();
</script>

<template>
    <SiteHead :seo="seo" />
    <SiteLayout>
        <Hero />
        <Navbar />

        <section class="py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div v-if="solution.image" class="col-lg-6 mb-4">
                        <img :src="solution.image" :alt="solution.title" class="img-fluid rounded shadow-sm" />
                    </div>
                    <div :class="solution.image ? 'col-lg-6' : 'col-lg-12'">
                        <h1 class="jf-heading mb-3">{{ solution.title }}</h1>
                        <p class="lead">{{ solution.body }}</p>
                        <div class="mt-4 d-flex gap-2">
                            <button class="jf-btn">Cotiza ya</button>
                            <Link href="/soluciones" class="jf-btn jf-btn--secondary">Volver a soluciones</Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <Footer />
    </SiteLayout>
</template>
