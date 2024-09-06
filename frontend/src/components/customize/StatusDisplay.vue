<template>
    <div
        class="o-status-display"
        :class="'o-status-display--' + size"
        :style="{ backgroundColor: bgColor }"
    >
        <span class="font-semibold text-cm-00">
            {{ item.name }}
        </span>

        <div
            v-if="isModifiable"
            class="text-cm-00 text-sm ml-4"
        >
            <button
                class="mr-1.5 hover:text-cm-200 transition-2eio"
                type="button"
                @click="$emit('editMarker', item)"
            >
                <i
                    class="far fa-pencil-alt"
                >
                </i>
            </button>

            <button
                class="hover:text-cm-200 transition-2eio"
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
    name: 'StatusDisplay',
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

.o-status-display {
    @apply
        px-4
        py-2
        rounded-lg
    ;

    &--sm {
        @apply
            px-2
            py-1
            text-xs
        ;
    }
}

</style>
