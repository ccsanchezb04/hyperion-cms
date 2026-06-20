<script setup lang="ts">
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';
import { computed } from 'vue';

interface Props {
    user: User;
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

const showAvatar = computed(() => Boolean(props.user.avatar));
</script>

<template>
    <div class="d-flex align-items-center gap-2 text-start">
        <span
            class="d-inline-flex align-items-center justify-content-center rounded-circle bg-body-secondary fw-semibold flex-shrink-0"
            style="width: 2rem; height: 2rem; font-size: 0.8rem;"
        >
            <img v-if="showAvatar" :src="user.avatar" :alt="user.name" class="rounded-circle w-100 h-100" />
            <template v-else>{{ getInitials(user.name) }}</template>
        </span>
        <div class="d-flex flex-column overflow-hidden lh-sm">
            <span class="fw-medium text-truncate">{{ user.name }}</span>
            <span v-if="showEmail" class="small text-body-secondary text-truncate">{{ user.email }}</span>
        </div>
    </div>
</template>
