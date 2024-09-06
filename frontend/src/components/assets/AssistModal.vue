<template>
    <Modal
        class="c-assist-modal"
        containerClass="p-8 w-96"
        v-bind="$attrs"
    >
        <div
            class="text-center mb-5"
        >
            <i
                class="text-6xl mb-3"
                :class="icon"
            >
            </i>
            <p
                class="font-semibold text-xl"
            >
                {{ headerText }}
            </p>
        </div>

        <div>
            <slot>
            </slot>
        </div>
    </Modal>
</template>

<script>

import { warn } from 'vue';

export default {
    name: 'AssistModal',
    components: {

    },
    mixins: [
    ],
    props: {
        icon: {
            type: String,
            default: 'fal fa-circle-info',
        },
        headerTextPath: {
            type: String,
            default: '',
        },
        headerTextString: {
            type: String,
            default: '',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        headerText() {
            return this.headerTextPath ? this.$t(this.headerTextPath) : this.headerTextString;
        },
    },
    methods: {

    },
    created() {
        if (!this.headerTextPath && !this.headerTextString) {
            warn('Add a title to this assist modal');
        }
    },
};
</script>

<style scoped>

/*.c-assist-modal {

} */

</style>
