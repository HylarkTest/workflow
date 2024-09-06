<template>
    <div
        class="c-pipeline-basic font-semibold inline-flex items-center"
        :class="sizeClass"
        :style="{ backgroundColor: bgColor, color: textColor }"
    >

        <ClearButton
            v-if="!hideRemove"
            positioningClass="mr-2"
            @click.stop="$emit('removePipeline', pipeline)"
        >
        </ClearButton>

        {{ pipeline.name }}
    </div>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';

export default {
    name: 'PipelineBasic',
    components: {
        ClearButton,
    },
    mixins: [
    ],
    props: {
        pipeline: {
            type: Object,
            required: true,
        },
        hideRemove: Boolean,
        size: {
            type: String,
            default: 'sm',
            validator(val) {
                return ['sm', 'xs'].includes(val);
            },
        },
    },
    emits: [
        'removePipeline',
    ],
    data() {
        return {

        };
    },
    computed: {
        color() {
            return this.pipeline?.color;
        },
        textColor() {
            return this.$root.extraColorDisplay(this.color);
        },
        bgColor() {
            return this.$root.extraColorDisplay(this.color, '100');
        },
        sizeClass() {
            return `c-pipeline-basic--${this.size}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-pipeline-basic {
    /* stylelint-disable-next-line */
    clip-path: polygon(95% 0, 100% 50%, 95% 100%, 0% 100%, 5% 50%, 0% 0%);

    &--first {
        /* stylelint-disable-next-line */
        clip-path: polygon(95% 0, 100% 50%, 95% 100%, 0% 100%, 0% 0%);
    }

    &--last {
        /* stylelint-disable-next-line */
        clip-path: polygon(95% 0, 95% 100%, 0% 100%, 5% 50%, 0% 0%);
    }

    &--sm {
        @apply
            px-3
            py-1
            text-xs
        ;
    }

    &--xs {
        /* stylelint-disable-next-line */
        clip-path: polygon(85% 0, 100% 50%, 85% 100%, 0% 100%, 10% 50%, 0% 0%);

        @apply
            px-2
            text-xxsxs
        ;

        &.c-pipeline-basic {
            &--first {
                /* stylelint-disable-next-line */
                clip-path: polygon(85% 0, 100% 50%, 85% 100%, 0% 100%, 0% 0%);
            }

            &--last {
                /* stylelint-disable-next-line */
                clip-path: polygon(85% 0, 85% 100%, 0% 100%, 10% 50%, 0% 0%);
            }
        }
    }
}

</style>
