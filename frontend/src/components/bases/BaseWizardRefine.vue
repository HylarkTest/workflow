<template>
    <div class="o-base-wizard-refine">
        <h1 class="o-creation-wizard__header">
            Next, let's personalize your new base
        </h1>

        <BaseRefine
            class="w-full"
            footerPositioningClasses="bottom-0"
            v-bind="$attrs"
        >
        </BaseRefine>
    </div>
</template>

<script>

import BaseRefine from './BaseRefine.vue';

export default {
    name: 'BaseWizardRefine',
    components: {
        BaseRefine,
    },
    mixins: [
    ],
    props: {

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

/*.o-base-wizard-refine {

} */

</style>
