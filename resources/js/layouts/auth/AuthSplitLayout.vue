<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const name = page.props.name;
const quote = page.props.quote as { message: string; author: string } | undefined;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="vh-100 row g-0">
        <div class="col-lg-6 d-none d-lg-flex flex-column p-4 p-lg-5 text-white position-relative" style="background-color: #18181b;">
            <Link :href="route('home')" class="d-inline-flex align-items-center text-white text-decoration-none fw-medium fs-5 position-relative" style="z-index: 2;">
                <AppLogoIcon class="me-2 rounded" style="width: 2rem; height: 2rem;" />
                {{ name }}
            </Link>
            <div v-if="quote" class="mt-auto position-relative" style="z-index: 2;">
                <blockquote class="d-flex flex-column gap-2 mb-0">
                    <p class="fs-5 mb-0">&ldquo;{{ quote.message }}&rdquo;</p>
                    <footer class="small text-body-secondary">{{ quote.author }}</footer>
                </blockquote>
            </div>
        </div>
        <div class="col-lg-6 d-flex align-items-center justify-content-center p-3 p-lg-5">
            <div class="w-100" style="max-width: 22rem;">
                <div class="text-center mb-4">
                    <h1 v-if="title" class="h5 fw-medium mb-1">{{ title }}</h1>
                    <p v-if="description" class="text-body-secondary small mb-0">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
