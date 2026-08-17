<script setup lang="ts">
defineProps<{
    name: string;
    quote: string;
}>();
</script>

<template>
    <div class="card h-100">
        <div class="card-body">
            <h5 class="card-title">{{ name }}</h5>
            <div class="card-text jf-testimonial-body" v-html="quote" />
        </div>
    </div>
</template>

<style scoped>
.jf-testimonial-body {
    position: relative;
    max-height: 9.5rem; /* ~330 chars a ~16px */
    overflow: hidden;
    transition: max-height 0.35s ease;
}

.jf-testimonial-body::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2.5rem;
    background: linear-gradient(transparent, var(--bs-card-bg, #fff));
    pointer-events: none;
    transition: opacity 0.25s ease;
}

.card:hover .jf-testimonial-body {
    max-height: 100rem;
}

.card:hover .jf-testimonial-body::after {
    opacity: 0;
}

.jf-testimonial-body :deep(p) {
    margin-bottom: 0.5rem;
}
.jf-testimonial-body :deep(p:last-child) {
    margin-bottom: 0;
}
</style>
