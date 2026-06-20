<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface SeoAlternate {
    hreflang: string;
    href: string;
}

const labels: Record<string, string> = {
    es: 'ES',
    en: 'EN',
};

const page = usePage<{ seo?: { alternates?: SeoAlternate[] } }>();

const alternates = computed<SeoAlternate[]>(() => {
    const list = page.props.seo?.alternates ?? [];
    // Excluir x-default; ya está cubierto por uno de los locales reales
    return list.filter((a) => a.hreflang !== 'x-default');
});

const current = computed<string>(() => {
    // El html lang está seteado via og:locale (es_CO/en_US). El path actual
    // determina qué link está activo. Si empieza con /en, es 'en'; sino 'es'.
    const path = typeof window !== 'undefined' ? window.location.pathname : '';
    if (path === '/en' || path.startsWith('/en/')) return 'en';
    return 'es';
});

const isCurrent = (lang: string) => lang === current.value;
</script>

<template>
    <div class="jf-langswitch d-inline-flex align-items-center gap-1">
        <a
            v-for="alt in alternates"
            :key="alt.hreflang"
            :href="alt.href"
            class="jf-langswitch__btn"
            :class="{ 'jf-langswitch__btn--active': isCurrent(alt.hreflang) }"
            :aria-current="isCurrent(alt.hreflang) ? 'page' : undefined"
            :hreflang="alt.hreflang"
        >
            {{ labels[alt.hreflang] ?? alt.hreflang.toUpperCase() }}
        </a>
    </div>
</template>

<style scoped>
.jf-langswitch__btn {
    padding: 0.15rem 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--jf-text, #6c757d);
    text-decoration: none;
    border-radius: 0.25rem;
    transition: background 0.15s, color 0.15s;
}
.jf-langswitch__btn:hover {
    background: rgba(0, 0, 0, 0.05);
    color: var(--jf-primary, #0066cc);
}
.jf-langswitch__btn--active {
    background: var(--jf-primary, #0066cc);
    color: #fff;
}
.jf-langswitch__btn--active:hover {
    color: #fff;
}
</style>
