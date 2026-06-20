import { onMounted, ref } from 'vue';

const STORAGE_KEY = 'sidebar';
const mobileOpen = ref(false);
const desktopCollapsed = ref(false);

export function useSidebar() {
    onMounted(() => {
        desktopCollapsed.value = localStorage.getItem(STORAGE_KEY) === 'collapsed';
    });

    function toggleDesktop() {
        desktopCollapsed.value = !desktopCollapsed.value;
        localStorage.setItem(STORAGE_KEY, desktopCollapsed.value ? 'collapsed' : 'expanded');
    }

    function openMobile() {
        mobileOpen.value = true;
    }

    function closeMobile() {
        mobileOpen.value = false;
    }

    return {
        mobileOpen,
        desktopCollapsed,
        toggleDesktop,
        openMobile,
        closeMobile,
    };
}
