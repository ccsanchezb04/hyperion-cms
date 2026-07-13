<script setup lang="ts">
import Ga4Snippet from '../components/Ga4Snippet.vue';
import GtmSnippet from '../components/GtmSnippet.vue';
import WhatsAppButton from '../components/WhatsAppButton.vue';
import { useSite } from '../composables/useSite';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    title?: string;
}>();

const { integration } = useSite();
const gscCode = computed(() => integration('site.integrations.gsc_verification'));
</script>

<template>
    <Head v-if="gscCode">
        <meta name="google-site-verification" :content="gscCode" />
    </Head>

    <div class="jf-site">
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
</style>
