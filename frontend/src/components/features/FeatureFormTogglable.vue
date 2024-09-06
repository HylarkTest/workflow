<template>
    <div class="c-feature-form-togglable">
        <div class="flex items-center">
            <label class="header-form">
                <slot
                    name="label"
                >
                </slot>
            </label>
            <button
                v-if="!isOpenAlways"
                class="button-rounded--sm button-secondary--light m-1"
                type="button"
                @click="toggle"
            >
                {{ buttonMessage }}
            </button>
        </div>

        <template
            v-if="isOpen"
        >
            <slot></slot>
        </template>
    </div>
</template>

<script>

export default {
    name: 'FeatureFormTogglable',
    components: {

    },
    mixins: [
    ],
    props: {
        isOpenAlways: Boolean,
        isOpenByDefault: Boolean,
    },
    data() {
        return {
            isOpen: this.isOpenAlways || this.isOpenByDefault,
        };
    },
    computed: {
        buttonMessage() {
            if (this.isOpen) {
                return 'Hide';
            }
            return this.isOpenByDefault ? 'Open' : 'Add';
        },
    },
    methods: {
        toggle() {
            this.isOpen = !this.isOpen;
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.c-feature-form-togglable {

} */

</style>
