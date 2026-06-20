<script setup lang="ts">
interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <main
        v-if="variant === 'sidebar'"
        class="flex-grow-1 d-flex flex-column min-w-0"
        :class="$props.class"
    >
        <slot />
    </main>
    <main
        v-else
        class="container-xl flex-grow-1 d-flex flex-column gap-3 py-3"
        :class="$props.class"
    >
        <slot />
    </main>
</template>
