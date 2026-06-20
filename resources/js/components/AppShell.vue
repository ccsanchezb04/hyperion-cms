<script setup lang="ts">
interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();
</script>

<template>
    <div v-if="variant === 'header'" class="d-flex flex-column min-vh-100">
        <slot />
    </div>
    <div v-else class="d-flex min-vh-100 app-shell">
        <slot />
    </div>
</template>

<style scoped>
.app-shell {
    background-color: var(--bs-body-bg);
}
</style>
