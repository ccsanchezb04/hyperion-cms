<script setup lang="ts">
import { useSite } from '../composables/useSite';

const { setting } = useSite();
</script>

<template>
    <section id="nosotros" class="jf-dark-bg text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="text-center my-4 jf-heading">
                        {{ setting('site.about.heading', 'Somos JuanFer Seguros') }}
                    </h2>
                    <p class="jf-text-justify mb-4">{{ setting('site.about.intro') }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <h3 class="text-center jf-heading">
                        {{ setting('site.about.vision_title', 'Visión') }}
                    </h3>
                    <p>{{ setting('site.about.vision_lead') }}</p>
                    <p class="jf-text-justify">{{ setting('site.about.vision_body') }}</p>
                </div>
                <div class="col-lg-6">
                    <h3 class="text-center jf-heading">
                        {{ setting('site.about.mission_title', 'Misión') }}
                    </h3>
                    <p class="jf-text-justify">{{ setting('site.about.mission_body') }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
