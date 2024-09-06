<template>
    <FeedbackPopup
        class="c-undo-action font-semibold"
        bgHoverColor="bg-cm-200"
        iconHoverColor="text-cm-600"
        :feedbackId="feedbackId"
    >
        <p
            v-t="message"
            class="mb-2"
        >
        </p>

        <button
            class="c-undo-action__button button bg-cm-200 text-cm-600 hover:bg-cm-100"
            type="button"
            @click="undo"
        >
            <i
                class="c-undo-action__arrow far fa-arrow-rotate-left"
            >
            </i>

            <span
                v-t="'feedback.undo'"
            >
            </span>
        </button>
    </FeedbackPopup>
</template>

<script>

import FeedbackPopup from './FeedbackPopup.vue';

export default {
    name: 'UndoAction',
    components: {
        FeedbackPopup,
    },
    mixins: [
    ],
    props: {
        customMessagePath: {
            type: String,
            default: '',
        },
        undoFunction: {
            type: Function,
            required: true,
        },
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
        message() {
            return this.customMessagePath || 'feedback.undoAction';
        },
    },
    methods: {
        undo() {
            // Make async await once adding first function
            this.undoFunction();
            this.$root.closeFeedback(this.feedbackId);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-undo-action {
    @apply
        flex
        flex-col
        items-center
        p-6
    ;

    &__button {
        @apply
            flex
            items-center
        ;

        &:hover .c-undo-action__arrow {
            transform: rotate(-180deg);
        }
    }

    &__arrow {
        transition: transform 0.2s ease-in-out;

        @apply
            mr-2
        ;
    }
}

</style>
