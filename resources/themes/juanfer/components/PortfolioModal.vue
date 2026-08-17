<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue';
import { useContactPrefill } from '../composables/useContactPrefill';
import type { SiteSolution } from '../composables/useSite';

const props = defineProps<{
    solution: SiteSolution | null;
}>();

const emit = defineEmits<{ close: [] }>();

const lockScroll   = () => { document.body.style.overflow = 'hidden'; };
const unlockScroll = () => { document.body.style.overflow = ''; };

const onKey = (e: KeyboardEvent) => { if (e.key === 'Escape') emit('close'); };

onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => {
    document.removeEventListener('keydown', onKey);
    unlockScroll();
});

watch(() => props.solution, (val) => { if (val) { lockScroll(); } else { unlockScroll(); } });

const { set: setPrefill } = useContactPrefill();

const cotiza = () => {
    if (props.solution) setPrefill(props.solution.title);
    emit('close');
    // Espera a que el modal cierre antes de hacer scroll
    setTimeout(() => {
        const el = document.getElementById('contacto');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    }, 260);
};
</script>

<template>
    <Teleport to="body">
        <Transition name="jf-modal">
            <div
                v-if="solution"
                class="jf-pm-backdrop"
                role="dialog"
                aria-modal="true"
                :aria-label="solution.title"
                @click.self="emit('close')"
            >
                <div class="jf-pm-dialog">
                    <!-- Cabecera -->
                    <div class="jf-pm-header jf-dark-bg">
                        <div class="jf-pm-header__text">
                            <h2 class="jf-pm-title jf-heading">{{ solution.title }}</h2>
                            <p v-if="solution.description" class="jf-pm-tagline">
                                {{ solution.description }}
                            </p>
                        </div>
                        <div class="jf-pm-header__actions">
                            <button type="button" class="jf-btn" @click="cotiza">
                                Cotiza ya
                            </button>
                            <button
                                type="button"
                                class="jf-pm-close"
                                aria-label="Cerrar"
                                @click="emit('close')"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Cuerpo con portafolio -->
                    <div class="jf-pm-body">
                        <div class="jf-solution-body" v-html="solution.body" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* ── Backdrop ───────────────────────────────────────────── */
.jf-pm-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(10, 18, 40, 0.72);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
}

/* ── Dialog ─────────────────────────────────────────────── */
.jf-pm-dialog {
    background: var(--jf-bg-muted, #f5f6f8);
    border-radius: 14px;
    width: 100%;
    max-width: 860px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
}

/* ── Header ─────────────────────────────────────────────── */
.jf-pm-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem 1.75rem;
    flex-shrink: 0;
    flex-wrap: wrap;
}

.jf-pm-header__text {
    flex: 1;
    min-width: 0;
}

.jf-pm-title {
    font-size: 1.4rem;
    margin: 0 0 0.35rem;
    color: #fff;
}

.jf-pm-tagline {
    margin: 0;
    color: rgba(255, 255, 255, 0.65);
    font-size: 0.93rem;
    line-height: 1.5;
}

.jf-pm-header__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}

.jf-pm-close {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.8);
    transition: background 0.2s, color 0.2s;
    padding: 0;
}
.jf-pm-close:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}
.jf-pm-close svg {
    width: 18px;
    height: 18px;
}

/* ── Body ───────────────────────────────────────────────── */
.jf-pm-body {
    overflow-y: auto;
    padding: 1.75rem;
    flex: 1;
}

/* ── Portfolio content (same as Solutions/Show.vue) ─────── */
.jf-solution-body :deep(.jf-portfolio) {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
}

.jf-solution-body :deep(.jf-portfolio__insurer) {
    background: #fff;
    border-radius: 10px;
    padding: 1.5rem 1.5rem 1rem;
    border-top: 4px solid var(--jf-secondary);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.jf-solution-body :deep(.jf-portfolio__insurer-name) {
    font-family: var(--jf-font-heading);
    color: var(--jf-primary);
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.jf-solution-body :deep(.jf-portfolio__products) {
    list-style: none;
    padding: 0;
    margin: 0;
}

.jf-solution-body :deep(.jf-portfolio__products li) {
    padding: 0.45rem 0;
    border-bottom: 1px solid var(--jf-border, #e9ecef);
    color: #495057;
    font-size: 0.93rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.jf-solution-body :deep(.jf-portfolio__products li::before) {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--jf-secondary);
    flex-shrink: 0;
}

.jf-solution-body :deep(.jf-portfolio__products li:last-child) {
    border-bottom: none;
}

.jf-solution-body :deep(.jf-portfolio__products--cols) {
    column-count: 2;
    column-gap: 1.5rem;
}

@media (max-width: 767px) {
    .jf-pm-backdrop {
        padding: 0;
        align-items: flex-end;
    }

    .jf-pm-dialog {
        border-radius: 14px 14px 0 0;
        max-height: 92vh;
    }

    .jf-pm-header {
        padding: 1.25rem 1.25rem 1rem;
        flex-direction: column;
        gap: 0.75rem;
    }

    .jf-pm-header__actions {
        align-self: flex-end;
    }

    .jf-pm-body {
        padding: 1.25rem;
    }

    .jf-solution-body :deep(.jf-portfolio) {
        grid-template-columns: 1fr;
    }

    .jf-solution-body :deep(.jf-portfolio__products--cols) {
        column-count: 1;
    }
}

/* ── Transition ─────────────────────────────────────────── */
.jf-modal-enter-active,
.jf-modal-leave-active {
    transition: opacity 0.22s ease;
}
.jf-modal-enter-active .jf-pm-dialog,
.jf-modal-leave-active .jf-pm-dialog {
    transition: transform 0.22s ease, opacity 0.22s ease;
}
.jf-modal-enter-from,
.jf-modal-leave-to {
    opacity: 0;
}
.jf-modal-enter-from .jf-pm-dialog,
.jf-modal-leave-to .jf-pm-dialog {
    transform: translateY(20px);
    opacity: 0;
}
</style>
