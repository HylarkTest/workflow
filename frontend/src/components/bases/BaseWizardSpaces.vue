<template>
    <div class="o-base-wizard-spaces">
        <h1 class="o-creation-wizard__header">
            Arrange your spaces
        </h1>

        <h3 class="o-creation-wizard__description mb-6 max-w-xl text-center">
            {{ $t('registration.spaces.collaborative.subtitle') }}
        </h3>

        <BaseSpaces
            class="w-full"
            v-bind="$attrs"
        >
        </BaseSpaces>
    </div>
</template>

<script>

import BaseSpaces from './BaseSpaces.vue';

export default {
    name: 'BaseWizardSpaces',
    components: {
        BaseSpaces,
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

/*.o-base-wizard-spaces {

} */

</style>
