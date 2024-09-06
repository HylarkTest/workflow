<template>
    <button
        type="button"
        class="c-icon-button button"
        :class="[colorClasses, textColorClass, disabledClass]"
        :disabled="disabled"
    >
        <div
            v-if="icon"
            :class="marginClass"
        >
            <i :class="icon"></i>
        </div>
        <p
            v-if="showText"
            class="c-icon-button__word"
        >
            {{ text }}
        </p>
    </button>
</template>

<script>
export default {

    name: 'IconButton',
    components: {

    },
    mixins: [
    ],
    props: {
        icon: {
            type: [String, Boolean],
            default: 'fas fa-plus-square',
        },
        text: {
            type: String,
            default: 'New',
        },
        colorClasses: {
            type: String,
            default: 'bg-primary-600 hover:bg-primary-500',
        },
        textColorClass: {
            type: String,
            default: 'text-cm-00',
        },
        hideText: Boolean,
        disabled: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        marginClass() {
            return this.showText && this.icon ? 'mr-3' : '';
        },
        showText() {
            return this.text && !this.hideText;
        },
        disabledClass() {
            return { unclickable: this.disabled };
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.c-icon-button {
    @apply
        flex
        items-center
    ;

    /*&__padding {
        padding-bottom: 6px;
        padding-top: 6px;

        @apply
            px-4
        ;
    }*/

    &__word {
        @apply
            font-semibold
            text-sm
        ;
    }
}
</style>
