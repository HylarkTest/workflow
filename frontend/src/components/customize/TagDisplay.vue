<template>
    <div
        class="o-tag-display text-cm-00 rounded-full"
        :class="'o-tag-display--' + size"
        :style="{ backgroundColor: bgColor, color: textColor }"
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
    name: 'TagDisplay',
    components: {

    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
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
        bgColor() {
            return this.$root.extraColorDisplay(this.item.color, '100');
        },
        textColor() {
            return this.$root.extraColorDisplay(this.item.color);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-tag-display {
    @apply
        flex
        items-center
        px-4
        py-2
        text-sm
    ;

    &--sm {
        @apply
            px-2
            py-0.5
            text-xs
        ;

        .o-tag-display__square {
            @apply
                h-3
                rounded
                w-3
            ;
        }
    }

    &__square {
        @apply
            h-4
            mr-2
            rounded-md
            w-4
        ;
    }
}

</style>
