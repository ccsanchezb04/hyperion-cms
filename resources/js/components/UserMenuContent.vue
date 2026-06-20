<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import type { User } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';

defineProps<{
    user: User;
}>();
</script>

<template>
    <li class="px-3 py-2">
        <UserInfo :user="user" :show-email="true" />
    </li>
    <li><hr class="dropdown-divider" /></li>
    <li>
        <Link :href="route('profile.edit')" class="dropdown-item d-flex align-items-center gap-2">
            <Settings :size="16" />
            <span>Settings</span>
        </Link>
    </li>
    <li><hr class="dropdown-divider" /></li>
    <li>
        <Link method="post" :href="route('logout')" as="button" class="dropdown-item d-flex align-items-center gap-2 w-100 text-start">
            <LogOut :size="16" />
            <span>Log out</span>
        </Link>
    </li>
</template>
