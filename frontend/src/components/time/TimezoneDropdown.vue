<template>
    <div class="c-timezone-dropdown">
        <DropdownInput
            v-model:inputVal="filters.freeText"
            v-blur="selectIfOneResultOrReset"
            class="w-full"
            :options="filteredTimezones"
            :allOptions="timezoneOptions"
            :displayRule="timezoneDisplay"
            :modelValue="timezone"
            focusClasses="bg-secondary-100 shadow-secondary-700/30"
            popupConditionalDirective="show"
            dropdownComponent="DropdownLine"
            @update:modelValue="selectTimezone"
        >
        </DropdownInput>
        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
            >
                {{ error }}
            </AlertTooltip>
        </transition>
    </div>
</template>

<script>

import getTimezones from '@/core/timezones.js';
import filterList from '@/core/filterList.js';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

const timezones = getTimezones();

export default {
    name: 'TimezoneDropdown',
    components: {
        AlertTooltip,
    },
    mixins: [
    ],
    props: {
        modelValue: {
            type: String,
            required: true,
        },
        error: {
            type: String,
            default: '',
        },
        allowNull: Boolean,
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        const timezone = _.find(timezones, ['value', this.modelValue]);
        return {
            filters: {
                freeText: timezone?.long || '',
            },
        };
    },
    computed: {
        timezoneOptions() {
            return timezones;
        },
        filteredTimezones() {
            if (this.timezone && this.filters.freeText === this.timezone.long) {
                return [this.timezone];
            }
            return filterList(this.timezoneOptions, this.filters, { keys: ['acronym', 'long'], threshold: 0.2 });
        },
        timezone() {
            return _.find(timezones, ['value', this.modelValue]);
        },
    },
    methods: {
        updateInput(val) {
            this.filters.freeText = val?.long || '';
        },
        selectTimezone(timezone) {
            this.$emit('update:modelValue', timezone?.value || null);
            this.updateInput(timezone);
        },
        selectIfOneResultOrReset() {
            if (this.filteredTimezones.length === 1) {
                if (this.filteredTimezones[0].value === this.modelValue) {
                    return;
                }
                this.selectTimezone(this.filteredTimezones[0]);
            } else {
                this.selectTimezone(this.timezone);
            }
        },
    },
    watch: {
        timezone(timezone) {
            this.updateInput(timezone);
        },
        'filters.freeText': function onFilterChange(filter) {
            if (this.allowNull && filter !== this.timezone?.long) {
                this.$emit('update:modelValue', null);
            }
        },
    },
    created() {
        this.timezoneDisplay = 'long';
    },
};
</script>

<style scoped>

.c-timezone-dropdown {
    max-width: 400px;
    @apply
        relative
    ;
}

</style>
