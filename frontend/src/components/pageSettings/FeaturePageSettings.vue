<template>
    <PageSettingsTemplate
        class="o-feature-page-settings"
        :typeName="feature"
        v-bind="$attrs"
    >
        <div>
            <SettingsHeaderLine>
                <template
                    #header
                >
                    Choose your filters
                </template>

                <template
                    #description
                >
                    Select which filters you want to appear on the side.
                </template>

                <div>
                    <CheckHolder
                        v-for="filter in allTodosFilters"
                        :key="filter.id"
                        class="mb-2"
                    >
                        <div class="flex items-center ml-2">
                            <div
                                class="h-6 w-6 text-xs circle-center mr-2"
                                :class="getBgColor(filter.color, '100')"
                            >
                                <i
                                    class="far fa-fw"
                                    :class="[filter.icon, getTextColor(filter.color, '700')]"
                                >
                                </i>
                            </div>

                            <span
                                v-t="filter.textPath"
                                class="text-sm"
                            >
                            </span>
                        </div>
                    </CheckHolder>
                </div>
            </SettingsHeaderLine>
        </div>
    </PageSettingsTemplate>
</template>

<script>

import PageSettingsTemplate from './PageSettingsTemplate.vue';

import providesColors from '@/vue-mixins/style/providesColors.js';

import { allTodosFilters } from '@/core/display/featureFilters.js';

export default {
    name: 'FeaturePageSettings',
    components: {
        PageSettingsTemplate,
    },
    mixins: [
        providesColors,
    ],
    props: {
        feature: {
            type: String,
            required: true,
            validator(val) {
                return ['EMAILS', 'TODOS', 'EVENTS', 'PINBOARD', 'LINKS', 'DOCUMENTS'].includes(val);
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {
        this.allTodosFilters = allTodosFilters;
    },
};
</script>

<style scoped>

/*.o-feature-page-settings {

} */

</style>
