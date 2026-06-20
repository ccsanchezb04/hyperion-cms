<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    description: string;
    url: string;
    siteName?: string;
}>();

const displayUrl = computed(() => {
    try {
        const u = new URL(props.url);
        const parts = u.pathname.split('/').filter(Boolean);
        return [u.host, ...parts].join(' › ');
    } catch {
        return props.url || '';
    }
});

const truncatedTitle = computed(() =>
    props.title.length > 60 ? props.title.slice(0, 57) + '…' : props.title,
);
const truncatedDesc = computed(() =>
    props.description.length > 160 ? props.description.slice(0, 157) + '…' : props.description,
);
</script>

<template>
    <div class="google-snippet border rounded p-3 bg-white">
        <div class="gs-source d-flex align-items-center gap-2 mb-1">
            <div class="gs-favicon"></div>
            <div class="d-flex flex-column">
                <span class="gs-site">{{ siteName ?? 'JuanFer Seguros' }}</span>
                <span class="gs-url">{{ displayUrl }}</span>
            </div>
        </div>
        <a href="#" class="gs-title text-decoration-none d-block">{{ truncatedTitle }}</a>
        <p class="gs-desc mb-0">{{ truncatedDesc }}</p>
    </div>
</template>

<style scoped>
.google-snippet {
    font-family: arial, sans-serif;
    max-width: 600px;
}
.gs-favicon {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #1a73e8;
    flex-shrink: 0;
}
.gs-site {
    font-size: 14px;
    color: #202124;
    line-height: 1.2;
}
.gs-url {
    font-size: 12px;
    color: #4d5156;
    line-height: 1.2;
}
.gs-title {
    font-size: 20px;
    line-height: 1.3;
    color: #1a0dab;
    font-weight: 400;
}
.gs-title:hover {
    text-decoration: underline !important;
}
.gs-desc {
    font-size: 14px;
    color: #4d5156;
    line-height: 1.58;
    margin-top: 4px;
}
</style>
