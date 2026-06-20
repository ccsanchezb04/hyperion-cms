<script setup lang="ts">
defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="jf-site">
        <main>
            <slot />
        </main>
    </div>
</template>

<style scoped>
.jf-site {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
</style>
