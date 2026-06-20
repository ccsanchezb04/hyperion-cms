<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    description: string;
    image: string;
    url: string;
}>();

const host = computed(() => {
    try {
        return new URL(props.url).host.toUpperCase();
    } catch {
        return '';
    }
});
</script>

<template>
    <div class="fb-card border rounded overflow-hidden">
        <div class="fb-image" :style="image ? `background-image: url('${image}')` : ''">
            <div v-if="!image" class="fb-image-placeholder text-body-secondary small">
                Sin imagen OG
            </div>
        </div>
        <div class="fb-meta p-3">
            <div class="fb-host">{{ host }}</div>
            <div class="fb-title">{{ title }}</div>
            <div class="fb-desc">{{ description }}</div>
        </div>
    </div>
</template>

<style scoped>
.fb-card {
    font-family: Helvetica, Arial, sans-serif;
    max-width: 524px;
    background: #fff;
}
.fb-image {
    aspect-ratio: 1200 / 630;
    background-color: #e4e6eb;
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
}
.fb-image-placeholder {
    color: #65676b;
}
.fb-meta {
    background: #f0f2f5;
    color: #65676b;
}
.fb-host {
    font-size: 12px;
    color: #65676b;
    text-transform: uppercase;
    line-height: 1.4;
}
.fb-title {
    color: #050505;
    font-size: 17px;
    font-weight: 600;
    line-height: 1.2;
    margin-top: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.fb-desc {
    color: #65676b;
    font-size: 14px;
    line-height: 1.3;
    margin-top: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
