<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SiteHead from '../../components/SiteHead.vue';
import SiteLayout from '../../layouts/SiteLayout.vue';

interface Solution {
    id: number;
    title: string;
    slug: string;
    body: string;
    image: string | null;
    href: string;
}

defineProps<{ solutions: Solution[]; seo: any }>();
</script>

<template>
    <SiteHead :seo="seo" />
    <SiteLayout>
        <section class="df-section">
            <div class="df-container">
                <h2>Todas las soluciones</h2>
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
    </SiteLayout>
</template>
