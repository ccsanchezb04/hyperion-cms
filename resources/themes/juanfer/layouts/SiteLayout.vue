<script setup lang="ts">
import Ga4Snippet from '../components/Ga4Snippet.vue';
import GtmSnippet from '../components/GtmSnippet.vue';
import LanguageSwitcher from '../components/LanguageSwitcher.vue';
import WhatsAppButton from '../components/WhatsAppButton.vue';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="jf-site">
        <!-- Selector de idioma fijo en la esquina superior derecha -->
        <div class="jf-lang-bar">
            <LanguageSwitcher />
        </div>

        <main>
            <slot />
        </main>

        <WhatsAppButton />
        <GtmSnippet />
        <Ga4Snippet />
    </div>
</template>

<style scoped>
.jf-site {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Fijo en la esquina superior derecha, encima de todo, sin afectar el layout */
.jf-lang-bar {
    position: fixed;
    top: 0.6rem;
    right: 1rem;
    z-index: 1050;
    background: rgba(20, 30, 60, 0.82);
    border-radius: 6px;
    padding: 0.2rem 0.6rem;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
</style>
