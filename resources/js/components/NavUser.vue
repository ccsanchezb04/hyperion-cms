<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { type SharedData, type User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { BDropdown } from 'bootstrap-vue-next';
import { ChevronsUpDown } from 'lucide-vue-next';

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
</script>

<template>
    <BDropdown variant="link" no-caret toggle-class="text-decoration-none w-100 p-2 d-flex align-items-center gap-2 text-body" menu-class="shadow-sm">
        <template #button-content>
            <UserInfo :user="user" />
            <ChevronsUpDown class="ms-auto" :size="16" />
        </template>
        <UserMenuContent :user="user" />
    </BDropdown>
</template>
