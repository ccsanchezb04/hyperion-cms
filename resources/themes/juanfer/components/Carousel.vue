<script setup lang="ts">
import type { SiteCarouselSlide } from '../composables/useSite';

defineProps<{
    slides: SiteCarouselSlide[];
}>();
</script>

<template>
    <div v-if="slides.length" id="jfCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button
                v-for="(slide, idx) in slides"
                :key="`ind-${idx}`"
                type="button"
                data-bs-target="#jfCarousel"
                :data-bs-slide-to="idx"
                :class="{ active: idx === 0 }"
                :aria-current="idx === 0 ? 'true' : undefined"
                :aria-label="`Slide ${idx + 1}`"
            ></button>
        </div>
        <div class="carousel-inner">
            <div
                v-for="(slide, idx) in slides"
                :key="`item-${idx}`"
                class="carousel-item"
                :class="{ active: idx === 0 }"
            >
                <img :src="slide.src" class="d-block w-100" :alt="slide.alt" />
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#jfCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#jfCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</template>
