<template>
    <RegistrationBase
        class="o-registration-uses"
        :showNext="isAllDone"
        :showPrevious="showPrevious"
        @nextStep="$emit('nextStep', 'refine')"
        @previousStep="previousStep"
    >
        <template
            #title
        >
            {{ $t('registration.refine.title') }}
        </template>

        <!-- <template
            #subtitle
        >
            {{ $t('registration.refine.subtitle') }}
        </template> -->

        <BaseRefine
            v-if="usesLength"
            :base="base"
            :useCases="useCases"
            v-bind="$attrs"
        >

        </BaseRefine>
    </RegistrationBase>
</template>

<script>

import RegistrationBase from '@/components/access/RegistrationBase.vue';
import BaseRefine from '@/components/bases/BaseRefine.vue';

export default {
    name: 'RegistrationRefine',
    components: {
        RegistrationBase,
        BaseRefine,
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
        };
    },
    computed: {
        spaces() {
            return this.base.spaces;
        },
        usesLength() {
            return this.useCases.length;
        },
        isAllDone() {
            return this.usesMapped.every((use) => {
                return use.needsRefinement ? use.isDone : true;
            });
        },
        usesMappedOnSpaces() {
            return this.spaces.map((space) => {
                const usesMapped = _(space.uses).map((use) => {
                    const isIgnored = this.isIgnored(use);
                    const refinement = isIgnored ? null : use.refinement;
                    const hasRefinement = !!refinement;
                    const fullUse = this.useCases.find((useCase) => {
                        return useCase.val === use.val && useCase.space.id === space.id;
                    });
                    const clonedUse = _.cloneDeep(fullUse);

                    return {
                        ...clonedUse,
                        isDone: hasRefinement ? refinement.done : true,
                        needsRefinement: hasRefinement ? 1 : 0,
                        refinement,
                        tempVal: `${use.val}-${space.id}`,
                    };
                }).orderBy('needsRefinement', 'desc').value();
                return {
                    ...space,
                    uses: usesMapped,
                };
            });
        },
        usesMapped() {
            return _.flatMap(this.usesMappedOnSpaces, 'uses');
        },
    },
    methods: {
        previousStep() {
            this.showPrevious = false;
            this.$emit('previousStep', 'refine');
        },
        isIgnored(use) {
            const ignoreVal = use.ignoreIfAlsoSelected;
            return ignoreVal && !!this.useCases.find((useCase) => {
                return ignoreVal.includes(useCase.val);
            });
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

/*.o-registration-refine {
}*/

</style>
