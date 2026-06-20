<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

defineProps<{
    class?: string;
}>();

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div class="btn-group" role="group" aria-label="Appearance" :class="$props.class">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            type="button"
            class="btn btn-sm d-inline-flex align-items-center gap-2"
            :class="appearance === value ? 'btn-primary' : 'btn-outline-secondary'"
            @click="updateAppearance(value)"
        >
            <component :is="Icon" :size="14" />
            <span>{{ label }}</span>
        </button>
    </div>
</template>
