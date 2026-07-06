<script setup lang="ts">
import { useSite } from '../composables/useSite';
import type { SiteAlly } from '../composables/useSite';

defineProps<{ items: SiteAlly[] }>();

const { setting } = useSite();
</script>

<template>
    <section id="aliados" class="jf-dark-bg py-5">
        <div class="container">
            <h2 class="jf-allies-heading text-center mb-5">
                {{ setting('site.allies.heading', 'Nuestros Aliados') }}
            </h2>
            <div v-if="items.length" class="row g-4 justify-content-center">
                <div v-for="ally in items" :key="ally.id" class="col-6 col-md-4 col-lg-3">
                    <component
                        :is="ally.url ? 'a' : 'div'"
                        v-bind="ally.url ? { href: ally.url, target: '_blank', rel: 'noopener noreferrer' } : {}"
                        class="jf-ally-card"
                    >
                        <img v-if="ally.logo" :src="ally.logo" :alt="ally.name" class="jf-ally-logo" />
                        <span v-else class="jf-ally-name">{{ ally.name }}</span>
                    </component>
                </div>
            </div>
            <p v-else class="text-center text-white-50">Proximamente anunciaremos nuestros aliados.</p>
        </div>
    </section>
</template>

<style scoped>
.jf-allies-heading {
    color: #fff;
    font-family: var(--jf-font-heading);
    font-weight: 700;
}

.jf-ally-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    padding: 1.25rem 1rem;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.12);
    text-decoration: none;
    transition: background 0.2s, transform 0.2s, border-color 0.2s;
    width: 100%;
}

.jf-ally-card:hover {
    background: rgba(255, 255, 255, 0.14);
    border-color: var(--jf-secondary);
    transform: translateY(-3px);
}

.jf-ally-logo {
    max-width: 100%;
    max-height: 80px;
    object-fit: contain;
    filter: brightness(0) invert(1);
    opacity: 0.85;
    transition: opacity 0.2s;
}

.jf-ally-card:hover .jf-ally-logo {
    opacity: 1;
}

.jf-ally-name {
    color: rgba(255, 255, 255, 0.8);
    font-family: var(--jf-font-heading);
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
}
</style>
