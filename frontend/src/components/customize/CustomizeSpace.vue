<template>
    <div class="o-customize-space">
        <div
            class="p-6"
        >
            <div
                class="flex justify-between mb-2 flex-wrap"
            >
                <div class="flex items-center">
                    <ExpandCollapseButton
                        class="mr-3"
                        :isExpanded="isExpanded"
                        @toggleExpandCollapse="$emit('toggleExpandCollapse')"
                    >
                    </ExpandCollapseButton>
                    <h3 class="text-2xl font-semibold">
                        {{ space.name }}
                    </h3>
                </div>

                <div>
                    <button
                        class="button-primary--border button--sm mr-2"
                        type="button"
                        @click="openPageCreation({ page: null })"
                    >
                        <i
                            class="fal fa-memo mr-1"
                        >
                        </i>
                        {{ $t('customizations.pages.addPage') }}
                    </button>
                    <button
                        class="button-primary button--sm"
                        type="button"
                        @click="openSpaceEdit"
                    >
                        <i
                            class="fal fa-pencil mr-1"
                        >
                        </i>
                        {{ $t('customizations.spaces.edit') }}
                    </button>
                </div>
            </div>

            <div v-if="isExpanded">

                <div v-if="hasFolders">
                    <div class="flex mb-3">
                        <UnderlinedTabs
                            :tabs="tabs"
                            :selectedTab="currentTab"
                            @selectTab="switchTab"
                        >
                        </UnderlinedTabs>
                    </div>
                    <Component
                        :is="customizationComponent"
                        :pages="allSpacePages"
                        :spaceFolders="spaceFolders"
                        :pageBeingDeleted="pageBeingDeleted"
                        @openPageEdit="$emit('openPageEdit', $event)"
                    >
                    </Component>
                </div>

                <NoContentText
                    v-else
                    class="mt-4"
                    customHeaderPath="customizations.spaces.noContent.pages.header"
                    customMessagePath="customizations.spaces.noContent.pages.description"
                    customIcon="fa-memo"
                    iconBgClass="bg-cm-00"
                >
                    <button
                        class="button-primary--border button--sm mt-2"
                        type="button"
                        @click="openPageCreation({ page: null })"
                    >
                        {{ $t('common.getStarted') }}
                    </button>
                </NoContentText>

                <PotentialPagesSection
                    v-if="currentTab === 'pages'"
                    class="o-customize-space__potential"
                    :existingPagesTemplateRefs="existingPagesTemplateRefs"
                    @openPageCreation="openPageCreation"
                >
                    <template #header>
                        <h3 class="o-customize-space__potential--header">
                            {{ $t(potentialPagesHeaderPath) }}
                        </h3>

                        <p
                            v-md-text="$t('customizations.pages.potential.description', { spaceName })"
                            class="mb-1 text-center text-smbase text-gray-500"
                        >
                        </p>
                    </template>
                </PotentialPagesSection>

            </div>
        </div>
    </div>
</template>

<script>

import CustomizeSpaceBlueprints from './CustomizeSpaceBlueprints.vue';
import CustomizeSpacePages from './CustomizeSpacePages.vue';
import PotentialPagesSection from './PotentialPagesSection.vue';
import ExpandCollapseButton from '@/components/buttons/ExpandCollapseButton.vue';
import UnderlinedTabs from '@/components/tabs/UnderlinedTabs.vue';

const tabs = [
    {
        value: 'pages',
        namePath: 'common.pages',
        component: 'CustomizeSpacePages',
    },
    {
        value: 'blueprints',
        namePath: 'labels.blueprints',
        component: 'CustomizeSpaceBlueprints',
    },
];

export default {
    name: 'CustomizeSpace',
    components: {
        CustomizeSpaceBlueprints,
        CustomizeSpacePages,
        ExpandCollapseButton,
        PotentialPagesSection,
        UnderlinedTabs,
    },
    mixins: [
    ],
    props: {
        space: {
            type: Object,
            required: true,
        },
        pageBeingDeleted: {
            type: [Object, null],
            default: null,
        },
        isExpanded: Boolean,
    },
    emits: [
        'openPageEdit',
        'openPageCreation',
        'openSpaceEdit',
        'toggleExpandCollapse',
    ],
    data() {
        return {
            currentTab: 'pages',
        };
    },
    computed: {
        spaceFolders() {
            return this.space.folders;
        },
        allSpacePages() {
            return this.spaceFolders.map((folder) => folder.pages).flat();
        },
        existingPagesTemplateRefs() {
            const pagesTemplateRefs = this.allSpacePages.map((page) => page.templateRefs);

            const pagesTemplateRefsAsArray = pagesTemplateRefs.flat();

            return pagesTemplateRefsAsArray.filter((templateRef) => templateRef);
        },
        foldersContentLength() {
            return this.spaceFolders?.length;
        },
        hasFolders() {
            return !!this.foldersContentLength;
        },
        spaceName() {
            return this.space.name;
        },
        potentialPagesHeaderPath() {
            const pathKey = this.hasFolders
                ? 'header'
                : 'headerInitial';
            return `customizations.pages.potential.${pathKey}`;
        },
        customizationComponent() {
            const currentTabObj = this.tabs.find((tab) => tab.value === this.currentTab);
            return currentTabObj.component;
        },
    },
    methods: {
        openSpaceEdit() {
            this.$emit('openSpaceEdit', this.space);
        },
        openPageCreation({ page = null, initialStep = '' }) {
            this.$emit('openPageCreation', { space: this.space, page, initialStep });
        },
        switchTab(tab) {
            this.currentTab = tab.value;
        },
    },
    created() {
        this.tabs = tabs;
    },
};
</script>

<style scoped>
.o-customize-space {

    &__potential {
        @apply
            bg-gradient-to-b
            from-primary-100
            pt-5
            rounded-b-xl
            to-primary-50
        ;

        &--header {
            @apply
                bg-primary-200
                font-bold
                mb-5
                py-2
                text-center
                text-xl
            ;
        }
    }
}
</style>
