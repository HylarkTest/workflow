<template>
    <div
        v-if="mapping"
        class="o-page-wizard-subset"
    >
        <div class="max-w-xl">
            <div>
                <h2 class="o-creation-wizard__prompt pt-12">
                    Which "{{ mapping.name }}" records do you want to show on "{{ pageForm.name }}"?
                </h2>
                <div class="flex justify-center">
                    <button
                        class="button--lg mr-4"
                        :class="buttonClass('ALL')"
                        type="button"
                        @click="setAll"
                    >
                        {{ $t('common.all') }}
                    </button>

                    <button
                        class="button--lg"
                        :class="buttonClass('SUBSET')"
                        type="button"
                        @click="setSubset"
                    >
                        A subset
                    </button>
                </div>
            </div>

            <div
                v-if="pageFilter && pageFilter !== 'ALL' && mapping"
                class="pt-12"
            >
                <h2 class="o-creation-wizard__prompt">
                    How do you want to filter for that subset?
                </h2>

                <div class="bg-cm-100 rounded-xl p-4">
                    <PageSubsetFilters
                        :pageForm="pageForm"
                        :mapping="mapping"
                        :noContentDisplayable="true"
                        bgColor="white"
                        @updateForm="updateForm"
                    >
                    </PageSubsetFilters>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import PageSubsetFilters from '@/components/customize/PageSubsetFilters.vue';

import MAPPING from '@/graphql/mappings/queries/Mapping.gql';

const filter = {
    by: '', // MARKER, FIELD
    fieldId: null,
    match: 'IS', // IS, IS_NOT
    matchValue: null, // id of marker or option of field
    context: null,
};

export default {
    name: 'PageWizardSubset',
    components: {
        PageSubsetFilters,
    },
    mixins: [
    ],
    props: {
        pageForm: {
            type: Object,
            required: true,
        },
        blueprintForm: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:pageForm',
    ],
    apollo: {
        mapping: {
            query: MAPPING,
            variables() {
                return { id: this.pageForm.mapping };
            },
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
        };
    },
    computed: {
        pageFilter() {
            return this.pageForm.filter;
        },

        // isFields() {
        //     return this.filterBy === 'FIELD';
        // },
    },
    methods: {
        setAll() {
            this.emitFilter('ALL');
        },
        setSubset() {
            if (!this.isSelected('SUBSET')) {
                const clone = _.clone(filter);
                this.emitFilter(clone);
            }
        },
        isSelected(val) {
            if (val === 'ALL') {
                return this.pageForm.filter === val;
            }
            return this.pageFilter && this.pageFilter !== 'ALL';
        },
        buttonClass(val) {
            return this.isSelected(val)
                ? 'bg-secondary-600 text-cm-00'
                : 'button-secondary--light';
        },
        emitFilter(newVal) {
            this.$emit('update:pageForm', { valKey: 'filter', newVal });
        },
        updateFilter(valKey, val) {
            if (this.pageFilter === 'ALL') {
                return;
            }
            const clone = _.clone(this.pageFilter);
            clone[valKey] = val;
            this.emitFilter(clone);
        },
        updateForm({ valKey, val }) {
            this.updateFilter(valKey, val);
        },
    },
    created() {
        this.emitFilter(null);
    },
};
</script>

<style scoped>

.o-page-wizard-subset {
    &__label {
        @apply
            block
            font-semibold
            text-center
            text-cm-400
            text-smbase
            uppercase
        ;
    }
}

</style>
