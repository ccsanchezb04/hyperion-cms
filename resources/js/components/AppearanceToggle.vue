<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

const { appearance, updateAppearance } = useAppearance();

const order = ['light', 'dark', 'system'] as const;
type Appearance = (typeof order)[number];

const icon = computed(() => {
    switch (appearance.value) {
        case 'light': return Sun;
        case 'dark': return Moon;
        default: return Monitor;
    }
});

const label = computed(() => {
    switch (appearance.value) {
        case 'light': return 'Tema claro';
        case 'dark': return 'Tema oscuro';
        default: return 'Tema del sistema';
    }
});

const cycle = () => {
    const idx = order.indexOf(appearance.value as Appearance);
    const next = order[(idx + 1) % order.length];
    updateAppearance(next);
};
</script>

<template>
    <button
        type="button"
        class="btn btn-sm btn-link text-body p-1 d-inline-flex align-items-center"
        @click="cycle"
        :title="label"
        :aria-label="label"
    >
        <component :is="icon" :size="18" />
    </button>
</template>
