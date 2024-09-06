<template>
    <div
        class="o-stage-display"
        :class="[positionClass, 'o-stage-display--' + size]"
        :style="{ backgroundColor: lightColor, color: boldColor }"
    >
        <span class="font-semibold">
            {{ item.name }}
        </span>

        <div
            v-if="isModifiable"
            class="text-sm ml-4"
        >
            <button
                class="mr-1.5 hover:opacity-75 transition-2eio"
                type="button"
                @click="$emit('editMarker', item)"
            >
                <i
                    class="far fa-pencil-alt"
                >
                </i>
            </button>

            <button
                class="hover:opacity-75 transition-2eio"
                type="button"
                @click="$emit('deleteMarker', item)"
            >
                <i
                    class="far fa-trash-alt"
                >
                </i>
            </button>
        </div>
    </div>
</template>

<script>

export default {
    name: 'StageDisplay',
    components: {

    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        index: {
            type: Number,
            default: 0,
        },
        markerCount: {
            type: [Number, null],
            default: null,
        },
        isModifiable: Boolean,
        size: {
            type: String,
            default: 'md',
            validator(val) {
                return ['sm', 'md'].includes(val);
            },
        },
    },
    emits: [
        'deleteMarker',
        'editMarker',
    ],
    data() {
        return {

        };
    },
    computed: {
        boldColor() {
            return this.$root.extraColorDisplay(this.item.color);
        },
        lightColor() {
            return this.$root.extraColorDisplay(this.item.color, '100');
        },
        isLast() {
            if (this.markerCount) {
                return (this.markerCount - 1) === this.index;
            }
            return false;
        },
        positionClass() {
            if (this.index === 0) {
                return 'o-stage-display--first';
            }
            if (this.isLast) {
                return 'o-stage-display--last';
            }
            return '';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-stage-display {
    /* stylelint-disable-next-line */
    clip-path: polygon(95% 0, 100% 50%, 95% 100%, 0% 100%, 5% 50%, 0% 0%);

    @apply
        px-5
        py-2
    ;

    &--sm {
        @apply
            px-3
            py-0.5
            text-xs
        ;
    }

    &--first {
        /* stylelint-disable-next-line */
        clip-path: polygon(95% 0, 100% 50%, 95% 100%, 0% 100%, 0% 0%);
    }

    &--last {
        /* stylelint-disable-next-line */
        clip-path: polygon(95% 0, 95% 100%, 0% 100%, 5% 50%, 0% 0%);
    }
}

</style>
