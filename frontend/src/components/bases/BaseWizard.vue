<template>
    <CreationWizard
        class="o-new-base-wizard"
        :seeBack="seeBack"
        :seeNext="seeNext"
        :nextTextPath="nextText"
        :processing="processing"
        @goBack="goBack"
        @goNext="goNext"
    >
        <template
            #processingText
        >
            Creating your new base...
        </template>

        <component
            v-if="canCreateNewBase"
            :is="stepComponent"
            v-model:base="base"
            :useCases="useCases"
            :baseStructure="baseStructure"
            contextLabel="CREATE"
            class="flex flex-col items-center"
            @closeFullDialog="$emit('closeFullDialog')"
            @registerWithoutUse="createWithoutUse"
            @pressedEnter="pressedEnter"
        >
        </component>

        <div
            v-else
        >
            <h1 class="o-creation-wizard__prompt">
                You've reached the maximum number of bases available per account
            </h1>

            <h2 class="o-creation-wizard__description text-center">
                If you wish to add a new collaborative base, you will need to leave one of your existing bases.
            </h2>
        </div>

        <template
            v-if="onUses"
            #middle
        >
            <div class="flex-1 flex items-center flex-col">
                <div class="flex gap-3 mb-1">
                    <ButtonEl
                        v-for="use in selectedUses"
                        :key="use.val"
                        class="relative"
                        @click="toggleUse(use)"
                    >
                        <UseMini
                            :use="use"
                            :base="base"
                        >

                        </UseMini>
                        <ClearButton
                            positioningClass="-top-1 -right-1 absolute"
                        >
                        </ClearButton>
                    </ButtonEl>
                </div>

                <div class="flex flex-col items-center">
                    <div class="font-medium text-xs text-cm-500 flex">
                        Selected uses

                        <div class="ml-2">
                            <span
                                class="font-bold text-primary-600"
                            >
                                {{ selectedUsesLength }}
                            </span> / {{ maxChoices }}
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </CreationWizard>
</template>

<script>

import BaseWizardStart from './BaseWizardStart.vue';
import BaseWizardUses from './BaseWizardUses.vue';
import BaseWizardSpaces from './BaseWizardSpaces.vue';
import BaseWizardRefine from './BaseWizardRefine.vue';
import BaseWizardConfirm from './BaseWizardConfirm.vue';
import BaseWizardDone from './BaseWizardDone.vue';
import CreationWizard from '@/components/customize/CreationWizard.vue';
import UseMini from '@/components/access/UseMini.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

import interactsWithWizardBasic from '@/vue-mixins/interactsWithWizardBasic.js';

import {
    getFullStructure,
} from '@/core/mappings/templates/helpers.js';
import uses from '@/core/mappings/templates/uses.js';
import { arrRemoveId } from '@/core/utils.js';
import { createBase } from '@/core/repositories/baseRepository.js';
import { maxBases } from '@/core/data/bases.js';

const defaultSpacesArr = [
    {
        id: 1,
        name: 'Space 1',
        uses: [],
    },
    {
        id: 2,
        name: 'Space 2',
        uses: [],
    },
];
export default {
    name: 'BaseWizard',
    components: {
        CreationWizard,
        BaseWizardStart,
        BaseWizardUses,
        BaseWizardDone,
        BaseWizardSpaces,
        BaseWizardRefine,
        BaseWizardConfirm,
        UseMini,
        ClearButton,
    },
    mixins: [
        interactsWithWizardBasic,
    ],
    props: {

    },
    emits: [
        'closeFullDialog',
    ],
    data() {
        return {
            base: {
                name: '',
                image: '',
                baseType: 'COLLABORATIVE',
                spaces: defaultSpacesArr,
            },
            currentStep: 'START',
            maxChoices: 3,
        };
    },
    computed: {
        contentStructure() {
            return getFullStructure(this.spacesArr);
        },
        baseStructure() {
            return {
                ...this.base,
                ...this.contentStructure,
            };
        },
        hasName() {
            return this.base.name;
        },
        hasUseCases() {
            return this.useCases.length;
        },
        stepComponent() {
            return this.currentStepObj.component || `BaseWizard${this.stepPascal}`;
        },
        onUses() {
            return this.currentStep === 'USES';
        },
        stepAfterSpaces() {
            if (this.noRefinementsNeeded) {
                return 'CONFIRM';
            }
            return 'REFINE';
        },
        stepBeforeConfirm() {
            if (this.noRefinementsNeeded) {
                return 'SPACES';
            }
            return 'REFINE';
        },
        steps() {
            return [
                {
                    step: 'START',
                    hideBack: true,
                    seeNext: this.hasName,
                },
                {
                    step: 'USES',
                    seeNext: this.hasUseCases,
                },
                {
                    step: 'SPACES',
                    seeNext: true,
                    goNext: this.stepAfterSpaces,
                },
                {
                    step: 'REFINE',
                    seeNext: this.isRefineCompleted,
                },
                {
                    step: 'CONFIRM',
                    seeNext: true,
                    goNext: 'DONE',
                    goBack: this.stepBeforeConfirm,
                    nextText: 'finish',
                },
                {
                    step: 'DONE',
                    seeNext: true,
                    nextText: 'done',
                    hideBack: true,
                    emitEvent: 'closeFullDialog',
                },
            ];
        },
        useCases() {
            // Adjusting to take what is stored and merge with defaults
            // so that we do not need to store everything
            // and to provide easy reference to indexes and other info
            return _(this.spacesArr).flatMap((space, spaceIndex) => {
                return space.uses.map((use, useIndex) => {
                    const defaultUse = _.find(uses, { val: use.val });
                    return {
                        ...defaultUse,
                        ...use,
                        useIndex,
                        space,
                        spaceIndex,
                    };
                });
            }).value();
        },
        spacesArr() {
            return this.base.spaces;
        },
        selectedUsesLength() {
            return this.selectedUses.length;
        },
        selectedUses() {
            return _.uniqBy(this.useCases, 'val');
        },
        isRefineCompleted() {
            return this.usesMapped.every((use) => {
                return use.needsRefinement ? use.isDone : true;
            });
        },
        usesMapped() {
            return _.flatMap(this.usesMappedOnSpaces, 'uses');
        },
        usesMappedOnSpaces() {
            return this.spacesArr.map((space) => {
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
        noRefinementsNeeded() {
            return _(this.useCases).every((use) => {
                return !use.refinement || this.isIgnored(use);
            });
        },
        user() {
            return this.$root.authenticatedUser;
        },
        bases() {
            return this.user.allBases();
        },
        canCreateNewBase() {
            return this.bases.length < maxBases;
        },
    },
    methods: {
        async goNext(nextStep) {
            // When an automatic step forward, delay a bit so the user has a moment to see their selection
            const time = nextStep ? 400 : 0;
            setTimeout(async () => {
                if (this.nextAction === 'DONE') {
                    this.processing = true;
                    try {
                        await createBase(this.baseStructure);
                        this.currentStep = this.nextAction;
                    } finally {
                        this.forcedNextAction = null;
                        this.processing = false;
                    }
                } else {
                    const emit = this.currentStepObj.emitEvent;
                    if (emit) {
                        this.$emit(emit);
                    } else {
                        this.currentStep = this.nextAction;
                    }
                }
            }, time);
        },
        isIgnored(use) {
            const ignoreVal = use.ignoreIfAlsoSelected;
            return ignoreVal && !!this.useCases.find((useCase) => {
                return ignoreVal.includes(useCase.val);
            });
        },
        createWithoutUse() {
            this.forcedNextAction = 'DONE';
            this.goNext();
        },
        selectedUse(use) {
            return _.filter(this.useCases, { val: use.val });
        },
        toggleUse(use) {
            const selectedUseArr = this.selectedUse(use);

            if (selectedUseArr.length) {
                selectedUseArr.forEach((selectedUseObj) => {
                    const spaceIndex = selectedUseObj.spaceIndex;
                    this.base.spaces[spaceIndex].uses = arrRemoveId(this.base.spaces[spaceIndex].uses, use.val, 'val');
                });
            } else {
                const newUse = {
                    val: use.val,
                    refinement: use.refinement,
                };
                this.base.spaces[0].uses.push(newUse);
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-new-base-wizard {

} */

</style>
