<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface SiteMenuItem { label: string; href: string }

const page = usePage<{ site?: { menu?: SiteMenuItem[]; settings?: Record<string, string> } }>();
const menu = computed<SiteMenuItem[]>(() => page.props.site?.menu ?? []);
const settings = computed<Record<string, string>>(() => page.props.site?.settings ?? {});
const heading = computed(() => settings.value['site.heading'] ?? 'Hyperion site');
const tagline = computed(() => settings.value['site.tagline'] ?? '');
const footer = computed(() => settings.value['site.footer.copy'] ?? '');
</script>

<template>
    <div>
        <header class="df-hero">
            <h1>{{ heading }}</h1>
            <p v-if="tagline">{{ tagline }}</p>
            <div class="df-banner" style="display: inline-block; margin-top: 1rem;">
                Estás viendo el tema <strong>default</strong>. Cambia el tema activo en
                <code>/admin/themes</code>.
            </div>
        </header>

        <nav class="df-nav">
            <div class="df-container" style="display: flex; gap: 1.5rem; padding: 0;">
                <a v-for="item in menu" :key="item.label" :href="item.href">{{ item.label }}</a>
            </div>
        </nav>

        <main>
            <slot />
        </main>

        <footer class="df-footer">
            <div class="df-container">{{ footer }}</div>
        </footer>
    </div>
</template>
