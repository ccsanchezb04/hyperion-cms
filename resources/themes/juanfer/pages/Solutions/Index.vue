<script setup lang="ts">
import Footer from '../../components/Footer.vue';
import Hero from '../../components/Hero.vue';
import Navbar from '../../components/Navbar.vue';
import SiteHead from '../../components/SiteHead.vue';
import SolutionCard from '../../components/SolutionCard.vue';
import type { SiteSeoData, SiteSolution } from '../../composables/useSite';
import { useSite } from '../../composables/useSite';
import SiteLayout from '../../layouts/SiteLayout.vue';

defineProps<{
    solutions: SiteSolution[];
    seo: SiteSeoData;
}>();

const { setting } = useSite();
</script>

<template>
    <SiteHead :seo="seo" />
    <SiteLayout>
        <Hero />
        <Navbar />

        <section class="py-5">
            <div class="container">
                <h1 class="text-center my-4 jf-heading">
                    {{ setting('site.solutions.heading', 'Nuestras Soluciones') }}
                </h1>
                <div class="row">
                    <div v-for="s in solutions" :key="s.slug" class="col-lg-4 col-md-6 mb-4">
                        <SolutionCard
                            :image="s.image"
                            :title="s.title"
                            :description="s.body"
                            :href="s.href"
                            cta="Ver detalle"
                        />
                    </div>
                </div>
            </div>
        </section>

        <Footer />
    </SiteLayout>
</template>
