<template>
    <div class="c-bar-select">
        <button
            class="c-bar-select__click"
            :class="bgColor"
            :style="rightPositioningStyle"
            type="button"
            @click="$emit('selectArea')"
        >
        </button>
        <div
            class="c-bar-select__cover"
            :class="bgColor"
            :style="fullStyle"
        >
        </div>
        <div
            v-if="isSelected"
            class="c-bar-select__active"
            :class="borderColor"
            :style="fullStyle"
        >
        </div>
    </div>
</template>

<script>

export default {
    name: 'BarSelect',
    components: {

    },
    mixins: [
    ],
    props: {
        bgColor: {
            type: String,
            required: true,
        },
        borderColor: {
            type: String,
            required: true,
        },
        offset: {
            type: String,
            default: '0px',
        },
        isSelected: Boolean,
    },
    emits: [
        'selectArea',
    ],
    data() {
        return {

        };
    },
    computed: {
        rightPositioningStyle() {
            return { right: `-${this.offset}` };
        },
        fullStyle() {
            return {
                ...this.rightPositioningStyle,
                ...this.widthExtension,
            };
        },
        widthExtension() {
            return { width: `calc(100% + ${this.offset})` };
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.c-bar-select {
    &__cover {
        @apply
            absolute
            h-full
            hidden
            rounded-lg
            top-0
            z-0
        ;
    }

    &__click {
        @apply
            absolute
            h-full
            top-0
            w-3
            z-over
        ;

        &:hover ~ .c-bar-select__cover {
            @apply
                block
                opacity-25
            ;
        }

    }

    &__active {
        @apply
            absolute
            border-2
            border-solid
            h-full
            rounded-lg
            top-0
        ;
    }
}
</style>
