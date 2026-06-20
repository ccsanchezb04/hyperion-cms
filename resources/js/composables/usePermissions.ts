import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Reactive helper to check the current user's permissions.
 *
 * The backend exposes `auth.user.permissions` as a flat list of slugs.
 * Super-admins receive a single `'*'` wildcard so the check short-circuits
 * without iterating every slug.
 */
export function usePermissions() {
    const page = usePage<SharedData>();

    const permissions = computed<string[]>(() => page.props.auth.user?.permissions ?? []);

    const isSuperAdmin = computed(() => permissions.value.includes('*'));

    function can(permission?: string): boolean {
        if (!permission) return true;
        if (isSuperAdmin.value) return true;
        return permissions.value.includes(permission);
    }

    return { permissions, isSuperAdmin, can };
}
