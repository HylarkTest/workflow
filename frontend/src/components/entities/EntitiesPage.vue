<template>
    <div
        class="o-entities-page"
    >
        <LayoutPage
            :isLoading="isLoading"
            :headerProps="headerProps"
            backgroundStyle="ENTITIES"
            :isMaxFullScreen="isEntity"
            @openPageEdit="openPageSettings('', $event)"
        >
            <template
                #top
            >
                <div
                    v-if="entityTypePage"
                    class="flex justify-end"
                >
                    <template
                        v-if="isEntities"
                    >
                        <div>
                            <ViewsSelection
                                ref="views"
                                v-model:currentView="currentView"
                                :pageType="pageType"
                                :page="page"
                                :mapping="mapping"
                            >
                            </ViewsSelection>

                            <SupportTip
                                v-if="activeTips && isTipActive('VIEW')"
                                :activator="$refs.views"
                                :tips="activeTips"
                                :tip="getTip('VIEW')"
                            >
                            </SupportTip>
                        </div>

                        <RoundedIcon
                            class="ml-2"
                            icon="fa-list-timeline"
                            title="History"
                            @click="openHistory"
                        >
                        </RoundedIcon>
                    </template>

                    <Shortcuts
                        class="ml-2"
                        :page="page"
                        @goToShortcut="goToShortcut"
                    >
                    </Shortcuts>

                    <RoundedIcon
                        ref="pageCustomizations"
                        class="ml-2"
                        icon="fa-sliders-simple"
                        title="Page customizations"
                        @click="openPageSettings"
                    >
                    </RoundedIcon>

                    <SupportTip
                        v-if="activeTips && isTipActive('PAGE_CUSTOMIZATIONS')"
                        :activator="$refs.pageCustomizations"
                        :tips="activeTips"
                        :tip="getTip('PAGE_CUSTOMIZATIONS')"
                    >
                    </SupportTip>

                    <!-- <CustomizationsButton
                        v-if="page.mapping"
                        @click="openModal"
                    >
                    </CustomizationsButton> -->
                </div>
            </template>

            <component
                v-if="showPage"
                :is="pageTypeComponent"
                :page="page"
                :isBirdseyePage="viewIsBirdseye"
                :mapping="mapping"
                defaultFilter="all"
                :currentView="currentView"
            >
            </component>

        </LayoutPage>

        <DataEditModal
            v-if="isPageSettingsOpen"
            :page="page"
            :defaultTab="defaultSettingsTab"
            :defaultView="defaultSettingsView"
            @closeModal="closePageSettings"
        >
        </DataEditModal>

        <HistoryModal
            v-if="isModalOpen"
            :pageType="`Item:${mapping.id}`"
            @closeModal="closeModal"
        >
        </HistoryModal>
    </div>
</template>

<script>

import PAGE from '@/graphql/pages/queries/Page.gql';
import MAPPING from '@/graphql/mappings/queries/Mapping.gql';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import interactsWithPageSettings from '@/vue-mixins/interactsWithPageSettings.js';
import interactsWithRouterTitles from '@/vue-mixins/interactsWithRouterTitles.js';

import EntitiesContent from '@/components/entities/EntitiesContent.vue';
import EntityContent from '@/components/entities/EntityContent.vue';
// import PageFreedoc from '@/components/pages/PageFreedoc.vue';
// import PageLinks from '@/components/pages/PageLinks.vue';
// import PageCalendar from '@/components/pages/PageCalendar.vue';
// import PagePinboard from '@/components/pages/PagePinboard.vue';

import NotesPage from '@/components/notes/NotesPage.vue';
import CustomizationsButton from '@/components/buttons/CustomizationsButton.vue';
import ViewsSelection from '@/components/design/ViewsSelection.vue';
import LayoutPage from '@/components/layout/LayoutPage.vue';
import DataEditModal from '@/components/customize/DataEditModal.vue';
import LayoutHeader from '@/components/layout/LayoutHeader.vue';
import Shortcuts from '@/components/assets/Shortcuts.vue';

import interactsWithPageHistory from '@/vue-mixins/interactsWithPageHistory.js';
import interactsWithSupportWidget from '@/vue-mixins/support/interactsWithSupportWidget.js';
import interactsWithActiveTips from '@/vue-mixins/support/interactsWithActiveTips.js';

import TodosPage from '@/components/todos/TodosPage.vue';
import DocumentsPage from '@/components/documents/DocumentsPage.vue';
import PinboardPage from '@/components/pinboard/PinboardPage.vue';
import LinksPage from '@/components/links/LinksPage.vue';
import TimekeeperPage from '@/components/timekeeper/TimekeeperPage.vue';
import CalendarPage from '@/components/events/CalendarPage.vue';
import { pageQueryHandler } from '@/http/exceptionHandler.js';
import tipsObject from '@/core/data/tips.js';
import { createPageFromObject } from '@/core/repositories/pageRepository.js';

export default {
    name: 'EntitiesPage',
    components: {
        LayoutHeader,
        CustomizationsButton,
        ViewsSelection,
        LayoutPage,
        DataEditModal,
        Shortcuts,

        EntitiesContent,
        EntityContent,

        NotesPage,
        TodosPage,
        CalendarPage,
        DocumentsPage,
        PinboardPage,
        LinksPage,
        TimekeeperPage,
    },
    mixins: [
        interactsWithModal,
        interactsWithPageHistory,
        interactsWithPageSettings,
        interactsWithRouterTitles,
        interactsWithSupportWidget,
        interactsWithActiveTips,
    ],
    props: {
        pageId: {
            type: String,
            required: true,
        },
    },
    apollo: {
        page: {
            query: PAGE,
            variables() {
                return { id: this.pageId };
            },
            fetchPolicy: 'cache-first',
            update: (data) => createPageFromObject(data.page),
            error: pageQueryHandler,
        },
        mapping: {
            query: MAPPING,
            variables() {
                return {
                    id: this.page?.mapping.id,
                };
            },
            skip() {
                return !this.page?.mapping;
            },
            fetchPolicy: 'cache-first',
            error: pageQueryHandler,
        },
    },
    data() {
        return {
            currentView: null,
            settingsOpen: false,
            allowRouterTitle: true,
        };
    },
    computed: {
        tipKey() {
            if (this.isEntities) {
                return 'RECORDS';
            }
            if (this.isEntity) {
                return 'RECORD';
            }
            return false;
        },
        supportPropsObj() {
            return {
                sectionName: 'Records',
                sectionTitle: 'A records page',
                hideSearch: true,
                val: this.tipKey,
                hidePromptIf: this.areModalsOpen,
                tips: tipsObject[this.tipKey],
            };
        },
        areModalsOpen() {
            return this.$root.listOfOpenModalKeys.length;
        },
        entityTypePage() {
            return this.isEntities || this.isEntity;
        },
        isEntities() {
            return this.pageType === 'ENTITIES';
        },
        isEntity() {
            return this.pageType === 'ENTITY';
        },
        isLoading() {
            return this.$apollo.loading;
        },
        pageType() {
            return this.page?.type;
        },
        pageName() {
            return this.page?.name;
        },
        pageTypeComponent() {
            if (this.viewIsBirdseye) {
                if (this.viewType === 'EVENTS') {
                    return 'CalendarPage';
                }
                // NotesPage
                // TodosPage
                // CalendarPage
                // DocumentsPage
                // PinboardPage
                // LinksPage
                // TimekeeperPage
                return `${_.pascalCase(this.viewType)}Page`;
            }
            // EntitiesContent
            // EntityContent
            return `${_.pascalCase(this.pageType)}Content`;
        },
        viewIsBirdseye() {
            return this.currentView?.categoryType === 'BIRDSEYE';
        },
        viewType() {
            return this.currentView?.viewType;
        },
        showPage() {
            if (this.isLoading) {
                return false;
            }
            if (this.isEntity) {
                return !!this.mapping;
            }
            return this.currentView;
        },
        headerProps() {
            const props = {
                page: this.page,
                mapping: this.mapping,
            };

            if (this.viewIsBirdseye) {
                const type = this.viewType === 'EVENTS' ? 'CALENDAR' : this.viewType;
                props.subsectionName = this.$t(`links.${_.camelCase(type)}`);
            }
            return props;
        },
        routerTitle() {
            return this.pageName;
        },
    },
    methods: {
        openSettings() {
            this.settingsOpen = true;
        },
        closeSettings() {
            this.settingsOpen = false;
        },
    },
    watch: {
        '$route.fullPath': function onRouteUpdate() {
            this.currentView = null;
        },
    },
};
</script>

<style scoped>
.o-entities-page {
    @apply
        h-full
    ;

    &__side {
        @apply
            mr-4
            w-200p
        ;
    }
}
</style>
