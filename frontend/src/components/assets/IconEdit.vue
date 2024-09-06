<template>
    <ButtonEl
        class="c-icon-edit inline-flex items-baseline"
        @click="changeIcon"
    >
        <div
            class="c-icon-edit__icon centered mr-4 hover:shadow-lg transition-2eio"
        >
            <i
                class="fal"
                :class="symbol"
            >
            </i>
        </div>

        <button
            class="button--sm bg-cm-200 hover:shadow-md"
            type="button"
        >
            Change icon
        </button>

        <IconSearchModal
            v-if="isModalOpen"
            :selectedIcon="symbol"
            @update:selectedIcon="selectIcon"
            @closeModal="closeModal"
        >
        </IconSearchModal>
    </ButtonEl>
</template>

<script>

import IconSearchModal from '@/components/assets/IconSearchModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'IconEdit',
    components: {
        IconSearchModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        symbol: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:symbol',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        changeIcon() {
            this.openModal();
        },
        selectIcon(icon) {
            this.$emit('update:symbol', icon);
            this.closeModal();
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-icon-edit {
    &__icon {
        @apply
            bg-cm-00
            border
            border-primary-600
            border-solid
            h-12
            rounded-lg
            text-2xl
            text-primary-600
            w-12
        ;
    }
}

</style>
