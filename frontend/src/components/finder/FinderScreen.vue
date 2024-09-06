<template>
    <div class="o-finder-screen">

        <div class="mb-1">
            <InputLine
                ref="finderInput"
                v-model="query"
                placeholder="What are you looking for?"
            >
            </InputLine>
        </div>

        <div class="flex justify-end mb-10">
            <SortingDropdown
                v-if="showResults"
                v-model:sortOrder="sortOrder"
                :sortables="sortables"
            >
            </SortingDropdown>
        </div>
        <NoContentText
            v-if="showNoContent"
            class="mt-10"
            :customHeaderPath="noContentHeaderPath"
            :customIcon="noContentIcon"
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

        <LoaderFetch
            v-if="isLoading"
            class="py-8"
            :isFull="true"
            :sphereSize="40"
        >
        </LoaderFetch>

        <FinderResults
            v-if="showResults"
            :results="results"
            @closeModal="$emit('closeModal')"
        >
        </FinderResults>
    </div>
</template>

<script>
import _ from 'lodash';

import FinderResults from './FinderResults.vue';
import SortingDropdown from '@/components/sorting/SortingDropdown.vue';

import interactsWithSortables from '@/vue-mixins/interactsWithSortables.js';

import SEARCH_QUERY from '@/graphql/search/SearchQuery.gql';

export default {
    name: 'FinderScreen',
    components: {
        SortingDropdown,
        FinderResults,
    },
    mixins: [
        interactsWithSortables,
    ],
    props: {
    },
    emits: [
        'closeModal',
    ],
    apollo: {
        search: {
            query: SEARCH_QUERY,
            variables() {
                return {
                    query: this.query || '',
                    orderBy: [{
                        field: this.sortOrder.value,
                        direction: this.sortOrder.direction || 'DESC',
                    }],
                };
            },
            skip() {
                return !this.query;
            },
            debounce: 300,
            fetchPolicy: 'network-only',
        },
    },
    data() {
        return {
            query: this.searchTerm,
            sortOrder: this.startingSortOrder('MATCH'),
            sortables: this.validSortables(['MATCH', 'CREATED_AT', 'UPDATED_AT']),
            typing: false,
        };
    },
    computed: {
        results() {
            if (!this.query) {
                return [];
            }
            return this.search?.edges || [];
        },
        resultsLength() {
            return this.results.length;
        },
        noResultsFound() {
            return !this.resultsLength && this.query;
        },
        noQueryNoResults() {
            return !this.resultsLength && !this.query;
        },
        showResults() {
            return this.resultsLength && !this.isLoading;
        },
        isLoading() {
            return this.typing || this.$apollo.loading;
        },
        showNoContent() {
            return !this.isLoading && (this.noResultsFound || this.noQueryNoResults);
        },
        noContentHeaderPath() {
            return this.noQueryNoResults ? 'finder.typeAQuery' : 'finder.noResults';
        },
        noContentIcon() {
            return this.noQueryNoResults ? 'fa-magnifying-glass' : 'fa-file-slash';
        },
    },
    methods: {
        focusInput() {
            this.$refs.finderInput.onFocus(true);
        },
        stopTyping: _.debounce(function stopTyping() {
            this.typing = false;
        }, 300, { leading: false, trailing: true }),
    },
    watch: {
        query(value) {
            if (value) {
                this.typing = true;
                this.stopTyping();
            }
        },
    },
    created() {

    },
    mounted() {
        this.focusInput();
    },
};
</script>

<style scoped>

/*.o-finder-screen {

} */

</style>
