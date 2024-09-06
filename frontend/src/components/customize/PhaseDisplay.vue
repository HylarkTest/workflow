<template>
    <div
        class="o-phase-display"
        :class="bgClass"
    >
        <div
            class="h-full w-2 absolute top-0 left-0"
            :style="{ backgroundColor: bgColor }"
        >
            &nbsp;
        </div>

        <span class="font-semibold text-cm-600 mr-4">
            {{ phase.name }}
        </span>

        <div class="text-cm-400 text-sm">
            <button
                class="mr-1.5 hover:text-primary-600 transition-2eio"
                type="button"
                @click="$emit('editMarker', item)"
            >
                <i
                    class="far fa-pencil-alt"
                >
                </i>
            </button>

            <button
                class="hover:text-primary-600 transition-2eio"
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
    name: 'PhaseDisplay',
    components: {

    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        bgClass: {
            type: String,
            default: 'bg-cm-00',
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

.o-phase-display {
    @apply
        pl-6
        pr-4
        py-1
        relative
        rounded-r-lg
    ;
}

</style>
