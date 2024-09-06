<template>
    <div class="c-location-picker">
        <DropdownInput
            v-model:inputVal="filters.freeText"
            :modelValue="location"
            comparator="id"
            class="w-full"
            :options="filteredLocations"
            displayRule="name"
            :placeholder="formattedPlaceholder"
            dropdownComponent="DropdownBox"
            :neverHighlighted="true"
            useFormValueForDisplay
            :processing="processing"
            v-bind="$attrs"
            @update:modelValue="selectLocation"
        >
            <template #option="{ display, original }">
                {{ display }}
                <span
                    v-if="!isSingleLevelPicker"
                    class="c-location-picker__level text-cm-600 border-cm-600"
                >
                    {{ getLevel(original) }}
                </span>
            </template>
        </DropdownInput>

    </div>
</template>

<script>

import pluralize from 'pluralize';
import LOCATION_SEARCH from '@/graphql/locations/LocationSearch.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';
import { getName } from '@/core/display/theStandardizer.js';

export default {
    name: 'LocationPicker',
    components: {

    },
    mixins: [
    ],
    props: {
        location: {
            type: [Object, Array, null],
            default: null,
        },
        levels: {
            type: [Array, null],
            default: null,
        },
        placeholder: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:location',
    ],
    apollo: {
        locations: {
            query: LOCATION_SEARCH,
            variables() {
                const data = {
                    search: this.filters.freeText,
                };
                if (this.levels) {
                    data.levels = this.levels;
                }
                return data;
            },
            skip() {
                return !this.filters.freeText;
            },
            update: (data) => initializeConnections(data).locations,
            debounce: 300,
            fetchPolicy: 'no-cache',
        },
    },
    data() {
        return {
            filters: {
                freeText: !_.isArray(this.location) ? (this.location?.name || '') : '',
            },
        };
    },
    computed: {
        processing() {
            return this.$apollo.queries.locations.loading;
        },
        isMultiSelect() {
            return _.isArray(this.location);
        },
        formattedPlaceholder() {
            if (this.placeholder) {
                return this.placeholder;
            }
            return this.defaultPlaceholder;
        },
        defaultPlaceholder() {
            // If single location level picker, use that single level for placeholder, otherwise use generic "location"
            const locationType = this.isSingleLevelPicker ? _.upperFirst(this.singleLevelType) : 'Location';
            const locationKey = this.isMultiSelect ? pluralize(locationType) : locationType;
            return this.$t(`labels.select${locationKey}`);
        },
        filteredLocations() {
            return this.filters.freeText ? (this.locations || []) : [];
        },
        isSingleLevelPicker() {
            return this.levels?.length === 1;
        },
        singleLevelType() {
            if (this.isSingleLevelPicker) {
                const singleLevel = this.levels?.[0];
                return _.camelCase(singleLevel);
            }
            return null;
        },
    },
    methods: {
        getLevel(location) {
            const camelCaseLevel = _.camelCase(location.level);
            return this.$t(`fields.types.${camelCaseLevel}`);
        },
        selectLocation(location) {
            this.updateInput(location);
            this.$emit('update:location', location);
        },
        updateInput(location) {
            this.filters.freeText = location?.name || '';
        },
    },
    created() {
        this.entitiesDisplay = getName;
    },
};
</script>

<style scoped>

.c-location-picker {

    &__level {
        @apply
            border
            border-solid
            leading-4
            ml-2
            px-1.5
            rounded-full
            text-xxs
        ;
    }
}

</style>
