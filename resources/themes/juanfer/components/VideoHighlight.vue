<script setup lang="ts">
import { computed } from 'vue';
import { useEmbed } from '../composables/useEmbed';
import type { SiteHighlight } from '../composables/useSite';

const props = defineProps<{
    highlight: SiteHighlight;
}>();

const urlRef = computed(() => props.highlight.embed_url);
const { embedSrc, isLinkedIn, isTikTok, tikTokEmbedSrc, externalUrl } = useEmbed(urlRef);
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
                    <div v-else-if="isTikTok" class="ratio rounded overflow-hidden shadow-sm" style="--bs-aspect-ratio: 177%;">
                        <iframe
                            :src="tikTokEmbedSrc"
                            allowfullscreen
                            loading="lazy"
                            :title="highlight.title"
                            style="border: 0;"
                        ></iframe>
                    </div>
                    <div v-else-if="isLinkedIn" class="d-flex justify-content-center align-items-center py-4">
                        <a
                            :href="externalUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="jf-btn"
                        >
                            Ver en LinkedIn &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
