<script setup lang="ts">
import { useSite } from '../composables/useSite';
import { computed, onMounted } from 'vue';

const { integration } = useSite();
const gtmId = computed(() => integration('site.integrations.gtm_id'));

onMounted(() => {
    if (!gtmId.value) return;

    (window as any).dataLayer = (window as any).dataLayer || [];
    (window as any).dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtm.js?id=${gtmId.value}`;
    document.head.appendChild(script);
});
</script>

<template>
    <noscript v-if="gtmId">
        <iframe
            :src="`https://www.googletagmanager.com/ns.html?id=${gtmId}`"
            height="0"
            width="0"
            style="display:none;visibility:hidden"
        ></iframe>
    </noscript>
</template>
