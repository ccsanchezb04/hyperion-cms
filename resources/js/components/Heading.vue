<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="mb-4">
        <h2 class="h4 fw-semibold mb-1">{{ title }}</h2>
        <p v-if="description" class="text-body-secondary small mb-0">
            {{ description }}
        </p>
    </div>
    <hr class="my-4" />
</template>
