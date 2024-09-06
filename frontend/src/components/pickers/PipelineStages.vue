<template>
    <div class="c-pipeline-stages">
        <button
            class="c-pipeline-stages__toggle circle-center"
            type="button"
        >
            <i
                class="far"
                :class="popupAngle"
            >
            </i>
        </button>
    </div>
</template>

<script>

export default {
    name: 'PipelineStages',
    components: {

    },
    mixins: [
    ],
    props: {
        bgColor: {
            type: String,
            default: 'gray',
        },
        stages: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            isPopupOpen: false,
        };
    },
    computed: {
        popupAngle() {
            return this.isPopupOpen ? 'fa-chevron-up' : 'fa-chevron-down';
        },
        bgClass() {
            return `c-pipeline-picker__picker--${this.bgColor}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-pipeline-stages {
    &__toggle {
        transition: 0.2s ease-in-out;
        @apply
            bg-cm-100
            h-5
            text-xs
            w-5
        ;

        &:hover {
            @apply
                bg-cm-200
            ;
        }

        &--white {
            @apply
                bg-cm-00
            ;

            &:hover {
                @apply
                    bg-primary-100
                    text-primary-600
                ;
            }
        }
    }
}

</style>
