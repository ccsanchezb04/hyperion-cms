<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-vh-100 d-flex flex-column align-items-center justify-content-center bg-body-tertiary p-3 p-md-5">
        <div class="w-100 d-flex flex-column gap-3" style="max-width: 28rem;">
            <Link :href="route('home')" class="d-flex align-items-center justify-content-center text-body text-decoration-none fw-medium">
                <span class="d-inline-flex align-items-center justify-content-center overflow-hidden rounded-3 shadow-sm" style="width: 5rem; height: 5rem;">
                    <AppLogoIcon style="width: 5rem; height: 5rem;" />
                </span>
            </Link>

            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-transparent text-center border-0 pt-4 px-4 px-md-5">
                    <h2 class="h5 mb-1">{{ title }}</h2>
                    <p v-if="description" class="text-body-secondary small mb-0">{{ description }}</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
