<script setup lang="ts">
import { computed } from 'vue';
import type { SiteHighlight } from '../composables/useSite';

const props = defineProps<{
    highlight: SiteHighlight;
}>();

const embedSrc = computed((): string => {
    const url = props.highlight.embed_url?.trim();
    if (!url) return '';
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/);
    if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}`;
    return url;
});
</script>

<template>
    <section id="destacado" class="bg-white py-5">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-12 col-lg-6">
                    <h2 class="jf-heading mb-3">{{ highlight.title }}</h2>
                    <div class="jf-text-justify" v-html="highlight.body"></div>
                </div>
                <div class="col-12 col-lg-6">
                    <div v-if="embedSrc" class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                        <iframe
                            :src="embedSrc"
                            allowfullscreen
                            loading="lazy"
                            :title="highlight.title"
                            style="border: 0;"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
