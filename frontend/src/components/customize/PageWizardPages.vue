<template>
    <div class="o-page-wizard-pages">
        <h2 class="o-creation-wizard__prompt mt-4">
            Select the pre-set page you'd like to add to your space
        </h2>

        <div class="w-full my-8 px-10">
            <div class="centered">
                <FreeFilter
                    v-model="filters.freeText"
                    class="max-w-300p w-3/4"
                    freePlaceholder="Filter pages"
                >
                </FreeFilter>
            </div>

            <div
                v-if="validGroups?.length"
            >
                <div
                    v-for="group in validGroups"
                    :key="group.groupKey"
                    class="mb-12"
                >
                    <h5
                        class="text-xl font-bold mb-4"
                    >
                        {{ getSectionName(group.groupKey) }}
                    </h5>

                    <div class="o-page-wizard-pages__items">
                        <PresetPage
                            v-for="page in group.pages"
                            :key="page.id"
                            :page="page"
                            :isSelected="isPageSelected(page)"
                            @click="selectPage(page)"
                        >

                        </PresetPage>
                    </div>

                </div>
            </div>
            <NoContentText
                v-else
                class="mt-10"
                customHeaderPath="common.noFilterMatches"
            >
                <template
                    #graphic
                >
                    <BirdImage
                        class="h-28"
                        whichBird="MagnifyingGlassBird_72dpi.png"
                    >
                    </BirdImage>
                </template>
            </NoContentText>
        </div>
    </div>
</template>

<script>

import PresetPage from '@/components/customize/PresetPage.vue';
import FreeFilter from '@/components/sorting/FreeFilter.vue';

import filterList from '@/core/filterList.js';
import { featurePages, entityPagesList } from '@/core/display/typenamesList.js';

const sortOrder = {
    ENTITIES: 0,
    TODOS: 1,
    CALENDAR: 2,
};

export default {
    name: 'PageWizardPages',
    components: {
        PresetPage,
        FreeFilter,
    },
    mixins: [
    ],
    props: {
        selectedPageData: {
            type: [null, Object],
            required: true,
        },
        availablePages: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'update:selectedPageData',
    ],
    data() {
        return {
            filters: {
                freeText: '',
            },
        };
    },
    computed: {
        grouped() {
            return _.groupBy(this.pagesList, 'pageType');
        },
        validGroups() {
            const groupArr = [];
            _.forEach(this.grouped, (group, key) => {
                if (group.length) {
                    groupArr.push(
                        {
                            groupKey: key,
                            pages: _.sortBy(group, (page) => {
                                return page.pageName || page.name;
                            }),
                        }
                    );
                }
            });

            const entityIndex = groupArr.findIndex((group) => {
                return group.groupKey === 'ENTITY';
            });
            const entitiesIndex = groupArr.findIndex((group) => {
                return group.groupKey === 'ENTITIES';
            });

            const entityPages = groupArr[entityIndex]?.pages || [];
            const allEntityPages = groupArr[entitiesIndex]?.pages.concat(entityPages) || [];

            if (~entitiesIndex) {
                groupArr[entitiesIndex].pages = _.sortBy(allEntityPages, (page) => {
                    return page.pageName || page.name;
                });
            }

            if (~entityIndex) {
                groupArr.splice(entityIndex, 1);
            }

            const sorted = _(groupArr).sortBy((group) => {
                return sortOrder[group.groupKey];
            }).value();
            return sorted;
        },
        pagesList() {
            return filterList(this.availablePages, this.filters, { keys: ['name', 'pageName'], threshold: 0.4 });
        },

        combined() {
            return {
                ...featurePages,
                ...entityPagesList,
            };
        },
        fullOptions() {
            return this.sections.map((option) => {
                return this.combined[option];
            });
        },
    },
    methods: {
        selectPage(page) {
            const oldId = this.selectedPageData.page?.id;
            const newObj = _.cloneDeep(this.selectedPageData);
            const isSelected = oldId === page.id;
            if (isSelected) {
                newObj.page = null;
            } else {
                newObj.page = page;
            }
            this.$emit('update:selectedPageData', newObj);
        },
        isPageSelected(page) {
            return this.selectedPageData.page && (this.selectedPageData.page.id === page.id);
        },
        getSectionName(section) {
            return this.$t(`customizations.pageWizard.pageSections.${_.camelCase(section)}`);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-page-wizard-pages {
    &__items {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));

        @apply
            gap-4
            grid
            justify-center
            w-full
        ;
    }

    &__side {
        top: 0;
    }
}

</style>
