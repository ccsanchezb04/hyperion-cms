<script setup lang="ts">
import { computed, ref } from 'vue';

interface MediaItem {
    id: number;
    url: string;
}

const props = defineProps<{
    availableMedia: MediaItem[];
    modelValue: number[];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number[]];
}>();

const showLibrary = ref(false);

const selectedMedia = computed(() =>
    props.availableMedia.filter((m) => props.modelValue.includes(m.id)),
);

const unselectedMedia = computed(() =>
    props.availableMedia.filter((m) => !props.modelValue.includes(m.id)),
);

const add = (id: number) => {
    if (!props.modelValue.includes(id)) {
        emit('update:modelValue', [...props.modelValue, id]);
    }
};

const remove = (id: number) => {
    emit('update:modelValue', props.modelValue.filter((x) => x !== id));
};
</script>

<template>
    <div>
        <!-- Selected images -->
        <div v-if="selectedMedia.length" class="d-flex flex-wrap gap-2 mb-2">
            <div
                v-for="m in selectedMedia"
                :key="m.id"
                class="position-relative"
                style="width: 90px; height: 70px"
            >
                <img
                    :src="m.url"
                    class="rounded border w-100 h-100"
                    style="object-fit: cover"
                    :alt="`media-${m.id}`"
                />
                <button
                    type="button"
                    class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 lh-1"
                    style="width: 20px; height: 20px; font-size: 0.7rem"
                    @click="remove(m.id)"
                    title="Quitar"
                >
                    ×
                </button>
            </div>
        </div>
        <p v-else class="text-body-secondary small mb-2">Sin imágenes seleccionadas.</p>

        <!-- Toggle library -->
        <button
            type="button"
            class="btn btn-sm btn-outline-secondary"
            @click="showLibrary = !showLibrary"
        >
            {{ showLibrary ? 'Ocultar biblioteca' : 'Seleccionar de la biblioteca' }}
        </button>

        <!-- Library grid -->
        <div v-if="showLibrary" class="mt-2 border rounded p-2" style="max-height: 260px; overflow-y: auto">
            <p v-if="!availableMedia.length" class="text-body-secondary small mb-0">
                No hay imágenes en la biblioteca. Súbelas en
                <a href="/admin/media" target="_blank">Media</a>.
            </p>
            <div v-else class="d-flex flex-wrap gap-2">
                <div
                    v-for="m in availableMedia"
                    :key="m.id"
                    class="position-relative"
                    style="width: 90px; height: 70px; cursor: pointer"
                    :title="modelValue.includes(m.id) ? 'Quitar' : 'Agregar'"
                    @click="modelValue.includes(m.id) ? remove(m.id) : add(m.id)"
                >
                    <img
                        :src="m.url"
                        class="rounded border w-100 h-100"
                        style="object-fit: cover"
                        :class="{ 'opacity-50': modelValue.includes(m.id) }"
                        :alt="`media-${m.id}`"
                    />
                    <div
                        v-if="modelValue.includes(m.id)"
                        class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded"
                        style="background: rgba(0,0,0,0.3)"
                    >
                        <i class="bi bi-check-circle-fill text-white fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
