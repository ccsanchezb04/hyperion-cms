<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { SiteSolution } from '../composables/useSite';
import { useSite } from '../composables/useSite';
import SolutionCard from './SolutionCard.vue';

defineProps<{
    items: SiteSolution[];
}>();

const { setting } = useSite();
</script>

<template>
    <section id="soluciones" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center my-4 jf-heading">
                        {{ setting('site.solutions.heading', 'Nuestras Soluciones') }}
                    </h2>
                </div>
            </div>
            <div class="row">
                <div v-for="s in items" :key="s.slug" class="col-lg-4 mb-4">
                    <SolutionCard
                        :image="s.image"
                        :title="s.title"
                        :description="s.body"
                        :href="s.href"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <Link href="/soluciones" class="jf-btn jf-btn--secondary float-end my-4">
                        {{ setting('site.solutions.cta_all', 'Ver todas las soluciones') }}
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
