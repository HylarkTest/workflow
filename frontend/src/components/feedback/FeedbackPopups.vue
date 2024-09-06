<template>
    <div class="o-feedback-popups stop-blur">
        <transition-group
            name="t-fade-fast"
        >
            <div
                v-for="component in feedbackInfo"
                :key="component.id"
                class="mb-1"
            >
                <component
                    :is="component.componentName"
                    :feedbackId="component.id"
                    v-bind="component.props"
                >
                </component>
            </div>
        </transition-group>
    </div>
</template>

<script>

import UndoAction from './UndoAction.vue';
import ResponsePopup from './ResponsePopup.vue';

export default {
    name: 'FeedbackPopups',
    components: {
        UndoAction,
        ResponsePopup,
    },
    mixins: [
    ],
    props: {
        feedbackInfo: {
            type: Array,
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

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-feedback-popups {

}*/

</style>
