<template>
    <Modal
        containerClass="p-8 w-80"
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
                v-t="headerTextPath"
                class="font-semibold text-xl"
            >
            </p>
        </div>

        <div class="mb-8 text-center">
            <slot>
            </slot>
        </div>

        <div
            class="flex flex-col items-center"
            :class="{ unclickable: processing }"
        >
            <button
                v-t="actionButtonTextPath"
                class="text-cm-00 button text-center w-40"
                :class="actionButtonColorClasses"
                type="button"
                @click="proceedWithAction"
            >
            </button>

            <button
                v-t="cancelButtonTextPath"
                class="mt-2 text-center italic text-sm text-cm-600 hover:underline"
                type="button"
                @click="cancelAction"
            >
            </button>
        </div>
    </Modal>
</template>

<script>

export default {
    name: 'ConfirmModal',
    components: {
    },
    mixins: [
    ],
    props: {
        icon: {
            type: String,
            default: 'fal fa-circle-exclamation',
        },
        headerTextPath: {
            type: String,
            default: 'warnings.permanent',
        },
        actionButtonTextPath: {
            type: String,
            default: 'common.proceed',
        },
        actionButtonColorClasses: {
            type: String,
            default: 'bg-peach-600 hover:bg-peach-500',
        },
        cancelButtonTextPath: {
            type: String,
            default: 'common.cancel',
        },
        processing: Boolean,
    },
    emits: [
        'cancelAction',
        'proceedWithAction',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        cancelAction() {
            this.$emit('cancelAction');
        },
        proceedWithAction() {
            this.$emit('proceedWithAction');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-confirm-modal {

} */

</style>
