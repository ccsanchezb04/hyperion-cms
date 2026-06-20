import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export interface SiteMenuItem {
    label: string;
    href: string;
}

export interface SiteSharedProps {
    settings: Record<string, string>;
    menu: SiteMenuItem[];
}

export interface SiteSolution {
    id: number;
    title: string;
    slug: string;
    body: string;
    image: string | null;
    href: string;
}

export interface SiteTestimonial {
    name: string;
    quote: string;
}

export interface SiteCarouselSlide {
    src: string;
    alt: string;
}

export interface SiteSeoOg {
    title: string;
    description: string;
    image: string;
    url: string;
    type: string;
    locale: string;
    site_name: string;
}

export interface SiteSeoTwitter {
    card: string;
    title: string;
    description: string;
    image: string;
}

export interface SiteSeoData {
    title: string;
    description: string;
    keywords: string;
    canonical: string;
    robots: string;
    locale: string;
    og: SiteSeoOg;
    twitter: SiteSeoTwitter;
    json_ld: Record<string, unknown>[];
}

/**
 * Acceso tipado a las props compartidas del sitio (settings + menú principal).
 * Disponible en cualquier componente del tema.
 */
export function useSite() {
    const page = usePage<{ site: SiteSharedProps }>();

    const settings = computed<Record<string, string>>(() => page.props.site?.settings ?? {});
    const menu = computed<SiteMenuItem[]>(() => page.props.site?.menu ?? []);

    const setting = (key: string, fallback = ''): string => settings.value[key] ?? fallback;

    return { settings, menu, setting };
}
