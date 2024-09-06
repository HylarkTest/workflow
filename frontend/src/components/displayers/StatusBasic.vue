<template>
    <div
        class="c-status-basic font-semibold rounded transition-2eio"
        :class="[styleClasses, sizeClass]"
        :style="styleObj"
    >
        <template v-if="status">
            {{ status.name }}
        </template>

        <template
            v-else
        >
            {{ defaultText }}
        </template>

        <slot>
        </slot>
    </div>
</template>

<script>

export default {
    name: 'StatusBasic',
    components: {

    },
    mixins: [
    ],
    props: {
        status: {
            type: [Object, null],
            required: true,
        },
        statusStyle: {
            type: String,
            default: 'bold',
        },
        defaultText: {
            type: String,
            default: 'No status',
        },
        size: {
            type: String,
            default: 'sm',
            validator(val) {
                return ['sm', 'xs'].includes(val);
            },
        },
    },
    emits: [
    ],
    data() {
        return {

        };
    },
    computed: {
        color() {
            return this.status?.color || '#868d9a';
        },
        boldColor() {
            return this.$root.extraColorDisplay(this.color);
        },
        lightColor() {
            return this.$root.extraColorDisplay(this.color, '100');
        },
        styleObj() {
            if (this.statusStyle === 'bold') {
                return { backgroundColor: this.boldColor };
            }
            if (this.statusStyle === 'light') {
                return { backgroundColor: this.lightColor, color: this.boldColor };
            }
            return { borderColor: this.boldColor, color: this.boldColor };
        },
        styleClasses() {
            if (this.statusStyle === 'bold') {
                return 'text-cm-00';
            }
            if (this.statusStyle === 'border') {
                return 'border border-solid';
            }
            return '';
        },
        sizeClass() {
            return `c-status-basic--${this.size}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-status-basic {
    &--sm {
        @apply
            px-2
            py-1
            text-xs
        ;
    }

    &--xs {
        @apply
            px-1.5
            text-xxsxs
        ;
    }
}

</style>
