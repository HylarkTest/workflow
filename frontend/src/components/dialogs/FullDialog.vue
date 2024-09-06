<template>
    <Teleport
        :to="$root.$el"
    >
        <div
            v-blur.teleport="$parent"
            class="c-full-dialog"
            :class="[isFromModal ? 'z-modal' : 'z-dialog', mainTransitionClass]"
        >
            <div
                class="c-full-dialog__inner h-full flex flex-col"
            >
                <div
                    class="c-full-dialog__top"
                    :class="topClass"
                >
                    <div class="flex flex-1 items-center">
                        <button
                            type="button"
                            class="c-full-dialog__circle circle-center hover:bg-cm-00"
                            :class="buttonClass"
                            @click="closeAttempt"
                        >
                            <i class="fal fa-times"></i>
                        </button>
                        <div class="font-semibold text-xl w-full">
                            <slot name="title"></slot>
                        </div>
                    </div>
                    <div v-if="hasSave">
                        <button
                            type="submit"
                            :form="form"
                            class="button hover:bg-cm-00"
                            :class="buttonClass"
                        >
                        <!-- Save -->
                        </button>
                    </div>
                </div>
                <div
                    class="flex-1 min-h-0"
                    :class="paddingClass"
                >
                    <slot></slot>
                </div>
            </div>

            <LeaveConfirm
                v-if="isModalOpen"
                @closeModal="closeModal"
                @leaveScreen="leaveScreen"
                @cancelAction="closeModal"
            >
                <slot name="leaveConfirm">
                </slot>
            </LeaveConfirm>
        </div>
    </Teleport>
</template>

<script>

import LeaveConfirm from '@/components/assets/LeaveConfirm.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'FullDialog',
    inject: {
        getModal: {
            default: null,
        },
    },
    components: {
        LeaveConfirm,
    },
    mixins: [
        interactsWithModal,
    ],
    inheritAttrs: false,
    props: {
        hasSave: Boolean,
        isSaved: Boolean,
        isProcessing: Boolean,
        isActive: Boolean,
        headerColorName: {
            type: String,
            default: 'primary',
            validator: (color) => ['primary', 'secondary'].includes(color),
        },
        paddingClass: {
            type: String,
            default: 'p-10',
        },
        form: {
            type: String,
            default: '',
        },
        confirmClose: Boolean,
    },
    emits: [
        'closeFullDialog',
    ],
    data() {
        return {
            isFromModal: !!this.getModal,
            transitionActive: false,
        };
    },
    computed: {
        mainTransitionClass() {
            return this.transitionActive ? 'h-full' : 'h-0';
        },
        topClass() {
            return `bg-${this.headerColorName}-600`;
        },
        buttonClass() {
            return [
                `bg-${this.headerColorName}-500`,
                'text-cm-00',
                `hover:text-${this.headerColorName}-600`,
            ];
        },
    },
    methods: {
        closeAttempt() {
            if (this.confirmClose) {
                this.openModal();
            } else {
                this.closeDialog();
            }
        },
        leaveScreen() {
            this.closeDialog();
        },
        closeDialog() {
            this.closeModal();
            this.$emit('closeFullDialog');
        },
    },
    mounted() {
        setTimeout(() => {
            this.transitionActive = true;
        }, 5);
    },
    created() {

    },
};
</script>

<style scoped>
.c-full-dialog {
    transition: 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
    @apply
        bg-cm-00
        bottom-0
        fixed
        left-0
        outline-none
        w-full
    ;

    &__inner {
        @apply
            max-h-full
            w-screen
        ;
    }

    &__top {
        @apply
            flex
            items-center
            justify-between
            p-6
            text-cm-00
            w-full
        ;
    }

    &__circle {
        @apply
            cursor-pointer
            h-8
            mr-8
            w-8
        ;
    }
}
</style>
