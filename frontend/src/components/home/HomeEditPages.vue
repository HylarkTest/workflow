<template>
    <div class="o-home-edit-pages">
        <p
            v-t="'home.customize.pages.usesPersonal'"
            class="mb-2 text-sm italic"
        >
        </p>

        <div
            v-if="spacesWithPagesLength"
        >
            <div
                v-for="space in spacesWithPages"
                :key="space.id"
                class="mb-5 last:mb-0"
            >
                <h5
                    class="font-bold mb-1"
                >
                    {{ space.name }}
                </h5>

                <div>
                    <div
                        v-for="option in options"
                        :key="option.val"
                        class="mb-2.5 last:mb-0"
                    >
                        <div
                            class=""
                        >
                            <div
                                class="flex"
                            >
                                <CheckHolder
                                    :modelValue="spaceSettingValue(space, option.val)"
                                    :val="option.val"
                                    type="radio"
                                    @update:modelValue="setSpaceValue($event, space)"
                                >
                                    <span
                                        class="font-medium text-cm-500"
                                    >
                                        {{ $t(getOptionNamePath(option)) }}
                                    </span>
                                </CheckHolder>

                                <button
                                    type="button"
                                    @click="toggleExplanationTab(option.val, space.id)"
                                >
                                    <i
                                        class="fa-regular text-primary-500 ml-2"
                                        :class="questionIconClass(option.val, space.id)"
                                    >
                                    </i>
                                </button>
                            </div>

                            <div
                                v-if="isExplanationTabOpen(option.val, space.id)"
                                class="ml-8 bg-primary-100 text-sm rounded-xl px-3 py-1 relative"
                            >
                                <ClearButton
                                    positioningClass="-top-1 -right-1 absolute"
                                    @click="closeExplanation(option.val, space.id)"
                                >
                                </ClearButton>

                                {{ $t(getOptionExplanationPath(option)) }}
                            </div>
                        </div>

                        <div
                            v-if="option.val === 'SPECIFIC'"
                            class="ml-8 mt-2"
                            :class="{ unclickable: !isOnSpecificPages(space) }"
                        >
                            <div
                                v-for="page in space.pages"
                                :key="page.id"
                                class="mb-1 last:mb-0"
                            >
                                <CheckHolder
                                    :modelValue="spacePageValue(space)"
                                    :val="page.id"
                                    size="sm"
                                    @update:modelValue="setSpacePageValue($event, space)"
                                >
                                    <p
                                        class="text-smbase"
                                    >
                                        {{ page.name }}
                                    </p>
                                </CheckHolder>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <NoContentText
            v-else
            customIcon="fa-memo"
            customHeaderPath="home.customize.pages.none.header"
            customMessagePath="home.customize.pages.none.message"
        >
            <RouterLink
                class="button--sm button-primary mt-2 inline-block"
                :to="{ name: 'customizePage' }"
            >
                {{ $t(customizeTextPath) }}
            </RouterLink>
        </NoContentText>
    </div>
</template>

<script>
import ClearButton from '@/components/buttons/ClearButton.vue';

import interactsWithAuthenticatedUser from '@/vue-mixins/interactsWithAuthenticatedUser.js';
import {
    updateProfile,
    isActiveBasePersonal,
} from '@/core/repositories/baseRepository.js';

import { arrRemove } from '@/core/utils.js';

const options = [
    {
        val: 'DEFAULT',
    },
    {
        val: 'ALL',
    },
    {
        val: 'SPECIFIC',
    },
];

export default {
    name: 'HomeEditPages',
    components: {
        ClearButton,
    },
    mixins: [
        interactsWithAuthenticatedUser,
    ],
    props: {
        links: {
            type: Object,
            required: true,
        },
        activeBase: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            userForm: null,
            openExplanationTabs: [],
        };
    },
    computed: {
        spaces() {
            return this.links?.spaces || [];
        },
        spacesWithPagesLength() {
            return this.spacesWithPages?.length;
        },
        spacesWithPages() {
            return this.spaces.filter((space) => {
                return space.pages?.length;
            });
        },
        customizeTextPath() {
            return this.isPersonalActive
                ? 'customizations.prompts.myBase'
                : 'customizations.prompts.base';
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
        userBasePreferences() {
            return this.authenticatedUser.baseSpecificPreferences();
        },
        spacesContent() {
            // Backend should return empty object if there is nothing
            return this.userBasePreferences.homepage.spaces || {};
        },
        usesDefaults() {
            // If the pages are null, then it uses the defaults
            return !this.pagesToShow;
        },
        baseDefaults() {
            return this.activeBase.preferences.homepage.pages;
        },
    },
    methods: {
        setUserForm() {
            this.userForm = this.$apolloForm(() => {
                return {
                    preferences: {
                        homepage: {
                            spaces: _.clone(this.spacesContent),
                        },
                    },
                };
            }, { client: 'defaultClient' });
        },
        async saveUserForm() {
            await updateProfile(this.userForm);
            this.$debouncedSaveFeedback();
        },
        isOnSpecificPages(space) {
            const spaceObj = this.userForm.preferences.homepage.spaces[space.id];
            return spaceObj?.pages && (spaceObj.pages !== 'ALL');
        },
        spaceSettingValue(space) {
            const spaceObject = this.userForm.preferences.homepage.spaces[space.id];
            if (!spaceObject) {
                return 'DEFAULT';
            }
            if (spaceObject.pages === 'ALL') {
                return 'ALL';
            }
            return 'SPECIFIC';
        },
        setSpaceValue(val, space) {
            if (val === 'SPECIFIC' || val === 'ALL') {
                this.userForm.preferences.homepage.spaces[space.id] = {};
            }
            if (val === 'ALL') {
                this.userForm.preferences.homepage.spaces[space.id].pages = 'ALL';
            }
            if (val === 'SPECIFIC') {
                const foundSpace = this.spaces.find((spaceObj) => {
                    return spaceObj.id === space.id;
                });

                const pagesInSpace = foundSpace.pages;

                const pageIds = pagesInSpace.map((page) => {
                    return page.id;
                });
                this.userForm.preferences.homepage.spaces[space.id].pages = pageIds;
            }
            if (val === 'DEFAULT') {
                delete this.userForm.preferences.homepage.spaces[space.id];
            }
        },
        spacePageValue(space) {
            const pageSpace = this.userForm.preferences.homepage.spaces[space.id];
            return pageSpace?.pages;
        },
        setSpacePageValue(pages, space) {
            this.userForm.preferences.homepage.spaces[space.id].pages = pages;
        },
        getOptionNamePath(option) {
            const camelVal = _.camelCase(option.val);
            return `home.customize.pages.headers.${camelVal}`;
        },
        getOptionExplanationPath(option) {
            const camelVal = _.camelCase(option.val);
            return `home.customize.pages.explanations.${camelVal}`;
        },
        makeSectionId(val, spaceId) {
            return `${spaceId}-${val}`;
        },
        isExplanationTabOpen(val, spaceId) {
            const id = this.makeSectionId(val, spaceId);
            return this.openExplanationTabs.includes(id);
        },
        toggleExplanationTab(val, spaceId) {
            const id = this.makeSectionId(val, spaceId);
            if (this.isExplanationTabOpen(val, spaceId)) {
                this.closeExplanation(val, spaceId);
            } else {
                this.openExplanationTabs.push(id);
            }
        },
        questionIconClass(val, spaceId) {
            return this.isExplanationTabOpen(val, spaceId) ? 'fa-circle-xmark' : 'fa-circle-question';
        },
        closeExplanation(val, spaceId) {
            const id = this.makeSectionId(val, spaceId);
            this.openExplanationTabs = arrRemove(this.openExplanationTabs, id);
        },

    },
    watch: {
        'userForm.preferences.homepage.spaces': {
            handler(newVal) {
                const areSpacesEqual = _.isEqual(newVal, this.spacesContent);
                // TODO: Check comparison works for deep object
                if (!areSpacesEqual) {
                    this.saveUserForm();
                }
            },
            deep: true,
        },
    },
    created() {
        this.setUserForm();
        this.options = options;
    },
};
</script>

<style scoped>

/*.o-home-edit-pages {

} */

</style>
