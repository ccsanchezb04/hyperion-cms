<script setup lang="ts">
defineProps<{
    name: string;
    quote: string;
}>();
</script>

<template>
    <div class="card h-100">
        <div class="card-body">
            <h5 class="card-title">{{ name }}</h5>
            <p class="card-text">&ldquo;{{ quote }}&rdquo;</p>
        </div>
    </div>
</template>
