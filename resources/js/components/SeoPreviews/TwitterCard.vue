<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    description: string;
    image: string;
    url: string;
    handle?: string;
}>();

const host = computed(() => {
    try {
        return new URL(props.url).host;
    } catch {
        return '';
    }
});
</script>

<template>
    <div class="tw-card border rounded-3 overflow-hidden">
        <div class="tw-image" :style="image ? `background-image: url('${image}')` : ''">
            <div v-if="!image" class="text-body-secondary small">Sin imagen OG</div>
        </div>
        <div class="tw-meta px-3 py-2">
            <div class="tw-host">{{ host }}</div>
            <div class="tw-title">{{ title }}</div>
            <div class="tw-desc">{{ description }}</div>
            <div v-if="handle" class="tw-handle">{{ handle.startsWith('@') ? handle : '@' + handle }}</div>
        </div>
    </div>
</template>

<style scoped>
.tw-card {
    font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
    max-width: 506px;
    background: #fff;
    border-color: #cfd9de !important;
}
.tw-image {
    aspect-ratio: 1200 / 630;
    background-color: #eff3f4;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
}
.tw-meta {
    color: #536471;
    background: #fff;
}
.tw-host {
    font-size: 13px;
    color: #536471;
    line-height: 1.3;
}
.tw-title {
    color: #0f1419;
    font-size: 15px;
    font-weight: 400;
    line-height: 1.3;
    margin-top: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.tw-desc {
    color: #536471;
    font-size: 13px;
    line-height: 1.3;
    margin-top: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.tw-handle {
    color: #536471;
    font-size: 13px;
    margin-top: 4px;
}
</style>
