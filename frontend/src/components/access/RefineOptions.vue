<template>
    <div class="o-refine-options">
        <div
            v-for="(customization, customizationIndex) in customizations"
            :key="customization.val"
            class="mb-6 last:mb-0"
        >
            <div
                class="flex items-baseline mb-3"
            >
                <ToggleButton
                    v-if="hasSectionToggle(customization)"
                    class="mr-4"
                    :modelValue="isCustomizationActive(customization)"
                    @update:modelValue="toggleCustomization(
                        $event,
                        customization,
                        customizationIndex
                    )"
                >
                </ToggleButton>
                <p
                    class="font-bold"
                >
                    {{ $t(refinementsTitlePath(customization.val)) }}
                    <span
                        v-if="!(customization.optional || customization.canTurnOff)"
                    >
                        *
                    </span>
                </p>
            </div>

            <div
                v-if="isCustomizationActive(customization)"
                class="text-smbase flex flex-wrap gap-10"
            >
                <div
                    v-for="(category, categoryKey) in customization.categories"
                    :key="categoryKey"
                    class="flex-1"
                >
                    <p
                        v-if="!category.ignoreHeader"
                        class="mb-2 mt-1 font-bold text-cm-500 uppercase text-xssm"
                    >
                        {{ categoryText(categoryKey) }}
                        <span
                            v-if="!category.canTurnOff"
                        >
                            *
                        </span>
                    </p>

                    <p
                        v-if="category.prompt"
                        class="mb-2 mt-1 font-bold text-primary-700 text-sm"
                    >
                        {{ promptText(category.prompt) }}
                        <span
                            v-if="!category.canTurnOff || !category.ignoreHeader"
                        >
                            *
                        </span>
                    </p>
                    <div
                        v-for="option in category.options"
                        :key="option.optionVal"
                        class="my-1.5"
                    >
                        <CheckHolder
                            :modelValue="getOptionVal(customization, categoryKey)"
                            :val="option.optionVal"
                            :type="category.radio ? 'radio' : 'checkbox'"
                            canRadioClear
                            @update:modelValue="updateUse($event, {
                                customization,
                                category,
                                categoryKey,
                            })"
                        >
                            {{ optionText(option) }}
                        </CheckHolder>
                    </div>

                    <p
                        v-if="category.post"
                        class="italic text-cm-600 text-xssm mt-3"
                    >
                        {{ postText(category.post) }}
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>

<script>

import uses from '@/core/mappings/templates/uses.js';

export default {
    name: 'RefineOptions',
    components: {
    },
    mixins: [
    ],
    props: {
        use: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:use',
    ],
    data() {
        return {

        };
    },
    computed: {
        formattedVal() {
            return _.camelCase(this.use?.val);
        },
        customizations() {
            // Another option would have been to merge the saved refinements
            // into the customization options here, however it was
            // uncertain how beneficial that would be to performance
            // so for now fetching the values as needed rather than
            // integrating them here.
            return this.use.refinementMap?.customizations;
        },
        configUse() {
            return _(uses).find((use) => {
                return this.use.val === use.val;
            });
        },
    },
    methods: {
        optionText(option) {
            const optionKey = option.pathKey || option.optionVal;
            return this.$t(`registration.refine.refinements.options.${optionKey}`);
        },
        categoryText(categoryKey) {
            return this.$t(`registration.refine.refinements.subtitles.${categoryKey}`);
        },
        promptText(promptKey) {
            return this.$t(`registration.refine.refinements.prompts.${promptKey}`);
        },
        postText(postKey) {
            return this.$t(`registration.refine.refinements.posts.${postKey}`);
        },
        formattedKey(val) {
            return _.camelCase(val);
        },
        refinementsTitlePath(val) {
            return `registration.refine.refinements.titles.${this.formattedKey(val)}`;
        },
        updateUse(val, {
            customization,
            category,
            categoryKey,
        }) {
            const refinementIndex = this.getRefinementIndex(customization, categoryKey);

            const path = `refinement.customizations.[${refinementIndex}].selected`;

            const newUse = _.cloneDeep(this.use);

            if (category.radio && (_.get(newUse, path) === val)) {
                _.set(newUse, path, null);
            } else {
                _.set(newUse, path, val);
            }
            this.$emit('update:use', newUse);
        },
        hasSectionToggle(customization) {
            return _.has(customization, 'canTurnOff');
        },
        getRefinement(customization, categoryKey) {
            return _(this.use.refinement.customizations).find((setCustomization) => {
                return setCustomization.customizationVal === customization.val
                    && setCustomization.categoryKey === categoryKey;
            });
        },
        getRefinementIndex(customization, categoryKey) {
            return _(this.use.refinement.customizations).findIndex((setCustomization) => {
                return setCustomization.customizationVal === customization.val
                    && setCustomization.categoryKey === categoryKey;
            });
        },
        getOptionVal(customization, categoryKey) {
            const refinement = this.getRefinement(customization, categoryKey);
            return refinement?.selected;
        },
        isCustomizationActive(customization) {
            const refinement = _(this.use.refinement.customizations).find((setCustomization) => {
                return setCustomization.customizationVal === customization.val;
            });
            return _.has(refinement, 'active') ? refinement.active : true;
        },
        toggleCustomization(event, customization) {
            const newUse = _.cloneDeep(this.use);

            const customizations = this.configUse.refinement.customizations;

            customizations.forEach((refinement, index) => {
                const matchesVal = refinement.customizationVal === customization.val;
                if (matchesVal) {
                    const newRefinement = _.cloneDeep(refinement);

                    const selected = refinement.selected;
                    if (!event) {
                        // Toggle closing
                        const offVal = _.isArray(selected) ? [] : null;
                        newRefinement.selected = offVal;
                    } else {
                        // Toggle open with default set
                        newRefinement.selected = selected;
                    }
                    newRefinement.active = event;
                    newUse.refinement.customizations[index] = newRefinement;
                }
            });
            this.$emit('update:use', newUse);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-refine-options {

} */

</style>
