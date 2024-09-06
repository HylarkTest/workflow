<template>
    <div class="o-calendar-side">

        <FeatureSide
            :filterables="filterables"
            :activeFilters="activeFilters"
            :basicFilters="basicFilters"
            :getFilterCount="randomFunction"
            :displayedList="selectedList"
            :bases="bases"
            :sources="sources"
            hideFilter
            v-bind="$attrs"
            @update:activeFilters="$emit('update:activeFilters', $event)"
        >

            <template
                #listTitle
            >
                Calendars
            </template>

            <template
                #actionButtons
            >
                <div
                    ref="blurParent"
                    v-blur="closeNewListDropdown"
                >
                    <AddCircle
                        ref="add"
                        @click="addNewList"
                    >

                    </AddCircle>

                    <PopupBasic
                        v-if="newListDropdown"
                        :activator="$refs.add"
                        :blurParent="$refs.blurParent"
                        nudgeDownProp="0.375rem"
                        nudgeRightProp="0.625rem"
                        alignRight
                    >
                        <button
                            v-for="option in addNewOptions"
                            :key="option.name"
                            type="button"
                            class="o-calendar-side__option o-calendar-side__name"
                            @click="selectNewListSource(option)"
                        >
                            <i
                                v-if="option.provider"
                                class="text-cm-300 mr-1"
                                :class="integrationIcon(option.provider)"
                            >
                            </i>

                            {{ option.name }}
                        </button>
                    </PopupBasic>
                </div>
            </template>
        </FeatureSide>
    </div>
</template>

<script>

import interactsWithFeatureSide from '@/vue-mixins/features/interactsWithFeaturesSide.js';
import { createCalendarFromObject } from '@/core/repositories/calendarRepository.js';

const newCalendar = {
    id: '',
    name: 'New calendar',
    count: 0,
    new: true,
    order: null,
};

const newExternalCalendar = {
    id: '',
    name: 'New calendar',
    new: true,
};

export default {
    name: 'CalendarSide',
    components: {
    },
    mixins: [
        interactsWithFeatureSide,
    ],
    props: {
        selectedList: {
            type: [Object, null],
            default: null,
        },
        showAll: Boolean,
    },
    emits: [
        'addNewList',
        'update:sources',
        'update:activeFilters',
    ],
    data() {
        return {

        };
    },
    computed: {
        combinedSources() {
            return [
                ...this.sources.spaces,
                ...this.sources.integrations,
            ];
        },
    },
    methods: {
        selectNewListSource(source) {
            this.processNewList(source, newCalendar, newExternalCalendar);
        },
        async processNewList(source, newListObj, newExternalListObj) {
            this.closeNewListDropdown();
            const clone = this.getNewClone(source, newListObj, newExternalListObj);

            this.$emit('addNewList', { newList: createCalendarFromObject(clone), source });
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-calendar-side {
    &__option {
        @apply
            flex
            items-center
            px-4
            py-1
            text-xs
            uppercase
            w-full
        ;

        &:hover {
            @apply
                bg-cm-100
            ;
        }
    }

    &__name {
        @apply
            font-semibold
            mb-1
            text-cm-400
            text-xs
            uppercase
        ;
    }
}

</style>
