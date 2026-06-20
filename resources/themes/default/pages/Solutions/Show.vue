<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SiteHead from '../../components/SiteHead.vue';
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
    seo: any;
}>();
</script>

<template>
    <SiteHead :seo="seo" />
    <SiteLayout>
        <section class="df-section">
            <div class="df-container">
                <Link href="/soluciones" style="display: inline-block; margin-bottom: 1rem;">
                    ← Volver a soluciones
                </Link>
                <article>
                    <h2>{{ solution.title }}</h2>
                    <img
                        v-if="solution.image"
                        :src="solution.image"
                        :alt="solution.title"
                        style="max-width: 100%; border-radius: var(--df-radius); margin-bottom: 1rem;"
                    />
                    <p>{{ solution.body }}</p>
                </article>
            </div>
        </section>
    </SiteLayout>
</template>
