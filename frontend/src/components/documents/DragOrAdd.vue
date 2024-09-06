<template>
    <AttachmentButton
        ref="attachButton"
        v-bind="$attrs"
        :acceptMultiples="acceptMultiples"
        @addFile="addFile"
    >
        <ButtonEl
            class="c-drag-or-add hover:shadow-lg shadow-primary-400/20"
            :class="mainClasses"
            @click="attachFile"
        >
            <i
                class="fal fa-folder-arrow-up c-drag-or-add__icon"
            >
            </i>
            <div
                class="text-xs text-center"
            >
                Drag and drop here or
                <button
                    class="font-semibold hover:underline transition-2eio"
                    type="button"
                    @click.stop="attachFile"
                >
                    {{ chooseText }}
                </button>
            </div>
        </ButtonEl>
    </AttachmentButton>
</template>

<script>

import assistsComponentAroundAttachmentButton
    from '@/vue-mixins/common/assistsComponentAroundAttachmentButton.js';

export default {
    name: 'DragOrAdd',
    components: {

    },
    mixins: [
        assistsComponentAroundAttachmentButton,
    ],
    props: {
        size: {
            type: String,
            default: 'base',
            validator(val) {
                return ['base', 'sm'].includes(val);
            },
        },
        horizontal: Boolean,
        acceptMultiples: Boolean,
    },
    emits: [
        'addFile',
    ],
    data() {
        return {

        };
    },
    computed: {
        sizeClass() {
            return `c-drag-or-add__size--${this.size}`;
        },
        positionClass() {
            const layout = this.horizontal ? 'horizontal' : 'vertical';
            return `c-drag-or-add--${layout}`;
        },
        mainClasses() {
            return [
                this.sizeClass,
                this.positionClass,
            ];
        },
        chooseText() {
            return this.acceptMultiples ? 'choose files' : 'choose a file';
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>

.c-drag-or-add {
    transition: 0.2s ease-in-out;

    @apply
        bg-primary-100
        border-2
        border-dashed
        border-primary-600
        p-4
        rounded-lg
    ;

    &--horizontal {
        @apply
            flex
            items-center
        ;

        .c-drag-or-add__icon {
            @apply
                mb-0
                mr-2
            ;
        }
    }

    &__size--sm {
        @apply
            border
            px-4
            py-2
        ;

        .c-drag-or-add__icon {
            @apply
                mb-0
                text-xl
            ;
        }
    }

    &__icon {
        @apply
            block
            mb-2
            text-4xl
            text-center
            text-primary-700
        ;
    }
}

</style>
