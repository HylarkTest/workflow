<template>
    <div
        class="c-i-want-this bg-cm-100 text-xssm p-6 rounded-xl flex flex-col items-center"
    >
        <p>
            If this is a feature that want and would use, let us know!
            Hylark is growing and your feedback will help us take
            it in the best direction for our users.
        </p>

        <button
            class="bg-cm-00 button--sm mt-2 hover:bg-cm-200"
            :class="{ unclickable: disabled }"
            type="button"
            :disabled="disabled"
            @click="submitFeature"
        >
            I'd like this feature!
        </button>
    </div>
</template>

<script>

export default {
    name: 'IWantThis',
    components: {

    },
    mixins: [
    ],
    props: {
        featureMessage: {
            type: String,
            required: true,
        },
    },
    emits: [
        'wantedThis',
    ],
    data() {
        return {
            disabled: false,
        };
    },
    computed: {

    },
    methods: {
        submitFeature() {
            this.disabled = true;
            this.$emit('wantedThis');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-i-want-this {

} */

</style>
