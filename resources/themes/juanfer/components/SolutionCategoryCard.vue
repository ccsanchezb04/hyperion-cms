<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    category: string | null;
    href: string;
    image: string | null;
}>();

const taglines: Record<string, string> = {
    salud:    'Cuidamos tu salud y el bienestar de los tuyos.',
    vida:     'Protegemos tu autonomía, tus ingresos y el futuro de tu familia.',
    movilidad:'Te acompañamos en cada camino, seguro y sin preocupaciones.',
    hogar:    'Protegemos tu lugar seguro, cuidamos tus bienes y patrimonio.',
    empresas: 'Impulsamos tu crecimiento, protegemos tu inversión, cuidamos tu equipo humano.',
    rentas:   'Brindamos tranquilidad financiera para asegurar tu futuro.',
};

const iconMap: Record<string, string> = {
    salud: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
    vida: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
    movilidad: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17H5a2 2 0 0 1-2-2V9l2-4h14l2 4v6a2 2 0 0 1-2 2Z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>',
    hogar: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    empresas: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>',
    rentas: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
};

const tagline = computed(() => taglines[props.category ?? ''] ?? '');
const icon    = computed(() => iconMap[props.category ?? ''] ?? iconMap.salud);
</script>

<template>
    <a :href="href" class="jf-sol-card">
        <img v-if="image" :src="image" :alt="title" class="jf-sol-card__img" />
        <div class="jf-sol-card__body">
            <div v-if="!image" class="jf-sol-card__icon" v-html="icon" />
            <h3 class="jf-sol-card__title">{{ title }}</h3>
            <p class="jf-sol-card__desc">{{ tagline }}</p>
            <span class="jf-sol-card__cta">Ver portafolio &rarr;</span>
        </div>
    </a>
</template>

<style scoped>
.jf-sol-card {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0;
    padding: 0;
    border-radius: 12px;
    background: var(--jf-primary);
    border: 1px solid rgba(255, 255, 255, 0.12);
    overflow: hidden;
    transition: border-color 0.2s, transform 0.2s;
    height: 100%;
    text-decoration: none;
    color: #fff;
}

.jf-sol-card__img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    display: block;
    transition: transform 0.35s ease;
}
.jf-sol-card:hover .jf-sol-card__img {
    transform: scale(1.04);
}

.jf-sol-card__body {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1.5rem;
    flex: 1;
}

.jf-sol-card:hover {
    background: var(--jf-secondary);
    border-color: var(--jf-secondary);
    transform: translateY(-4px);
    color: #fff;
}

.jf-sol-card__icon {
    color: var(--jf-secondary);
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    transition: color 0.2s;
}
.jf-sol-card:hover .jf-sol-card__icon {
    color: #fff;
}
.jf-sol-card__icon :deep(svg) {
    width: 100%;
    height: 100%;
}

.jf-sol-card__title {
    font-family: var(--jf-font-heading);
    font-weight: 700;
    font-size: 1.2rem;
    color: #fff;
    margin: 0;
}

.jf-sol-card__desc {
    font-size: 0.93rem;
    color: rgba(255, 255, 255, 0.72);
    flex: 1;
    margin: 0;
    line-height: 1.55;
}

.jf-sol-card__cta {
    color: var(--jf-secondary);
    font-weight: 600;
    font-size: 0.88rem;
    margin-top: 0.25rem;
    transition: color 0.2s;
}
.jf-sol-card:hover .jf-sol-card__cta {
    color: rgba(255, 255, 255, 0.9);
}
</style>
