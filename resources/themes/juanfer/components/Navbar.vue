<script setup lang="ts">
import { useSite } from '../composables/useSite';
import LanguageSwitcher from './LanguageSwitcher.vue';

const { menu } = useSite();
</script>

<template>
    <nav class="jf-navbar navbar jf-dark-bg navbar-expand-lg">
        <div class="container">
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

            <div class="collapse navbar-collapse justify-content-center" id="jfNavbarNav">
                <ul class="navbar-nav mx-auto">
                    <li v-for="item in menu" :key="item.label" class="nav-item">
                        <a class="nav-link" :href="item.href">{{ item.label }}</a>
                    </li>
                </ul>
                <div class="ms-auto">
                    <LanguageSwitcher />
                </div>
            </div>
        </div>
    </nav>
</template>
