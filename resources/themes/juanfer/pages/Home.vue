<script setup lang="ts">
import About from '../components/About.vue';
import Allies from '../components/Allies.vue';
import Carousel from '../components/Carousel.vue';
import ContactForm from '../components/ContactForm.vue';
import Footer from '../components/Footer.vue';
import Hero from '../components/Hero.vue';
import Navbar from '../components/Navbar.vue';
import SiteHead from '../components/SiteHead.vue';
import Solutions from '../components/Solutions.vue';
import Testimonials from '../components/Testimonials.vue';
import type {
    SiteAlly,
    SiteCarouselSlide,
    SiteSeoData,
    SiteSolution,
    SiteTestimonial,
} from '../composables/useSite';
import SiteLayout from '../layouts/SiteLayout.vue';

defineProps<{
    solutions: SiteSolution[];
    testimonials: SiteTestimonial[];
    carousel: SiteCarouselSlide[];
    allies: SiteAlly[];
    seo: SiteSeoData;
}>();
</script>

<template>
    <SiteHead :seo="seo" />
    <SiteLayout>
        <Hero />
        <Navbar />
        <Carousel :slides="carousel" />
        <Solutions :items="solutions" />
        <About />
        <Testimonials :items="testimonials" />
        <Allies :items="allies" />
        <ContactForm />
        <Footer />
    </SiteLayout>
</template>
