import { ref } from 'vue';

interface Prefill {
    asunto: string;
    mensaje: string;
}

// Singleton a nivel de módulo: persiste mientras el SPA esté montado
const _prefill = ref<Prefill | null>(null);

export function useContactPrefill() {
    const set = (solutionTitle: string) => {
        _prefill.value = {
            asunto: 'cotizacion',
            mensaje: `Hola, quisiera cotizar el portafolio de ${solutionTitle}.`,
        };
    };

    const consume = (): Prefill | null => {
        const val = _prefill.value;
        _prefill.value = null;
        return val;
    };

    return { prefill: _prefill, set, consume };
}
