<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    { title: 'Profile', href: '/admin/settings/profile' },
    { title: 'Password', href: '/admin/settings/password' },
    { title: 'Appearance', href: '/admin/settings/appearance' },
];

const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-4">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="row g-4">
            <aside class="col-12 col-lg-3">
                <nav class="nav flex-column gap-1">
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="nav-link text-start text-body rounded"
                        :class="{ 'active bg-body-secondary': currentPath === item.href }"
                    >
                        {{ item.title }}
                    </Link>
                </nav>
            </aside>

            <div class="col-12 col-lg-9">
                <hr class="d-lg-none my-3" />
                <section class="d-flex flex-column gap-4" style="max-width: 36rem;">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
