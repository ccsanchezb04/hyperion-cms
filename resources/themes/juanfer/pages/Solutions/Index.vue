<script setup lang="ts">
import Footer from '../../components/Footer.vue';
import Hero from '../../components/Hero.vue';
import Navbar from '../../components/Navbar.vue';
import SiteHead from '../../components/SiteHead.vue';
import SolutionCategoryCard from '../../components/SolutionCategoryCard.vue';
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

        <section class="bg-white py-5">
            <div class="container">
                <h1 class="text-center mb-5 jf-heading">
                    {{ setting('site.solutions.heading', 'Nuestras Soluciones') }}
                </h1>
                <div class="row g-4">
                    <div v-for="s in solutions" :key="s.slug" class="col-12 col-sm-6 col-lg-4">
                        <SolutionCategoryCard
                            :title="s.title"
                            :category="s.category"
                            :href="s.href"
                            :image="s.image"
                        />
                    </div>
                </div>
            </div>
        </section>

        <Footer />
    </SiteLayout>
</template>
