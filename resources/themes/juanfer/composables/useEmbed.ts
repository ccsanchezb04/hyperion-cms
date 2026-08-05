import { computed, type Ref } from 'vue';

const YOUTUBE = /(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{11})/;
const VIMEO   = /vimeo\.com\/(\d+)/;
const LINKEDIN = /linkedin\.com/i;

export function useEmbed(urlRef: Ref<string | null | undefined>) {
    const raw = computed(() => urlRef.value?.trim() ?? '');

    const isLinkedIn = computed(() => LINKEDIN.test(raw.value));

    const embedSrc = computed((): string => {
        if (!raw.value || isLinkedIn.value) return '';

        const yt = raw.value.match(YOUTUBE);
        if (yt) return `https://www.youtube.com/embed/${yt[1]}`;

        const vi = raw.value.match(VIMEO);
        if (vi) return `https://player.vimeo.com/video/${vi[1]}`;

        return raw.value;
    });

    return { embedSrc, isLinkedIn, externalUrl: raw };
}
