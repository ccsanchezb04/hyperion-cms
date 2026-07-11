<script setup lang="ts">
import { useSite } from '../composables/useSite';
import { computed, onMounted } from 'vue';

const { integration } = useSite();
const ga4Id = computed(() => integration('site.integrations.ga4_measurement_id'));

onMounted(() => {
    if (!ga4Id.value) return;

    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${ga4Id.value}`;
    document.head.appendChild(script);

    (window as any).dataLayer = (window as any).dataLayer || [];
    function gtag(...args: any[]) { (window as any).dataLayer.push(args); }
    (window as any).gtag = gtag;
    gtag('js', new Date());
    gtag('config', ga4Id.value);
});
</script>

<template></template>
