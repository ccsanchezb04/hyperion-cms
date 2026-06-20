<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    class?: string;
}>();
</script>

<template>
    <div class="d-flex align-items-center gap-2" :class="$props.class">
        <span class="d-inline-flex align-items-center justify-content-center overflow-hidden rounded" style="width: 2rem; height: 2rem;">
            <AppLogoIcon style="width: 2rem; height: 2rem;" />
        </span>
        <span class="fw-semibold text-truncate">Hyperion CMS</span>
    </div>
</template>
