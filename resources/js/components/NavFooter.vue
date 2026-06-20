<script setup lang="ts">
import { type NavItem } from '@/types';

defineProps<{
    items: NavItem[];
    class?: string;
}>();
</script>

<template>
    <div class="px-3" :class="$props.class">
        <ul class="nav flex-column gap-1">
            <li v-for="item in items" :key="item.title" class="nav-item">
                <a
                    :href="item.href"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="nav-link d-flex align-items-center gap-2 text-body-secondary"
                >
                    <component v-if="item.icon" :is="item.icon" :size="16" />
                    <span>{{ item.title }}</span>
                </a>
            </li>
        </ul>
    </div>
</template>
