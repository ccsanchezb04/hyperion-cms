<script setup lang="ts">
defineOptions({
    inheritAttrs: false,
});

defineProps<{
    class?: string;
}>();
</script>

<template>
    <img
        src="/images/hyperion-icon.png"
        alt="Hyperion CMS"
        :class="$props.class"
        v-bind="$attrs"
    />
</template>
