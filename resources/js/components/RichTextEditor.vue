<script setup lang="ts">
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const showSource = ref(false);
const sourceHtml = ref(props.modelValue ?? '');

const editor = useEditor({
    content: props.modelValue ?? '',
    extensions: [StarterKit, Underline],
    editorProps: {
        attributes: { class: 'rte-content' },
    },
    onUpdate({ editor }) {
        const html = editor.getHTML();
        sourceHtml.value = html;
        emit('update:modelValue', html);
    },
});

watch(
    () => props.modelValue,
    (val) => {
        if (!editor.value) return;
        const current = editor.value.getHTML();
        if (val !== current) {
            editor.value.commands.setContent(val ?? '', false);
            sourceHtml.value = val ?? '';
        }
    },
);

const toggleSource = () => {
    if (showSource.value) {
        editor.value?.commands.setContent(sourceHtml.value, false);
        emit('update:modelValue', sourceHtml.value);
    } else {
        sourceHtml.value = editor.value?.getHTML() ?? '';
    }
    showSource.value = !showSource.value;
};

const onSourceChange = (e: Event) => {
    const val = (e.target as HTMLTextAreaElement).value;
    sourceHtml.value = val;
    emit('update:modelValue', val);
};

onBeforeUnmount(() => editor.value?.destroy());
</script>

<template>
    <div class="rte-wrapper border rounded-3">
        <!-- Toolbar -->
        <div class="rte-toolbar border-bottom d-flex flex-wrap align-items-center gap-1 p-2">
            <!-- Párrafo y headings -->
            <div class="btn-group btn-group-sm">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ active: editor?.isActive('paragraph') }"
                    title="Párrafo"
                    @click="editor?.chain().focus().setParagraph().run()"
                >
                    P
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ active: editor?.isActive('heading', { level: 1 }) }"
                    title="Heading 1"
                    @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                >
                    H1
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ active: editor?.isActive('heading', { level: 2 }) }"
                    title="Heading 2"
                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                >
                    H2
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ active: editor?.isActive('heading', { level: 3 }) }"
                    title="Heading 3"
                    @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                >
                    H3
                </button>
            </div>

            <!-- Formato inline -->
            <div class="btn-group btn-group-sm">
                <button
                    type="button"
                    class="btn btn-outline-secondary fw-bold"
                    :class="{ active: editor?.isActive('bold') }"
                    title="Negrilla (Ctrl+B)"
                    @click="editor?.chain().focus().toggleBold().run()"
                >
                    B
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary fst-italic"
                    :class="{ active: editor?.isActive('italic') }"
                    title="Cursiva (Ctrl+I)"
                    @click="editor?.chain().focus().toggleItalic().run()"
                >
                    I
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary text-decoration-underline"
                    :class="{ active: editor?.isActive('underline') }"
                    title="Subrayado (Ctrl+U)"
                    @click="editor?.chain().focus().toggleUnderline().run()"
                >
                    U
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary text-decoration-line-through"
                    :class="{ active: editor?.isActive('strike') }"
                    title="Tachado"
                    @click="editor?.chain().focus().toggleStrike().run()"
                >
                    S
                </button>
            </div>

            <!-- Listas -->
            <div class="btn-group btn-group-sm">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ active: editor?.isActive('bulletList') }"
                    title="Lista desordenada"
                    @click="editor?.chain().focus().toggleBulletList().run()"
                >
                    <i class="bi bi-list-ul"></i>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ active: editor?.isActive('orderedList') }"
                    title="Lista ordenada"
                    @click="editor?.chain().focus().toggleOrderedList().run()"
                >
                    <i class="bi bi-list-ol"></i>
                </button>
            </div>

            <!-- Cita -->
            <div class="btn-group btn-group-sm">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :class="{ active: editor?.isActive('blockquote') }"
                    title="Cita"
                    @click="editor?.chain().focus().toggleBlockquote().run()"
                >
                    <i class="bi bi-blockquote-left"></i>
                </button>
            </div>

            <!-- Deshacer / Rehacer -->
            <div class="btn-group btn-group-sm">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :disabled="!editor?.can().undo()"
                    title="Deshacer (Ctrl+Z)"
                    @click="editor?.chain().focus().undo().run()"
                >
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    :disabled="!editor?.can().redo()"
                    title="Rehacer (Ctrl+Y)"
                    @click="editor?.chain().focus().redo().run()"
                >
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>

            <!-- Toggle HTML fuente -->
            <button
                type="button"
                class="btn btn-sm ms-auto"
                :class="showSource ? 'btn-secondary' : 'btn-outline-secondary'"
                title="Ver / editar HTML"
                @click="toggleSource"
            >
                <i class="bi bi-code-slash"></i>
                HTML
            </button>
        </div>

        <!-- Editor WYSIWYG -->
        <EditorContent v-if="!showSource" :editor="editor" />

        <!-- Editor HTML fuente -->
        <textarea
            v-else
            :value="sourceHtml"
            rows="12"
            class="form-control border-0 rounded-0 rounded-bottom font-monospace small"
            style="resize: vertical"
            @input="onSourceChange"
        ></textarea>
    </div>
</template>

<style scoped>
.rte-toolbar {
    background-color: var(--bs-light, #f8f9fa);
}

:deep(.rte-content) {
    min-height: 220px;
    padding: 0.75rem 1rem;
    outline: none;
    line-height: 1.6;
}

:deep(.rte-content > * + *) {
    margin-top: 0.5rem;
}

:deep(.rte-content ul),
:deep(.rte-content ol) {
    padding-left: 1.5rem;
}

:deep(.rte-content blockquote) {
    border-left: 3px solid #dee2e6;
    padding-left: 1rem;
    color: #6c757d;
    margin: 0;
}

:deep(.rte-content h1) { font-size: 1.6rem; font-weight: 700; }
:deep(.rte-content h2) { font-size: 1.3rem; font-weight: 600; }
:deep(.rte-content h3) { font-size: 1.1rem; font-weight: 600; }

:deep(.ProseMirror:focus) {
    outline: none;
}

:deep(.ProseMirror p.is-editor-empty:first-child::before) {
    color: #adb5bd;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}
</style>
