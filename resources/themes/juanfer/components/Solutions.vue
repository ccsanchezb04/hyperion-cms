<script setup lang="ts">
import type { SiteSolution } from '../composables/useSite';
import { useSite } from '../composables/useSite';
import SolutionCategoryCard from './SolutionCategoryCard.vue';

defineProps<{
    items: SiteSolution[];
}>();

const { setting } = useSite();
</script>

<template>
    <section id="soluciones" class="bg-white py-5">
        <div class="container">
            <h2 class="text-center mb-5 jf-heading">
                {{ setting('site.solutions.heading', 'Nuestras Soluciones') }}
            </h2>
            <div class="row g-4">
                <div v-for="s in items" :key="s.slug" class="col-12 col-sm-6 col-lg-4">
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
</template>
