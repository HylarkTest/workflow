<template>
    <RegistrationBase
        class="o-registration-spaces"
        :showNext="hasUses"
        :showPrevious="showPrevious"
        @nextStep="nextStep"
        @previousStep="previousStep"
    >
        <template
            #title
        >
            {{ $t('registration.spaces.title') }}
        </template>

        <template
            #subtitle
        >
            {{ $t(`registration.spaces.${baseTypeFormatted}.subtitle`) }}
        </template>

        <BaseSpaces
            :base="base"
            v-bind="$attrs"
        >
        </BaseSpaces>

    </RegistrationBase>
</template>

<script>

import BaseSpaces from '@/components/bases/BaseSpaces.vue';
import RegistrationBase from '@/components/access/RegistrationBase.vue';

export default {
    name: 'RegistrationSpaces',
    components: {
        RegistrationBase,
        BaseSpaces,
    },
    mixins: [
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
        useCases: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'previousStep',
        'nextStep',
        'mounted',
    ],
    data() {
        return {
            showPrevious: true,
            modalSpace: null,
        };
    },
    computed: {
        hasUses() {
            return !!this.useCases.length;
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
    },
    methods: {
        previousStep() {
            this.showPrevious = false;
            this.$emit('previousStep', 'spaces');
        },
        nextStep() {
            this.$emit('nextStep', 'spaces');
        },
    },
    created() {

    },
    mounted() {
        this.$emit('mounted');
    },
};
</script>

<style scoped>

.o-registration-spaces {
    @apply
        min-h-screen
    ;
}

</style>
