<template>
    <div class="o-page-edit-personal">
        <FormWrapper
            :form="form"
        >
            <SettingsHeaderLine>
                <template
                    #header
                >
                    Your default filter
                </template>
                <slot
                    name="description"
                >
                    <div
                        v-t="'customizations.filters.explanations.personalFilter'"
                        class="text-sm mb-2 bg-secondary-100 rounded-lg p-2"
                    >
                    </div>
                </slot>

                <FiltersPicker
                    v-model="form.personalDefaultFilterId"
                    :page="page"
                    bgColor="gray"
                    :disabled="processingDefaultFilter"
                    property="id"
                    :mapping="mapping"
                    :hasPersonalDefaultInitially="true"
                    :filterables="filterables"
                    placeholder="Set a default filter"
                    :sortables="sortables"
                >
                </FiltersPicker>
            </SettingsHeaderLine>
        </FormWrapper>
    </div>
</template>

<script>

import FiltersPicker from '@/components/pickers/FiltersPicker.vue';
import { updateMappingPage } from '@/core/repositories/pageRepository.js';

import providesFilterables from '@/vue-mixins/providesFilterables.js';
import interactsWithSortables from '@/vue-mixins/interactsWithSortables.js';

export default {
    name: 'PageEditPersonal',
    components: {
        FiltersPicker,
    },
    mixins: [
        providesFilterables,
        interactsWithSortables,
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: this.$apolloForm({
                id: this.page.id,
                personalDefaultFilterId: this.page.personalDefaultFilter?.id,
            }),
            processingDefaultFilter: false,
        };
    },
    computed: {

    },
    methods: {
        async savePersonalDefaultFilter() {
            this.processingDefaultFilter = true;
            try {
                await updateMappingPage(this.form, this.page);
                this.$saveFeedback();
            } finally {
                this.processingDefaultFilter = false;
            }
        },
    },
    watch: {
        'form.personalDefaultFilterId': function onChange(newId) {
            if (newId === this.page.personalDefaultFilter?.id) {
                return;
            }
            this.savePersonalDefaultFilter();
        },
        'page.personalDefaultFilter.id': function onPersonalDefaultFilterChange(id) {
            this.form.personalDefaultFilterId = id;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-edit-personal {

} */

</style>
