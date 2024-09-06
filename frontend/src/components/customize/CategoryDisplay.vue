<template>
    <div
        class="o-category-display"
    >
        <span class="font-semibold text-cm-600 mr-4">
            {{ item.name }}
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
    name: 'CategoryDisplay',
    components: {

    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
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
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.o-category-display {
    @apply
        bg-cm-100
        px-4
        py-1
        rounded-full
    ;
}

</style>
