<template>
    <div
        class="c-stacked-icons"
        :class="colorClass"
    >
        <span
            class="fas"
            :class="[mainIcon, mainSize]"
        >
        </span>
        <div class="c-stacked-icons__circle circle-center">
            <span
                class="c-stacked-icons__sub fas"
                :class="[secondaryIcon, secondarySize]"
            >
            </span>
        </div>
    </div>
</template>

<script>

export default {
    name: 'StackedIcons',
    components: {

    },
    mixins: [
    ],
    props: {
        mainIcon: {
            type: String,
            required: true,
        },
        secondaryIcon: {
            type: String,
            required: true,
        },
        mainSize: {
            type: String,
            default: 'text-base',
        },
        secondarySize: {
            type: String,
            default: 'text-xxs',
        },
        colorClass: {
            type: String,
            default: 'text-primary-600',
        },
    },
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

.c-stacked-icons {
    @apply
        relative
    ;

    &__circle {
        bottom: 0;
        height: 13px;
        right: -8px;
        width: 13px;

        @apply
            absolute
            bg-cm-00
        ;
    }
}

</style>
