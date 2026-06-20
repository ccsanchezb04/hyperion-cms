<script setup lang="ts">
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import type { Component } from 'vue';

interface NavItem {
    title: string;
    href?: string;
    url?: string;
    icon?: Component;
}

defineProps<{
    items: NavItem[];
}>();

const page = usePage<SharedData>();
</script>

<template>
    <div class="px-3">
        <div class="text-uppercase small fw-semibold text-body-secondary mb-2 px-2">Platform</div>
        <ul class="nav nav-pills flex-column gap-1">
            <li v-for="item in items" :key="item.title" class="nav-item">
                <Link
                    :href="(item.href ?? item.url)!"
                    class="nav-link d-flex align-items-center gap-2 text-body"
                    :class="{ active: (item.href ?? item.url) === page.url }"
                >
                    <component v-if="item.icon" :is="item.icon" :size="16" />
                    <span>{{ item.title }}</span>
                </Link>
            </li>
        </ul>
    </div>
</template>
