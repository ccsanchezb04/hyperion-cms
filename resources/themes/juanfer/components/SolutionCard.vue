<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    image: string | null;
    title: string;
    description: string;
    href?: string;
    cta?: string;
}>();
</script>

<template>
    <div class="card h-100">
        <img v-if="image" :src="image" class="card-img-top" :alt="title" />
        <div class="card-body">
            <h5 class="card-title">{{ title }}</h5>
            <p class="card-text">{{ description }}</p>
            <Link v-if="href" :href="href" class="jf-btn float-end">{{ cta ?? 'Cotiza ya' }}</Link>
            <button v-else class="jf-btn float-end">{{ cta ?? 'Cotiza ya' }}</button>
        </div>
    </div>
</template>
