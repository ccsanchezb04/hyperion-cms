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
.jf-testimonial-body :deep(p) {
    margin-bottom: 0.5rem;
}
.jf-testimonial-body :deep(p:last-child) {
    margin-bottom: 0;
}
</style>
