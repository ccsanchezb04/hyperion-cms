<script setup lang="ts">
import { computed, ref } from 'vue';
import { useSite } from '../composables/useSite';

const { orgSetting, setting } = useSite();

const open      = ref(false);
const userMsg   = ref('');

const phone = computed(() => {
    const raw = orgSetting('site.organization.phone', '').replace(/\D/g, '');
    // Agrega código de Colombia si no viene con prefijo internacional
    return raw.startsWith('57') ? raw : `57${raw}`;
});

const defaultText = computed(() =>
    setting('site.whatsapp.default_message', 'Hola, ¿en qué podemos ayudarte?'),
);

const companyName = computed(() =>
    orgSetting('site.organization.name', 'JuanFer Seguros'),
);

const send = () => {
    const text = userMsg.value.trim() || defaultText.value;
    const url = `https://api.whatsapp.com/send?phone=${phone.value}&text=${encodeURIComponent(text)}`;
    window.open(url, '_blank', 'noopener,noreferrer');
    open.value  = false;
    userMsg.value = '';
};
</script>

<template>
    <Teleport to="body">
        <div class="jf-wa" :class="{ 'jf-wa--open': open }">
            <!-- Popup -->
            <Transition name="jf-wa-popup">
                <div v-if="open" class="jf-wa__popup">
                    <!-- Cabecera -->
                    <div class="jf-wa__head">
                        <div class="jf-wa__head-info">
                            <span class="jf-wa__head-dot" />
                            <span class="jf-wa__head-name">{{ companyName }}</span>
                        </div>
                        <button
                            type="button"
                            class="jf-wa__close"
                            aria-label="Cerrar"
                            @click="open = false"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    <!-- Burbuja de mensaje -->
                    <div class="jf-wa__body">
                        <div class="jf-wa__bubble">{{ defaultText }}</div>
                    </div>

                    <!-- Input + enviar -->
                    <div class="jf-wa__footer">
                        <textarea
                            v-model="userMsg"
                            class="jf-wa__input"
                            rows="2"
                            :placeholder="defaultText"
                            @keydown.enter.exact.prevent="send"
                        />
                        <button type="button" class="jf-wa__send" aria-label="Enviar" @click="send">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Botón flotante -->
            <button
                type="button"
                class="jf-wa__btn"
                :aria-label="open ? 'Cerrar WhatsApp' : 'Contactar por WhatsApp'"
                @click="open = !open"
            >
                <!-- Icono WhatsApp -->
                <svg v-if="!open" class="jf-wa__icon" viewBox="0 0 32 32" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16.004 2.667C8.64 2.667 2.667 8.64 2.667 16c0 2.347.61 4.56 1.68 6.48L2.667 29.333l7.04-1.653A13.253 13.253 0 0 0 16.004 29.333c7.36 0 13.33-5.973 13.33-13.333S23.363 2.667 16.003 2.667zm0 24c-2.133 0-4.133-.56-5.867-1.547l-.413-.24-4.173.987.987-4.107-.267-.427A10.613 10.613 0 0 1 5.333 16c0-5.893 4.773-10.667 10.667-10.667S26.667 10.107 26.667 16 21.893 26.667 16.003 26.667zm5.84-7.973c-.32-.16-1.893-.933-2.187-1.04-.293-.107-.507-.16-.72.16-.213.32-.827 1.04-.987 1.24-.187.213-.373.24-.693.08-.32-.16-1.36-.507-2.587-1.6-.96-.853-1.6-1.907-1.787-2.227-.187-.32-.013-.48.147-.64.147-.133.32-.347.48-.52.16-.173.213-.293.32-.507.107-.213.053-.4-.027-.56-.08-.16-.72-1.733-.987-2.373-.253-.627-.507-.533-.72-.547-.187-.013-.4-.013-.613-.013s-.56.08-.853.4c-.293.32-1.12 1.093-1.12 2.667s1.147 3.093 1.307 3.307c.16.213 2.253 3.44 5.467 4.827.76.333 1.36.533 1.813.68.76.24 1.453.213 2 .133.613-.093 1.893-.773 2.16-1.52.267-.747.267-1.387.187-1.52-.08-.133-.267-.213-.587-.373z"/>
                </svg>
                <!-- X cuando está abierto -->
                <svg v-else class="jf-wa__icon jf-wa__icon--close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </Teleport>
</template>

<style scoped>
/* ── Contenedor fijo ───────────────────────────────── */
.jf-wa {
    position: fixed;
    bottom: 1.25rem;
    right: 1.25rem;
    z-index: 3000;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.75rem;
}

/* ── Botón circular ────────────────────────────────── */
.jf-wa__btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #25d366;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(37, 211, 102, 0.45);
    transition: transform 0.2s, box-shadow 0.2s;
    flex-shrink: 0;
}
.jf-wa__btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(37, 211, 102, 0.5);
}
.jf-wa__icon {
    width: 34px;
    height: 34px;
    color: #fff;
}
.jf-wa__icon--close {
    width: 22px;
    height: 22px;
}

/* ── Popup ─────────────────────────────────────────── */
.jf-wa__popup {
    width: 280px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
    background: #e5ddd5;
}

.jf-wa__head {
    background: var(--jf-primary, #141e3c);
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}
.jf-wa__head-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
}
.jf-wa__head-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #25d366;
    flex-shrink: 0;
}
.jf-wa__head-name {
    font-family: var(--jf-font-heading);
    font-weight: 600;
    font-size: 0.88rem;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.jf-wa__close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    color: rgba(255,255,255,0.7);
    display: flex;
    flex-shrink: 0;
}
.jf-wa__close:hover { color: #fff; }
.jf-wa__close svg { width: 16px; height: 16px; }

.jf-wa__body {
    padding: 1rem;
}
.jf-wa__bubble {
    background: #fff;
    border-radius: 0 8px 8px 8px;
    padding: 0.6rem 0.85rem;
    font-size: 0.9rem;
    color: #333;
    line-height: 1.45;
    box-shadow: 0 1px 2px rgba(0,0,0,0.12);
}

.jf-wa__footer {
    background: #fff;
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
    padding: 0.6rem 0.75rem;
    border-top: 1px solid #ddd;
}
.jf-wa__input {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0.5rem 0.65rem;
    font-size: 0.88rem;
    font-family: var(--jf-font-body);
    resize: none;
    outline: none;
    color: #333;
    line-height: 1.4;
}
.jf-wa__input:focus { border-color: #25d366; }

.jf-wa__send {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #25d366;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.2s;
}
.jf-wa__send:hover { background: #1da851; }
.jf-wa__send svg { width: 18px; height: 18px; color: #fff; }

/* ── Transición ────────────────────────────────────── */
.jf-wa-popup-enter-active,
.jf-wa-popup-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.jf-wa-popup-enter-from,
.jf-wa-popup-leave-to {
    opacity: 0;
    transform: translateY(12px) scale(0.97);
}
</style>
