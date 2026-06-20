<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SiteHead from '../components/SiteHead.vue';
import SiteLayout from '../layouts/SiteLayout.vue';

interface Solution {
    id: number;
    title: string;
    slug: string;
    body: string;
    image: string | null;
    href: string;
}

interface Testimonial { name: string; quote: string }

defineProps<{
    solutions: Solution[];
    testimonials: Testimonial[];
    seo: any;
}>();
</script>

<template>
    <SiteHead :seo="seo" />
    <SiteLayout>
        <section id="soluciones" class="df-section">
            <div class="df-container">
                <h2>Soluciones</h2>
                <div class="df-grid">
                    <article v-for="s in solutions" :key="s.slug" class="df-card">
                        <img v-if="s.image" :src="s.image" :alt="s.title" />
                        <div class="df-card-body">
                            <h3>{{ s.title }}</h3>
                            <p>{{ s.body }}</p>
                            <Link :href="s.href" class="df-btn">Ver detalle</Link>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="testimonios" class="df-section" style="background: var(--df-surface);">
            <div class="df-container">
                <h2>Testimonios</h2>
                <div class="df-grid">
                    <article v-for="(t, idx) in testimonials" :key="idx" class="df-card">
                        <div class="df-card-body">
                            <h3>{{ t.name }}</h3>
                            <p>&ldquo;{{ t.quote }}&rdquo;</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </SiteLayout>
</template>
