<template>
    <div class="o-base-refine flex items-center flex-col">
        <div class="mb-8">
            <p
                class="text-center font-semibold mb-3 text-xl"
            >
                {{ $tc('registration.refine.selectedUses', usesLength) }}
            </p>

            <div
                v-for="space in usesMappedOnSpaces"
                :key="space.id"
                class="flex items-center mb-2"
            >
                <h5
                    v-if="allSpacesHaveUses && spacesLength > 1"
                    class="font-bold mr-4 text-lg"
                >
                    {{ space.name }}:
                </h5>

                <div class="flex gap-3 flex-wrap">
                    <component
                        v-for="use in space.uses"
                        :key="use.val"
                        :is="use.needsRefinement ? 'ButtonEl' : 'div'"
                        class="relative"
                    >
                        <UseMini
                            :class="[inProgressClass(use), { 'hover:shadow-lg': use.needsRefinement }]"
                            :use="use"
                            :base="base"
                            :showName="true"
                            @click="selectUse(use)"
                        >

                        </UseMini>

                        <div
                            v-if="hasUseTag(use)"
                            class="o-base-refine__tag"
                        >
                            {{ useTagText(use) }}
                        </div>
                    </component>
                </div>
            </div>
        </div>

        <div class="o-base-refine__container">
            <p
                class="text-center font-semibold mb-3 text-xl"
            >
                {{ $tc('registration.refine.refineIntro', refinableUsesLength) }}
                <span
                    class="text-secondary-600"
                >
                    {{ textTitle }}
                </span>
            </p>

            <div
                class="relative pt-10 xs:pl-10"
            >
                <img
                    class="o-base-refine__image"
                    :src="imageSrc"
                />

                <div
                    class="rounded-xl bg-cm-00 opacity-90 h-full w-full shadow-lg relative"
                >
                    <RefineOptions
                        v-model:use="currentUse"
                        class="mb-4 p-4"
                    >
                    </RefineOptions>

                    <div
                        class="o-base-refine__footer"
                        :class="footerPositioningClasses"
                    >
                        <template
                            v-if="!isCurrentComplete"
                        >
                            <p class="font-bold text-cm-600 text-sm">
                                Fill out all of the necessary fields to proceed
                            </p>
                        </template>
                        <template
                            v-else-if="!currentUse.isDone"
                        >
                            <p class="mb-2 font-bold text-cm-600 text-sm">
                                Happy with your choices for "{{ textTitle }}"?
                            </p>
                            <button
                                class="button--sm button-secondary"
                                type="button"
                                @click="setDone(currentUse)"
                            >
                                Done
                            </button>
                        </template>
                        <template
                            v-else
                        >
                            <p class="font-bold text-cm-600 text-sm">
                                <i
                                    class="fas fa-check-circle text-emerald-600 mr-1"
                                >
                                </i>
                                "{{ textTitle }}" has been personalized to you!
                            </p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import RefineOptions from '@/components/access/RefineOptions.vue';
import UseMini from '@/components/access/UseMini.vue';

import { parseMarkdown } from '@/core/utils.js';

export default {
    name: 'BaseRefine',
    components: {
        RefineOptions,
        UseMini,
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
        footerPositioningClasses: {
            type: String,
            default: 'bottom-14 sm:bottom-18',
        },
    },
    emits: [
        'update:base',
    ],
    data() {
        return {
            setUse: null,
        };
    },
    computed: {
        currentTitle() {
            return parseMarkdown(
                this.$t(`registration.uses.headers.${this.baseTypeFormatted}.${this.currentFormattedVal}`)
            );
        },
        textTitle() {
            return this.currentTitle.replace(/<[^>]*>/g, '');
        },
        spaces() {
            return this.base.spaces;
        },
        spacesLength() {
            return this.spaces.length;
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
        refinableUses() {
            return this.usesMapped.filter((use) => {
                return use.needsRefinement;
            });
        },
        isCurrentComplete() {
            return this.requiredCustomizations.every((customization) => {
                const selected = customization.setVal.selected;
                return _.isArray(selected) ? selected.length : selected;
            });
        },
        allAvailableCustomizations() {
            return _(this.currentUse.refinementMap?.customizations).flatMap((customization) => {
                return _(customization.categories).map((category, categoryKey) => {
                    const setVal = this.currentUse.refinement.customizations.find((setCustomization) => {
                        return setCustomization.customizationVal === customization.val
                            && setCustomization.categoryKey === categoryKey;
                    });
                    return {
                        categoryKey,
                        customizationVal: customization.val,
                        isCategoryOptional: category.optional || false,
                        isCustomizationOptional: customization.optional || false,
                        customizationCanTurnOff: customization.canTurnOff || false,
                        setVal: setVal || {},
                    };
                }).value();
            }).value();
        },
        requiredCustomizations() {
            return _.filter(this.allAvailableCustomizations, (customization) => {
                const canTurnOff = customization.customizationCanTurnOff;
                const activeVal = customization.setVal.active;

                if (canTurnOff && activeVal) {
                    return true;
                }
                if (canTurnOff && !activeVal) {
                    return false;
                }
                return !customization.isCategoryOptional
                    && !customization.isCustomizationOptional;
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
        areAllEditable() {
            return this.usesMapped.every((use) => {
                return use.needsRefinement;
            });
        },
        areSomeUnrefinable() {
            return this.usesMapped.some((use) => {
                return !use.needsRefinement;
            });
        },
        spacesUses() {
            return _.map(this.spaces, 'uses');
        },
        allSpacesHaveUses() {
            return this.spacesUses.every((uses) => {
                return uses.length;
            });
        },
        imageSrc() {
            return `${import.meta.env.VITE_API_URL}/images/stockUses/${this.currentFormattedVal}.jpg`;
        },
        currentUse: {
            get() {
                return this.setUse
                    ? _.find(this.usesMapped, { tempVal: this.setUse.tempVal })
                    : this.refinableUses[0];
            },
            set(use) {
                this.updateUses(use);
            },
        },
        usesLength() {
            return this.useCases.length;
        },
        refinableUsesLength() {
            return this.refinableUses.length;
        },
        currentFormattedVal() {
            return _.camelCase(this.currentUse?.val);
        },
    },
    methods: {
        inProgressClass(use) {
            return { 'shadow-md shadow-primary-600/20': this.isInProgress(use) };
        },
        isInProgress(use) {
            return this.currentUse.tempVal === use.tempVal;
        },
        selectUse(use) {
            if (use.needsRefinement) {
                this.setUse = use;
            }
        },
        isIgnored(use) {
            const ignoreVal = use.ignoreIfAlsoSelected;
            return ignoreVal && !!this.useCases.find((useCase) => {
                return ignoreVal.includes(useCase.val);
            });
        },
        hasUseTag(use) {
            return !use.needsRefinement || use.isDone || this.isIgnored(use);
        },
        useTagText(use) {
            if (!use.needsRefinement || this.isIgnored(use)) {
                return 'Ready to go';
            }
            if (use.isDone) {
                return 'Done';
            }
            return '';
        },
        updateUses(use) {
            const newBase = _.cloneDeep(this.base);

            const spaceIndex = use.spaceIndex;
            const useIndex = use.useIndex;

            if (~spaceIndex && ~useIndex) {
                const newUse = {
                    val: use.val,
                    refinement: use.refinement,
                };
                // If done, reset to complete again
                if (use.isDone) {
                    newUse.refinement.done = false;
                }
                newBase.spaces[spaceIndex].uses.splice(useIndex, 1, newUse);
                this.$emit('update:base', newBase);
            }
        },
        setDone(use) {
            const newUse = _.cloneDeep(use);
            newUse.refinement.done = true;
            this.updateUses(newUse);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-base-refine {
    &__container {

        @apply
            relative
            w-full
        ;

        @media (min-width: 640px) {
            & {
                max-width: 1000px;
                min-width: 80%;
            }

        }
    }

    &__image {
        height: 200px;
        min-width: 120px;
        object-fit: cover;
        transition: 0.2s ease-in-out;
        width: 30%;

        @apply
            absolute
            left-0
            rounded-lg
            top-0
        ;
    }

    &__tag {
        @apply
            absolute
            bg-primary-600
            font-semibold
            px-1.5
            py-0.5
            -right-3
            rounded
            text-cm-00
            text-xxs
            -top-3
        ;
    }

    &__footer {
        @apply
            bg-cm-100
            border-cm-200
            border-solid
            border-t
            flex
            flex-col
            items-center
            px-2
            py-2
            sticky
        ;
    }
}

</style>
