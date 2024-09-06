<template>
    <div class="o-registration-page">
        <transition
            :css="false"
            @leave="leave"
            @enter="enter"
        >
            <RegistrationInitial
                v-show="step === 'initial'"
                ref="initial"
                class="o-registration-page__main"
                @join="join"
                @mounted="childMounted"
            >
            </RegistrationInitial>
        </transition>

        <div>
            <div
                v-if="step !== 'initial' && transitioningFrom !== 'initial'"
            >
                <RegistrationSteps
                    :currentStepVal="step"
                    :selectedSteps="selectedSteps"
                >
                    <div
                        v-if="showBaseNameDisplay"
                        class="absolute bottom-2 left-2 font-semibold py-0.5 px-2 rounded"
                        :class="baseTypeClasses"
                    >
                        {{ baseNameDisplay }}
                    </div>

                    <button
                        v-if="!inMobileApp"
                        class="absolute top-2 right-4 button--sm button-primary--light z-nav"
                        type="button"
                        :title="$t('common.logOut')"
                        @click="signOut"
                    >
                        <i
                            class="far fa-arrow-right-from-bracket"
                        >
                        </i>
                    </button>
                </RegistrationSteps>
            </div>

            <div>
                <transition-group
                    :css="false"
                    @leave="leave"
                    @enter="enter"
                >
                    <RegistrationStart
                        v-if="shouldRender('start')"
                        v-show="shouldShow('start')"
                        v-model:base="base"
                        @nextStep="advanceStep"
                        @mounted="childMounted"
                    >
                    </RegistrationStart>

                    <RegistrationUses
                        v-if="shouldRender('uses')"
                        v-show="shouldShow('uses')"
                        key="uses"
                        ref="uses"
                        v-model:base="bases[0]"
                        :useCases="useCases"
                        :preSelectedUses="preSelectedUses"
                        @previousStep="reverseStep"
                        @nextStep="advanceStep"
                        @registerWithoutUse="registerWithoutUse"
                        @mounted="childMounted"
                    >
                    </RegistrationUses>

                    <RegistrationSpaces
                        v-if="shouldRender('spaces')"
                        v-show="shouldShow('spaces')"
                        ref="spaces"
                        key="spaces"
                        v-model:base="bases[0]"
                        :useCases="useCases"
                        @nextStep="advanceStep"
                        @previousStep="reverseStep"
                        @mounted="childMounted"
                    >
                    </RegistrationSpaces>

                    <RegistrationRefine
                        v-if="shouldRender('refine')"
                        v-show="shouldShow('refine')"
                        key="refine"
                        ref="refine"
                        v-model:base="bases[0]"
                        :useCases="useCases"
                        @nextStep="advanceStep"
                        @previousStep="reverseStep"
                        @mounted="childMounted"
                    >
                    </RegistrationRefine>

                    <RegistrationConfirm
                        v-if="shouldRender('confirm')"
                        v-show="shouldShow('confirm')"
                        ref="confirm"
                        key="confirm"
                        v-model:base="bases[0]"
                        :basesStructure="basesStructure"
                        :showLoader="showFinalLoader"
                        @finishRegistration="finishRegistration"
                        @previousStep="reverseStep"
                        @mounted="childMounted"
                    >
                    </RegistrationConfirm>
                </transition-group>
            </div>
        </div>
    </div>
</template>

<script>

import { smoothScroll } from '@/core/helpers/scrolling.js';
import {
    getFullStructure,
} from '@/core/mappings/templates/helpers.js';

import RegistrationInitial from '@/components/access/RegistrationInitial.vue';
import RegistrationStart from '@/components/access/RegistrationStart.vue';
import RegistrationUses from '@/components/access/RegistrationUses.vue';
import RegistrationRefine from '@/components/access/RegistrationRefine.vue';
import RegistrationSpaces from '@/components/access/RegistrationSpaces.vue';
import RegistrationConfirm from '@/components/access/RegistrationConfirm.vue';
import RegistrationSteps from '@/components/access/RegistrationSteps.vue';
import saveState from '@/vue-mixins/saveState.js';
import { bootstrapUser, logout } from '@/core/auth.js';
import { reportUnhandledValidationError } from '@/http/exceptionHandler.js';
import uses from '@/core/mappings/templates/uses.js';

function buildDefaultSpacesArray(preSelectedUses) {
    return [
        {
            id: 1,
            name: 'Space 1',
            // Flat map to filter and map at the same time
            uses: preSelectedUses.flatMap((use) => {
                const useCase = _.find(uses, { val: use });
                return useCase ? [_.pick(useCase, ['val', 'refinement'])] : [];
            }),
        },
        {
            id: 2,
            name: 'Space 2',
            uses: [],
        },
    ];
}

const routesThatNeedUses = ['register.refine', 'register.spaces', 'register.confirm'];

const personalBaseName = 'My base';
const collaborativeBaseName = 'Collaborative base';

export default {
    name: 'RegistrationPage',
    components: {
        RegistrationInitial,
        RegistrationStart,
        RegistrationUses,
        RegistrationRefine,
        RegistrationSteps,
        // RegistrationTemplates,
        RegistrationSpaces,
        RegistrationConfirm,
    },
    mixins: [
        saveState,
    ],
    props: {

    },
    data() {
        const preSelectedUses = this.$route.query.uses?.split(',') || [];
        return {
            leavingDone: null,
            stepsPath: 'start',
            // stepsPath: 'uses',
            bases: [
                {
                    id: 1,
                    baseType: 'PERSONAL',
                    spaces: buildDefaultSpacesArray(preSelectedUses),
                    name: personalBaseName,
                },
            ],
            transitioningFrom: null,
            enteringDone: null,
            enteringEl: null,
            showFinalLoader: false,
            // This is also used to order the uses
            preSelectedUses,
            inMobileApp: this.$route.query.mobileapp === 'true',
        };
    },
    saveState: {
        store: 'server',
        propertiesForSave: [
            { key: 'bases', deep: true },
        ],
        onSaveStateLoad(key, value, original) {
            if (key === 'bases') {
                if (this.preSelectedUses.length) {
                    return original;
                }
                const firstBase = value[0];
                const spacesArr = firstBase.spaces.map((space) => {
                    // Using flat map to filter and map at the same time.
                    const usesVal = space.uses.flatMap((val) => {
                        if (val.refinement) {
                            const adjustedRefinement = this.adjustRefinementForChanges(val);
                            if (!adjustedRefinement) {
                                this.goBackToStartingPage();
                                return [];
                            }

                            return [{
                                ...val,
                                refinement: adjustedRefinement,
                            }];
                        }
                        return val;
                    });

                    return {
                        ...space,
                        uses: usesVal,
                    };
                });
                return [{
                    ...firstBase,
                    spaces: spacesArr,
                }];
            }
            return value;
        },
        afterSavedStateLoaded() {
            const routeName = this.$route.name;
            if (routesThatNeedUses.includes(routeName) && !this.useCases.length) {
                const startingRoute = 'register.start';
                // const startingRoute = 'register.uses';
                this.$router.replace({ name: startingRoute });
            }
        },
    },
    computed: {
        selectedSteps() {
            return this[`${this.stepsPath}Steps`];
        },
        routeName() {
            return this.$route.name;
        },
        // The stage of registration. Stages are defined in `usesSteps`
        step() {
            if (this.routeName === 'register') {
                return 'initial';
            }
            return this.routeName.includes('register') && this.routeName.split('.')[1];
        },
        contentStructure() {
            return getFullStructure(this.spacesArr);
        },
        basesStructure() {
            return [
                {
                    ...this.base,
                    ...this.contentStructure,
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
        noRefinementsNeeded() {
            return _(this.useCases).every((use) => {
                return !use.refinement || this.isIgnored(use);
            });
        },
        startSteps() {
            const steps = [
                'start',
                'uses',
                'spaces',
                'refine',
                'confirm',
            ];
            if (this.noRefinementsNeeded) {
                return _.without(steps, 'refine');
            }
            return steps;
        },
        // usesSteps() {
        //     const steps = [
        //         'uses',
        //         'spaces',
        //         'refine',
        //         'confirm',
        //     ];
        //     if (this.noRefinementsNeeded) {
        //         return _.without(steps, 'refine');
        //     }
        //     return steps;
        // },
        spacesArr() {
            return this.bases[0]?.spaces;
        },
        base: {
            get() {
                return this.bases[0];
            },
            set(val) {
                // Val should be the full object
                this.bases[0] = val;
            },
        },
        baseType() {
            return this.base.baseType;
        },
        baseNameDisplay() {
            return this.base.name;
        },
        baseTypeClasses() {
            const color = this.baseType === 'PERSONAL' ? 'gold' : 'azure';
            return `text-${color}-600 bg-${color}-100`;
        },
        showBaseNameDisplay() {
            return this.step !== 'start';
            // return false;
        },
    },
    methods: {
        // By default Vue transitions will destroy the component before starting
        // the transition so the component is no longer running even though the
        // template is still rendered in the DOM. This is a problem as the
        // scrolling behaviour in one of the templates requires that the
        // JavaScript continue running while it is scrolling.
        // So here we want the component to stay rendered during the transition
        // using v-if and the transition is triggered by the v-show using
        // `shouldShow`. This means that the transition hooks will be triggered
        // twice but we deal with that later.
        shouldRender(step) {
            return this.step === step || this.transitioningFrom === step;
        },
        shouldShow(step) {
            return this.step === step;
        },
        resetUses() {
            this.bases[0].spaces[0].uses = [];
            this.bases[0].spaces[1].uses = [];
        },
        join() {
            // If they join from an invite then they don't need to go through
            // registration. It should automatically redirect them, but this
            // way we can show a message that they also have a personal base.
            if (this.$root.authenticatedUser.activeBase().baseType === 'COLLABORATIVE') {
                this.$router.push({
                    name: 'home',
                    query: {
                        firstTime: true,
                        hasPersonalBasePrompt: true,
                    },
                });
            } else {
                this.changeStep('start');
                // this.changeStep('uses');
            }
        },
        advanceStep(oldStep, forward = true) {
            // Prevent double clicks
            if (this.transitioningFrom) {
                return;
            }
            const indexOld = this.selectedSteps.indexOf(oldStep);
            const indexNew = forward ? indexOld + 1 : indexOld - 1;
            const newStep = this.selectedSteps[indexNew];
            this.changeStep(newStep);
        },
        reverseStep(oldStep) {
            this.advanceStep(oldStep, false);
        },
        // The router is the source of truth for where in the registration the
        // user is.
        changeStep(newStep) {
            this.$router.push({ name: `register.${newStep}`, query: this.$route.query });
        },
        // Storing the enter done state until the entering component is fully
        enter(el, done) {
            // The transition is triggered twice from the v-if and the v-show
            // Here we check if it has already been triggered and then skip if
            // it has.
            if (this.enteringDone) {
                done();
                return;
            }
            this.enteringDone = done;
            this.enteringEl = el;
        },
        async startEnter(el, done) {
            this.enteringDone = null;
            this.enteringEl = null;
            let startScroll = window.scrollY;
            const endScroll = el.offsetTop;
            // For some reason when at the top of the page it stays at the top
            // of the page when the new element is added so when going backwards
            // it appears to jump to the top. However if the user is scrolled
            // down a little bit then it will keep the user at the scrolled
            // position even with the new element inserted above.
            // Here we check if the user is at the top of the page and then
            // manually scroll them to the top of the page after the new element
            // is inserted.
            if (startScroll === 0 && endScroll === 0) {
                startScroll = el.offsetHeight;
            }

            // Smoothly scroll to the next section.
            await smoothScroll(endScroll, null, 500, startScroll);

            // Tell vue transitions that we are done so they can remove the
            // "leaving" element.
            if (this.leavingDone) {
                this.leavingDone();
            }
            done();
            this.transitioningFrom = null;
        },
        // The "leaving" element will be removed when the `done` callback is
        // invoked so we store it and invoke it after scrolling to the
        // "entering" element.
        leave(el, done) {
            // The transition is triggered twice from the v-if and the v-show
            // Here we make sure to only do something on the original leave.
            if (!this.transitioningFrom) {
                window.document.body.scrollTo(0, 0);
                done();
                return;
            }
            this.leavingDone = done;
        },
        async finishRegistration(structure) {
            this.showFinalLoader = true;
            try {
                await bootstrapUser(structure);
                const query = {
                    firstTime: true,
                };
                if (structure && (structure[0].baseType === 'COLLABORATIVE')) {
                    query.hasPersonalBasePrompt = true;
                }
                this.clearSavedState();
                await this.$router.push({ name: 'home', query });
            } catch (e) {
                reportUnhandledValidationError(e);
                throw e;
            } finally {
                this.showFinalLoader = false;
            }
        },
        // To avoid stuttering we wait here until the component is fully mounted
        // before initiating the enter scrolling.
        childMounted() {
            if (this.enteringDone) {
                this.startEnter(this.enteringEl, this.enteringDone);
            }
        },
        /*
         * Here we check through all the uses that have been saved from the
         * last time the user was on and validating that they are all still
         * relevant.
         */
        adjustRefinementForChanges(use) {
            // First fetch the use and default value from the `uses` array.
            const structure = _.find(uses, { val: use.val });
            const defaultRefinement = structure?.refinement;
            const cachedRefinement = use.refinement;
            // If we cannot find the corresponding use then we return null and
            // filter that out of their selected uses.
            if (!defaultRefinement) {
                return null;
            }

            const customizations = cachedRefinement?.customizations;

            // If there are no customizations for the use then return the default
            // refinements.
            if (!cachedRefinement || !customizations) {
                return defaultRefinement;
            }

            // If there are any differences in the stored refinement we set this
            // value to `true` and then mark that refinement as _not_ done.
            let differencesExist = false;

            // If there are any customizations the user has made that are no
            // longer defined in the `use` object we filter them out here.
            const validCustomizations = this.removeCustomizationsThatNoLongerExist(customizations, structure);

            if (validCustomizations.length !== customizations.length) {
                differencesExist = true;
            }

            // Finally we map through the user's customizations and make sure
            // all the selected options are valid options in the `use` object.
            const adjustedCustomizations = validCustomizations.map((customization) => {
                const { customizationVal, categoryKey, selected } = customization;

                const customizationOptions = this.getCustomizationOptionsFromUseStructure(structure, customizationVal);

                const defaultCustomization = this.getDefaultCustomizationFromUseStructure(
                    structure,
                    customizationVal,
                    categoryKey
                );

                const defaultSelected = defaultCustomization?.selected;

                // If the customization is null first check if they can actually
                // turn the customization off. If so return the cached selection,
                // otherwise return the default.
                if (selected === null) {
                    if (customizationOptions.canTurnOff) {
                        return customization;
                    }
                    differencesExist = true;
                    return defaultCustomization;
                }

                // Extract all the available options from the refinement map.
                const options = this.getAvailableOptionsFromCustomizationOptions(customizationOptions, categoryKey);

                const newSelected = this.validateSelectedCustomization(selected, defaultSelected, options);

                // If the validated selected is different to the cached value
                // we indicate that there are differences so we can redirect later.
                if (!_.isEqual(newSelected, selected)) {
                    differencesExist = true;
                }

                return {
                    ...customization,
                    selected: newSelected,
                };
            });

            // If the customizations have changed and they are further along
            // than the uses page we redirect them back to the uses page.
            if (differencesExist) {
                this.goBackToStartingPage();
            }

            return {
                ...use.refinement,
                done: differencesExist ? false : use.refinement.done,
                customizations: adjustedCustomizations,
            };
        },
        validateArraySelectedCustomizations(selected, defaultSelected, validOptions) {
            // If the types are different then we revert to the default.
            if (!_.isArray(selected)) {
                return defaultSelected;
            }
            // Find all the selected values that exist in the refinement
            // options. If the length is different that means some have
            // been removed from refinement options, and if they have all
            // been removed we set it to the default refinements.
            const newSelected = _.intersection(selected, validOptions);
            if (newSelected.length !== selected.length) {
                if (!newSelected.length) {
                    return defaultSelected;
                }
            }
            return newSelected;
        },
        validateStringSelectedCustomizations(selected, defaultSelected, validOptions) {
            // If the types are different then we revert to the default.
            if (_.isArray(selected)) {
                return defaultSelected;
            }
            // If the selected customization is not a valid option we
            // revert to the default.
            if (!validOptions.includes(selected)) {
                return defaultSelected || '';
            }
            return selected;
        },
        validateSelectedCustomization(selected, defaultSelected, validOptions) {
            if (_.isArray(defaultSelected)) {
                return this.validateArraySelectedCustomizations(selected, defaultSelected, validOptions);
            }
            return this.validateStringSelectedCustomizations(selected, defaultSelected, validOptions);
        },
        getDefaultCustomizationFromUseStructure(structure, customizationVal, categoryKey) {
            const defaultRefinement = structure?.refinement;

            return _.find(
                defaultRefinement.customizations,
                { customizationVal, categoryKey }
            );
        },
        getCustomizationOptionsFromUseStructure(structure, customizationVal) {
            return _.find(structure.refinementMap.customizations, { val: customizationVal });
        },
        getAvailableOptionsFromCustomizationOptions(customizationOptions, categoryKey) {
            const categoryOptions = customizationOptions.categories[categoryKey];

            return _.map(categoryOptions.options, 'optionVal');
        },
        removeCustomizationsThatNoLongerExist(customizations, structure) {
            return customizations.filter((customization) => {
                const { customizationVal, categoryKey } = customization;

                const customizationOptions = _.find(structure.refinementMap.customizations, { val: customizationVal });

                if (!customizationOptions || !_.has(customizationOptions?.categories, categoryKey)) {
                    return false;
                }
                return true;
            });
        },
        goBackToStartingPage() {
            const startingRoute = 'register.start';
            // const startingRoute = 'register.uses';
            if (![startingRoute].includes(this.$route.name)) {
                // This shouldn't destroy this component as all register routes
                // use it.
                this.$router.replace({ name: startingRoute });
            }
        },
        isIgnored(use) {
            const ignoreVal = use.ignoreIfAlsoSelected;
            return ignoreVal && !!this.useCases.find((useCase) => {
                return ignoreVal.includes(useCase.val);
            });
        },
        registerWithoutUse() {
            this.finishRegistration(this.basesStructure);
        },
        signOut() {
            this.$root.showSignoutLoader = true;
            logout();
        },
    },
    watch: {
        $route(to, from) {
            const name = from.name;
            this.transitioningFrom = name === 'register' ? 'initial' : name.split('.')[1];
        },
        spacesArr(spaces) {
            // TODO: Check spaces
            if (spaces) {
                const spaceUses = spaces[0].uses;
                this.preSelectedUses = this.preSelectedUses.filter((use) => {
                    return _.find(spaceUses, { val: use });
                });
            }
        },
        useCases(newVal, oldVal) {
            const changedLength = newVal.length !== oldVal.length;
            if (changedLength && (this.spacesArr[0].newPages || this.spacesArr[1].newPages)) {
                delete this.bases[0].spaces[0].newPages;
                delete this.bases[0].spaces[1].newPages;
            }
        },
        baseType(newVal) {
            const base = this.bases[0];
            if (newVal === 'COLLABORATIVE') {
                if (!base.name || base.name === personalBaseName) {
                    this.bases[0].name = collaborativeBaseName;
                }
            } else {
                this.bases[0].name = personalBaseName;
            }
        },

    },
};
</script>

<style scoped>

.o-registration-page {
    @apply
        h-full
    ;
}

</style>
