<template>
    <div class="c-feedback-popup">
        <CloseButton
            class="c-feedback-popup__close"
            v-bind="$attrs"
            @click="closeFeedbackPopup"
        >
        </CloseButton>

        <slot>
        </slot>
    </div>
</template>

<script>

import CloseButton from '@/components/buttons/CloseButton.vue';

export default {
    name: 'FeedbackPopup',
    components: {
        CloseButton,
    },
    mixins: [
    ],
    props: {
        feedbackId: {
            type: String,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
        closeFeedbackPopup() {
            this.$root.closeFeedback(this.feedbackId);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-feedback-popup {
    max-width: 350px;
    min-width: 200px;

    @apply
        bg-cm-00
        relative
        rounded-xl
        shadow-lg
    ;

    &__close {
        @apply
            absolute
            right-2
            top-2
        ;
    }
}

</style>
