<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <header class="mb-3">
        <h3 class="h6 fw-medium mb-1">{{ title }}</h3>
        <p v-if="description" class="text-body-secondary small mb-0">
            {{ description }}
        </p>
    </header>
</template>
