<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import logoSrc from '../assets/images/logos/logo_isotipo-05.png';
import { useSite } from '../composables/useSite';
import LanguageSwitcher from './LanguageSwitcher.vue';

const { menu } = useSite();

const scrolled = ref(false);

const onScroll = () => {
    scrolled.value = window.scrollY > 10;
};

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <nav class="jf-navbar navbar jf-dark-bg navbar-expand-lg sticky-top" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand p-0 jf-navbar-brand" :class="{ 'jf-navbar-brand--visible': scrolled }" href="/">
                <img :src="logoSrc" alt="JuanFer Seguros" class="jf-navbar-logo" />
            </a>

            <!-- Menú colapsable (desktop: flex row centrado; mobile: despliega debajo) -->
            <div class="collapse navbar-collapse" id="jfNavbarNav">
                <ul class="navbar-nav mx-auto">
                    <li v-for="item in menu" :key="item.label" class="nav-item">
                        <a class="nav-link" :href="item.href">{{ item.label }}</a>
                    </li>
                </ul>
            </div>

            <!-- Idioma + hamburguesa: siempre visibles, agrupados a la derecha -->
            <div class="d-flex align-items-center gap-2 ms-auto order-lg-last">
                <LanguageSwitcher />
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#jfNavbarNav"
                    aria-controls="jfNavbarNav"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </nav>
</template>

<style scoped>
.jf-navbar-brand {
    opacity: 0;
    width: 0;
    overflow: hidden;
    transition: opacity 0.3s ease, width 0.3s ease;
}

.jf-navbar-brand--visible {
    opacity: 1;
    width: auto;
}

.jf-navbar-logo {
    height: 44px;
    width: auto;
    display: block;
}
</style>
