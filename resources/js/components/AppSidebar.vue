<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { usePermissions } from '@/composables/usePermissions';
import { useSidebar } from '@/composables/useSidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BOffcanvas } from 'bootstrap-vue-next';
import {
    BookOpen,
    FileText,
    Folder,
    Image,
    Inbox,
    KeyRound,
    LayoutGrid,
    ListTree,
    Paintbrush,
    Settings,
    ShieldCheck,
    Tags,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';

const { can } = usePermissions();

const allNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
    { title: 'Contents', href: '/admin/contents', icon: FileText, permission: 'view-content' },
    { title: 'Categories', href: '/admin/categories', icon: Tags, permission: 'view-category' },
    { title: 'Media Library', href: '/admin/media', icon: Image, permission: 'view-media' },
    { title: 'Menus', href: '/admin/menus', icon: ListTree, permission: 'view-menu' },
    { title: 'Contact messages', href: '/admin/contact-messages', icon: Inbox, permission: 'view-contact-messages' },
    { title: 'Users', href: '/admin/users', icon: Users, permission: 'view-users' },
    { title: 'Roles', href: '/admin/roles', icon: ShieldCheck, permission: 'manage-roles' },
    { title: 'Permissions', href: '/admin/permissions', icon: KeyRound, permission: 'view-permissions' },
    { title: 'Themes', href: '/admin/themes', icon: Paintbrush, permission: 'manage-settings' },
    { title: 'Settings', href: '/admin/settings', icon: Settings, permission: 'view-settings' },
];

const mainNavItems = computed(() => allNavItems.filter((item) => can(item.permission)));

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];

const { mobileOpen, desktopCollapsed, closeMobile } = useSidebar();
</script>

<template>
    <aside
        class="app-sidebar border-end bg-body-tertiary d-none d-lg-flex flex-column"
        :class="{ collapsed: desktopCollapsed }"
    >
        <div class="p-3 border-bottom">
            <Link :href="route('dashboard')" class="text-decoration-none text-body">
                <AppLogo />
            </Link>
        </div>
        <div class="flex-grow-1 py-3 overflow-auto">
            <NavMain :items="mainNavItems" />
        </div>
        <div class="border-top py-2">
            <NavFooter :items="footerNavItems" class="mb-2" />
            <div class="px-2">
                <NavUser />
            </div>
        </div>
    </aside>

    <BOffcanvas v-model="mobileOpen" placement="start" class="app-sidebar-offcanvas">
        <template #header>
            <Link :href="route('dashboard')" class="text-decoration-none text-body" @click="closeMobile">
                <AppLogo />
            </Link>
        </template>
        <div class="d-flex flex-column h-100">
            <div class="flex-grow-1" @click="closeMobile">
                <NavMain :items="mainNavItems" />
            </div>
            <div class="border-top pt-3">
                <NavFooter :items="footerNavItems" class="mb-2" />
                <div class="px-2">
                    <NavUser />
                </div>
            </div>
        </div>
    </BOffcanvas>
</template>

<style scoped>
.app-sidebar {
    width: 16rem;
    flex-shrink: 0;
    transition: width 0.15s ease;
}
.app-sidebar.collapsed {
    width: 4rem;
    overflow: hidden;
}
.app-sidebar-offcanvas :deep(.offcanvas) {
    width: 16rem;
}
</style>
