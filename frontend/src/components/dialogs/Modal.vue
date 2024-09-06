<template>
    <Teleport
        :to="$root.$el"
    >
        <div
            ref="root"
            v-blur.teleport
            class="c-modal"
            :class="mainTransitionClass"
            v-bind="$attrs"
            @keypress.esc="closeModal"
        >
            <button
                type="button"
                class="c-modal__close center hover--grow"
                @click="closeModal"
            >
                <i class="fal fa-times"></i>
            </button>
            <div
                class="c-modal__wrapper scale-05"
                :class="[wrapperTransitionClass, positioningClass]"
            >
                <div
                    ref="containerEl"
                    v-blur="closeModal"
                    class="c-modal__container"
                    :class="[containerClass, containerBgClass]"
                    :style="containerStyle"
                >
                    <div
                        class="c-modal__inner"
                        :class="innerClass"
                    >
                        <div
                            v-if="header"
                            class="p-4 sticky -top-1 rounded-t-xl z-over text-center"
                            :class="containerBgClass"
                        >
                            <div class="u-text c-modal__header">
                                <slot name="header">
                                    <h1 class="px-4">
                                        {{ header }}
                                    </h1>
                                </slot>
                            </div>

                            <slot
                                v-if="description"
                                name="description"
                            >
                                <p class="u-text mt-2 text-smbase text-cm-500">
                                    {{ description }}
                                </p>
                            </slot>
                        </div>

                        <slot></slot>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script>

import { computed, ref } from 'vue';
import { arrRemove } from '@/core/utils.js';

const openModals = ref([]);
const hasModalsOpen = computed(() => openModals.value.length > 0);

export {
    openModals,
    hasModalsOpen,
};

export default {
    name: 'Modal',
    provide() {
        return {
            getModal: () => this,
        };
    },
    inject: {
        getModal: {
            default: null,
        },
    },
    components: {

    },
    mixins: [
    ],
    inheritAttrs: false,
    props: {
        containerClass: {
            type: String,
            default: '',
        },
        containerStyle: {
            type: Object,
            default: () => ({}),
        },
        innerClass: {
            type: String,
            default: '',
        },
        header: {
            type: [String, Boolean],
            default: '',
        },
        description: {
            type: String,
            default: '',
        },
        positioning: {
            type: String,
            default: 'CENTER',
            validator(val) {
                return ['CENTER', 'TOP'].includes(val);
            },
        },
        modalParent: {
            type: Object,
            default: null,
        },
        containerBgClass: {
            type: String,
            default: 'bg-cm-00',
        },
        modalKey: {
            type: String,
            default: '',
        },
    },
    emits: [
        'closeModal',
    ],
    data() {
        return {
            transitionActive: false,
        };
    },
    computed: {
        wrapperTransitionClass() {
            return this.transitionActive ? 'scale-1' : '';
        },
        mainTransitionClass() {
            return this.transitionActive ? 'opacity-100' : 'opacity-0';
        },
        positioningClass() {
            return this.isCentrallyPositioned ? 'items-center' : 'items-start';
        },
        isCentrallyPositioned() {
            return this.positioning === 'CENTER';
        },
        isFirstModal() {
            return !this.getModal;
        },
    },
    methods: {
        closeModal() {
            this.transitionActive = false;
            setTimeout(() => {
                this.$emit('closeModal');
            }, 300);
        },
        getModalParent() {
            return this.modalParent || this.$parent;
        },
    },
    created() {
        openModals.value.push(this);
    },
    mounted() {
        setTimeout(() => {
            this.transitionActive = true;
        }, 5);
        if (this.isFirstModal) {
            // document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        }
    },
    beforeUnmount() {
        if (this.isFirstModal) {
            // document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        }
        openModals.value = arrRemove(openModals.value, this);
    },
};
</script>

<style scoped>

.darkmode .c-modal {
    background-color: rgba(255 255 255 / 50%);
}

.c-modal {
    background-color: rgba(0 0 0 / 50%);
    transition: 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);

    @apply
        block
        bottom-0
        fixed
        left-0
        overflow-x-hidden
        overflow-y-auto
        right-0
        top-0
        z-modal
    ;

    &__wrapper {
        /* stylelint-disable plugin/no-unsupported-browser-features */
        min-height: calc(100% - 3rem);
        /* stylelint-enable */
        transition: 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);

        /* pb larger due to potential footer */
        @apply
            flex
            justify-center
            pb-10
            pl-4
            pr-10
            pt-6
            relative
            w-screen
        ;

        @media (min-width: 768px) {
            & {
                @apply
                    px-12
                ;
            }
        }
    }

    &__close {
        @apply
            border-2
            border-solid
            border-white
            fixed
            h-7
            right-2
            rounded-full
            text-cm-00
            top-2
            w-7
            z-over
        ;

        @media (min-width: 768px) {
            & {
                @apply
                    right-5
                    top-5
                ;
            }
        }
    }

    &__container {
        @apply
            max-w-full
            relative
            rounded-xl
            shadow-center-lg
        ;
    }

    &__header {
        @apply
            font-bold
            text-2xl
        ;
    }

    &__inner {
        @apply
            flex
            flex-col
            h-full
            w-full
        ;
    }
}
</style>
