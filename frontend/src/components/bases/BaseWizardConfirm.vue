<template>
    <div class="o-base-wizard-confirm">

        <h1 class="o-creation-wizard__header">
            Meet your new base
        </h1>

        <h3 class="o-creation-wizard__description mb-6 max-w-xl text-center">
            {{ $t('registration.confirm.collaborative.subtitle') }}
        </h3>

        <BaseConfirm
            class="w-full"
            v-bind="$attrs"
        >
        </BaseConfirm>
    </div>
</template>

<script>

import BaseConfirm from '@/components/bases/BaseConfirm.vue';

export default {
    name: 'BaseWizardConfirm',
    components: {
        BaseConfirm,
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

/*.o-base-wizard-confirm {

} */

</style>
