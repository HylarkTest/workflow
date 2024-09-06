<template>
    <div
        class="c-displayer-icon"
    >
        <button
            :class="displayClasses"
            type="button"
            @click="openModal"
        >
            <i
                class="far"
                :class="modelFieldValue || 'fa-plus'"
            >
            </i>
        </button>

        <IconSearchModal
            v-if="isModalOpen"
            :selectedIcon="modelFieldValue"
            @update:selectedIcon="saveIcon"
            @closeModal="closeModal"
        >
        </IconSearchModal>
    </div>
</template>

<script>

import IconSearchModal from '@/components/assets/IconSearchModal.vue';

import interactsWithDisplayersSelfEdit from '@/vue-mixins/displayers/interactsWithDisplayersSelfEdit.js';
import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'DisplayerIcon',
    components: {
        IconSearchModal,
    },
    mixins: [
        interactsWithModal,
        interactsWithDisplayers,
        interactsWithDisplayersSelfEdit,
    ],
    props: {

    },
    emits: [
        'saveField',
        'update:dataValue',
    ],
    data() {
        return {
            typeKey: 'ICON',
        };
    },
    computed: {
        displayClasses() {
            if (!this.modelFieldValue) {
                return 'c-displayer-icon--empty text-cm-300 hover:bg-cm-100 transition-2eio';
            }
            if (this.combo === 1) {
                return 'text-primary-600';
            }
            const style = 'circle-center h-5 w-5';
            if (this.modelFieldValue) {
                return `${style} bg-primary-600 text-cm-00`;
            }
            return `${style} bg-primary-200 text-cm-400`;
        },
    },
    methods: {
        saveIcon(event) {
            this.closeModal();
            this.saveValue(event);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-displayer-icon {
    &--empty {
        @apply
            border
            border-cm-200
            flex
            h-8
            items-center
            justify-center
            rounded-lg
            w-8
        ;
    }
}

</style>
