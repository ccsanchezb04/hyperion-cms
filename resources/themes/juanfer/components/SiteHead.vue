<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { SiteSeoData } from '../composables/useSite';

defineProps<{
    seo: SiteSeoData & { alternates?: { hreflang: string; href: string }[] };
}>();
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
        <meta name="robots" :content="seo.robots" />
        <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />

        <meta property="og:title" :content="seo.og.title" />
        <meta property="og:description" :content="seo.og.description" />
        <meta v-if="seo.og.image" property="og:image" :content="seo.og.image" />
        <meta v-if="seo.og.url" property="og:url" :content="seo.og.url" />
        <meta property="og:type" :content="seo.og.type" />
        <meta property="og:locale" :content="seo.og.locale" />
        <meta property="og:site_name" :content="seo.og.site_name" />

        <meta name="twitter:card" :content="seo.twitter.card" />
        <meta name="twitter:title" :content="seo.twitter.title" />
        <meta name="twitter:description" :content="seo.twitter.description" />
        <meta v-if="seo.twitter.image" name="twitter:image" :content="seo.twitter.image" />

        <link
            v-for="(alt, i) in seo.alternates ?? []"
            :key="`alt-${i}-${alt.hreflang}`"
            rel="alternate"
            :hreflang="alt.hreflang"
            :href="alt.href"
        />
    </Head>

    <!--
        JSON-LD se renderiza fuera de <Head> usando <component is="script">
        porque Vue no permite <script> directo en templates. Google y otros
        crawlers aceptan structured data en cualquier parte del documento.
    -->
    <component
        v-for="(block, i) in seo.json_ld"
        :key="i"
        is="script"
        type="application/ld+json"
        v-html="JSON.stringify(block)"
    />
</template>
