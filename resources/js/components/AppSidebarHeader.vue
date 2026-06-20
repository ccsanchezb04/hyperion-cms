<script setup lang="ts">
import AppearanceToggle from '@/components/AppearanceToggle.vue';
import { useSidebar } from '@/composables/useSidebar';
import type { BreadcrumbItemType } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Menu, PanelLeft } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { openMobile, toggleDesktop } = useSidebar();
</script>

<template>
    <header class="d-flex align-items-center gap-2 px-3 py-2 border-bottom bg-body">
        <button type="button" class="btn btn-sm btn-link text-body p-1 d-lg-none" @click="openMobile" aria-label="Open menu">
            <Menu :size="20" />
        </button>
        <button type="button" class="btn btn-sm btn-link text-body p-1 d-none d-lg-inline-flex" @click="toggleDesktop" aria-label="Toggle sidebar">
            <PanelLeft :size="18" />
        </button>

        <nav v-if="breadcrumbs.length" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <template v-for="(item, index) in breadcrumbs" :key="index">
                    <li
                        v-if="index === breadcrumbs.length - 1"
                        class="breadcrumb-item active"
                        aria-current="page"
                    >
                        {{ item.title }}
                    </li>
                    <li v-else class="breadcrumb-item">
                        <Link :href="item.href" class="link-body-emphasis text-decoration-none">{{ item.title }}</Link>
                    </li>
                </template>
            </ol>
        </nav>

        <div class="ms-auto d-flex align-items-center">
            <AppearanceToggle />
        </div>
    </header>
</template>
