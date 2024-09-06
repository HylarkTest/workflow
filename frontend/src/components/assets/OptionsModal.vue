<template>
    <Modal
        class="c-options-modal"
        containerClass="p-4 w-80"
        v-bind="$attrs"
        @closeModal="$emit('closeModal')"
    >
        <div class="c-options-modal__content">
            <div
                v-if="mainInfo.icon"
                class="rounded-full circle-center bg-cm-100 h-16 w-16 mb-4"
            >
                <i
                    class="far text-primary-600 text-3xl"
                    :class="mainInfo.icon"
                >
                </i>
            </div>
            <h1
                v-t="mainInfo.title"
                class="text-lg font-bold text-center mb-2 leading-tight"
            >
            </h1>
            <h2
                v-t="mainInfo.subtitle"
                class="text-sm text-center mb-6"
            >
            </h2>

            <div class="flex flex-col items-center mb-2">
                <button
                    v-for="(button, index) in buttons"
                    :key="button.val"
                    v-t="button.textPath"
                    class="button text-center w-40"
                    :class="buttonClass(index)"
                    type="button"
                    @click="triggerAction(button)"
                >
                </button>
            </div>

            <button
                v-t="'common.cancel'"
                class="mt-2 text-center italic text-sm text-cm-600 hover:underline"
                type="button"
                @click="cancelAction"
            >
            </button>
        </div>
    </Modal>
</template>

<script>

import Modal from '@/components/dialogs/Modal.vue';

export default {
    name: 'OptionsModal',
    components: {
        Modal,
    },
    mixins: [
    ],
    props: {
        mainInfo: {
            type: Object,
            required: true,
            validator(info) {
                return _.has(info, 'icon') // Empty string if unused
                    && _.has(info, 'title')
                    && _.has(info, 'subtitle');
            },
        },
        buttons: {
            type: Array,
            required: true,
            validator(buttons) {
                return _.every(buttons, (button) => {
                    return _.has(button, 'val')
                        && _.has(button, 'textPath');
                });
            },
        },
    },
    emits: [
        'cancelAction',
        'triggerAction',
        'closeModal',
    ],
    data() {
        return {

        };
    },
    computed: {
        buttonsLength() {
            return this.buttons.length;
        },
    },
    methods: {
        cancelAction() {
            this.$emit('cancelAction');
        },
        buttonClass(index) {
            let classes = '';
            if (index === 0) {
                classes = classes.concat(' bg-primary-600 text-cm-00 hover:bg-primary-500');
            } else {
                classes = classes.concat(' bg-cm-100 hover:bg-cm-200');
            }
            if (index !== (this.buttonsLength - 1)) {
                classes = classes.concat(' mb-2');
            }
            return classes;
        },
        triggerAction(button) {
            this.$emit('triggerAction', button.val);
            this.$emit('closeModal');
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-options-modal {

    &__content {
        @apply
            flex
            flex-col
            items-center
        ;
    }
}

</style>
