<script setup lang="ts">
import type { SiteTestimonial } from '../composables/useSite';
import { useSite } from '../composables/useSite';
import Testimonial from './Testimonial.vue';

defineProps<{
    items: SiteTestimonial[];
}>();

const { setting } = useSite();
</script>

<template>
    <section id="testimonios" class="py-5">
        <div class="container">
            <div class="row">
                <h2 class="text-center my-4 jf-heading">
                    {{ setting('site.testimonials.heading', 'Nuestros clientes han confiado en nosotros') }}
                </h2>
                <div v-for="(t, idx) in items" :key="`${t.name}-${idx}`" class="col-lg-4 mb-4">
                    <Testimonial :name="t.name" :quote="t.quote" />
                </div>
            </div>
        </div>
    </section>
</template>
