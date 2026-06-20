<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center bg-body p-3 p-md-5">
        <div class="w-100" style="max-width: 22rem;">
            <div class="d-flex flex-column gap-4">
                <div class="d-flex flex-column align-items-center gap-3">
                    <Link :href="route('home')" class="d-flex flex-column align-items-center gap-2 text-body text-decoration-none fw-medium">
                        <span class="d-inline-flex align-items-center justify-content-center overflow-hidden rounded-3 shadow-sm" style="width: 5rem; height: 5rem;">
                            <AppLogoIcon style="width: 5rem; height: 5rem;" />
                        </span>
                        <span class="visually-hidden">{{ title }}</span>
                    </Link>
                    <div class="text-center">
                        <h1 class="h5 fw-medium mb-1">{{ title }}</h1>
                        <p v-if="description" class="text-body-secondary small mb-0">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
