<template>
    <RegistrationBase
        class="o-registration-confirm"
        :showNext="true"
        :showPrevious="true"
        nextPath="common.finish"
        @nextStep="finishRegistration"
        @previousStep="previousStep"
    >
        <template
            #title
        >
            {{ $t('registration.confirm.title') }}
        </template>

        <template
            #subtitle
        >
            {{ $t(`registration.confirm.${baseTypeFormatted}.subtitle`) }}
        </template>

        <BaseConfirm
            v-if="spacesLength"
            :base="base"
            :baseStructure="baseStructure"
            v-bind="$attrs"
        >
        </BaseConfirm>

        <FullLoaderProcessing
            v-if="showLoader"
            loaderClass="my-16"
        >
            <div class="z-over mb-6 text-2xl text-primary-600 font-bold">
                Creating your account...
            </div>

            <template #post>
                <div class="z-over mt-8">
                    <LoadingBar
                        :isTotallyFake="true"
                    >
                    </LoadingBar>
                </div>
            </template>
        </FullLoaderProcessing>
    </RegistrationBase>
</template>

<script>

// import { searchableTemplates } from '@/core/data/templates.js';
// import { getCombinedMappings } from '@/core/mappings/defaults/defaultMappingCreators.js';

import RegistrationBase from '@/components/access/RegistrationBase.vue';
import FullLoaderProcessing from '@/components/loaders/FullLoaderProcessing.vue';
import LoadingBar from '@/components/loaders/LoadingBar.vue';
import BaseConfirm from '@/components/bases/BaseConfirm.vue';

// import Explanation from '@/components/display/Explanation.vue';

export default {
    name: 'RegistrationConfirm',
    components: {
        RegistrationBase,
        FullLoaderProcessing,
        LoadingBar,
        BaseConfirm,
        // Explanation,
    },
    mixins: [
    ],
    props: {
        basesStructure: {
            type: Array,
            required: true,
        },
        showLoader: Boolean,
        base: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'previousStep',
        'finishRegistration',
        'mounted',
    ],
    data() {
        return {
            selectedSpace: null,
        };
    },
    computed: {
        savedSpaces() {
            return this.baseStructure.spaces || [];
        },
        spacesLength() {
            return this.savedSpaces.length;
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
        baseStructure() {
            return this.basesStructure[0];
        },
    },
    methods: {
        previousStep() {
            this.$emit('previousStep', 'confirm');
        },
        finishRegistration() {
            this.$emit('finishRegistration', this.basesStructure);
        },
    },
    mounted() {
        this.$emit('mounted');
    },
};
</script>

<style scoped>

.o-registration-confirm {
    @apply
        min-h-screen
    ;
}

</style>
