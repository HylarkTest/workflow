<template>
    <div
        v-blur="closePopup"
        class="c-dropdown-basic"
        :class="{ unclickable: disabled }"
        @keydown.tab="closePopup"
    >
        <slot
            name="label"
            :popupState="isDropdownVisible"
        >
        </slot>

        <div
            ref="button"
            class="relative"
        >
            <ClearButton
                v-if="showClear && hasFormValue"
                :positioningClass="clearPositioning"
                @click="clearDropdown"
            >
            </ClearButton>

            <slot
                name="selected"
                :display="$slots.selected && displayFunction(valueObject, placeholder, false)"
                :popupState="isDropdownVisible"
                :selectedEvents="{ click: togglePopup, open: openPopup, enter: onEnter }"
                :closePopup="closePopup"
                :original="valueObject"
            >
                <button
                    type="button"
                    class="w-full"
                    @click="togglePopup"
                >
                    {{ $slots.selected || displayFunction(valueObject, placeholder, false) }}
                </button>
            </slot>
        </div>

        <!-- Not ideal, improve once there is more time -->
        <!-- Cannot use v-show here because the PopupBasic component is a
             Teleport component and the root node is not a standard element that
             can be hidden/shown. Instead there is a prop that tells the
             component to hide or show the main element. -->
        <PopupBasic
            v-if="shouldRenderPopup"
            ref="options"
            :hide="!shouldShowPopup"
            class="w-full text-sm"
            :matchWidth="!(!!optionsPopupProps.widthProp)"
            :activator="$refs.button"
            nudgeDownProp="0.375rem"
            v-bind="optionsPopupProps"
            :maxHeightProp="optionsPopupProps.maxHeightProp"
        >
            <div class="sticky top-0 bg-cm-00 z-over">
                <slot
                    name="noResults"
                    :selectedEvents="{ click: togglePopup }"
                >
                </slot>
            </div>

            <slot
                name="additional"
                :closePopup="closePopup"
            >
            </slot>

            <template
                v-if="isSearchable && !hideSearch"
                #popupTop
            >
                <slot
                    name="search"
                >
                    <InputLine
                        ref="input"
                        :modelValue="searchQuery"
                        class="w-full"
                        inputElClasses="pt-3 mb-2"
                        :placeholder="inputSearchPlaceholder"
                        :borderWidthClass="borderWidthClass"
                        @update:modelValue="$emit('update:searchQuery', $event)"
                        @keyup.enter.stop="selectSearch"
                    >

                        <template
                            v-if="addTypedValue"
                            #afterInput
                        >
                            <button
                                class="h-4 w-4 button-primary circle-center"
                                :class="{ unclickable: !searchQuery }"
                                type="submit"
                                :disabled="!searchQuery"
                                @click="addNew(searchQuery)"
                            >
                                <i class="fas fa-plus text-xs">
                                </i>
                            </button>
                        </template>
                    </InputLine>
                </slot>

                <slot
                    name="popupStart"
                >
                </slot>

            </template>
            <div
                v-if="hasGroups"
            >
                <template
                    v-for="(group, groupIndex) in groupedOptions"
                    :key="groupIndex"
                >
                    <slot
                        name="group"
                        :group="group.group"
                        :options="group.options"
                        :index="groupIndex"
                        :display="$slots.group && groupDisplayFunction(group.group)"
                    >
                        <div
                            v-if="group.group"
                            class="text-xs uppercase font-semibold text-cm-400 text-center mb-1 mt-4 first:mt-2"
                        >
                            {{ $slots.group || groupDisplayFunction(group.group) }}
                        </div>
                    </slot>

                    <ButtonEl
                        v-for="(option, index) in group.options"
                        :ref="setOptionsRef(option)"
                        :key="index"
                        type="button"
                        class="w-full"
                        :title="optionIsUnselectable(option) ? inactiveText : ''"
                        :class="{ unclickable: optionIsUnselectable(option) }"
                        :aria-disabled="optionIsUnselectable(option)"
                        @click="selectOption(option, index, group)"
                    >
                        <slot
                            name="option"
                            :index="index"
                            :display="$slots.option && displayFunction(option)"
                            :disabled="$slots.option && optionIsUnselectable(option)"
                            :original="option"
                            :group="group.group"
                            :position="whichPosition(index)"
                            :isSelected="isSelected(option)"
                            :selectedEvents="{ click: togglePopup }"
                            :isHovered="isHoveredInGroup(groupIndex, index)"
                            :isMultiSelect="isMultiSelect"
                            :hasRemoveIcon="selectedOptionHasRemoveIcon"
                        >
                            {{ $slots.option || displayFunction(option) }}
                        </slot>
                    </ButtonEl>
                    <slot
                        v-if="!group.options?.length"
                        name="emptyGroup"
                        :group="group.group"
                    >
                    </slot>
                    <slot
                        name="groupEnd"
                        :group="group.group"
                        :options="group.options"
                        :closePopup="closePopup"
                        :index="groupIndex"
                    >
                    </slot>
                </template>
            </div>
            <template
                v-else
            >
                <ButtonEl
                    v-for="(option, index) in visibleOptions"
                    :key="index"
                    :ref="setOptionsRef(option)"
                    :title="optionIsUnselectable(option) ? inactiveText : ''"
                    :class="{ unclickable: optionIsUnselectable(option) }"
                    :aria-disabled="optionIsUnselectable(option)"
                    type="button"
                    class="w-full"
                    @click="selectOption(option, index)"
                >
                    <slot
                        name="option"
                        :index="index"
                        :display="$slots.option && displayFunction(option)"
                        :disabled="$slots.option && optionIsUnselectable(option)"
                        :original="option"
                        :position="whichPosition(index)"
                        :isSelected="isSelected(option)"
                        :isHovered="hoveredIndex === index"
                        :selectedEvents="{ click: togglePopup }"
                        :isMultiSelect="isMultiSelect"
                        :hasCircleForSelected="hasCircleForSelected"
                        :hasRemoveIcon="selectedOptionHasRemoveIcon"
                    >
                        {{ $slots.option || displayFunction(option) }}
                    </slot>
                </ButtonEl>
            </template>
            <slot
                v-if="!hasOptions"
                name="noOptions"
            >
            </slot>

            <div class="sticky bottom-0 bg-cm-00">
                <slot
                    name="popupEnd"
                    :selectedEvents="{ click: togglePopup }"
                >
                </slot>
            </div>
        </PopupBasic>

        <transition
            name="t-fade"
        >
            <AlertTooltip
                v-if="errorMessage"
                :alertColor="alertColor"
            >
                {{ errorMessage }}
            </AlertTooltip>
        </transition>
    </div>
</template>

<script>
import Fuse from 'fuse.js';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';
import formWrapperChild from '@/vue-mixins/formWrapperChild.js';
import hasDropdownAwareArrowControls from '@/vue-mixins/hasDropdownAwareArrowControls.js';
import ClearButton from '@/components/buttons/ClearButton.vue';
import PopupBasic from '@/components/popups/PopupBasic.vue';

const filterOptionsObj = {
    shouldSort: true,
    threshold: 0.2,
    location: 0,
    distance: 100,
    maxPatternLength: 32,
    minMatchCharLength: 1,
};

export default {
    name: 'DropdownBasic',
    components: {
        PopupBasic,
        AlertTooltip,
        ClearButton,
    },
    mixins: [
        formWrapperChild,
        hasDropdownAwareArrowControls,
    ],
    props: {
        // A path name or callback used to extract the display string from the
        // option. This works in the same way as lodash functions like `map` and
        // is useful when the options are objects.
        // By default it uses the identity function which just returns the
        // option value.
        displayRule: {
            type: [Function, String],
            default: null,
        },
        groupDisplayRule: {
            type: [Function, String],
            default: null,
        },
        // A function to determine if an option should be disabled.
        inactiveOptionCondition: {
            type: [Function, null],
            default: null,
        },
        inactiveText: {
            type: String,
            default: 'This option cannot be selected',
        },
        showEmptyGroups: Boolean,
        // An array or object of options available in the dropdown. The dropdown
        // buttons are looped using the index as the key. If you need to control
        // the key then use an object.
        options: {
            type: [Array, Object, null],
            default: null,
            // Needed unless using groups
        },
        // Any props that should be bound to the options popup.
        optionsPopupProps: {
            type: [Object, null],
            default: null,
        },
        // A string that will be displayed if no option has been selected.
        placeholder: {
            type: String,
            default: '',
        },
        // The error prop itself is in the form mixin
        alertColor: {
            type: String,
            default: 'peach',
        },
        // A function that indicates if an option has not been selected. By
        // default it will assume that an option is not selected if the value
        // is `null`, `undefined` or an empty string.
        placeholderCondition: {
            type: Function,
            default: (value) => value == null || value === '',
        },
        // A path name or callback used to extract the emitted value from the
        // selected option. This works in the same way as lodash functions like
        // `map` and is useful when the options are objects.
        // By default it uses the identity function which just returns the
        // option value.
        property: {
            type: [Function, String],
            default: null,
        },
        showClear: Boolean,
        clearPositioning: {
            type: String,
            default: '-top-2 -right-2 absolute',
        },
        // Key whose value is compared.
        // Note: if "property" prop is a string, "comparator" prop must not also be a string.
        comparator: {
            type: [String, Function],
            default: () => _.isEqual,
        },
        popupConditionalDirective: {
            type: String,
            default: 'if',
        },
        // Dictates if the options can be searched
        isSearchable: Boolean,
        // A predicate that will be used to decide if an option matches the
        // search. If unset, this will default to the display predicate.
        searchableRule: {
            type: [Function, String],
            default: null,
        },
        // Prevent the dropdown closing on click.
        blockClose: Boolean,
        // A v-modelled property for filtering the results.
        searchQuery: {
            type: String,
            default: '',
        },
        groups: {
            type: Array,
            default: null,
        },
        inputSearchPlaceholder: {
            type: String,
            default: 'Search...',
        },
        borderWidthClass: {
            type: String,
            default: 'border-b',
        },
        addTypedValue: Boolean,
        useFormValueForDisplay: Boolean,
        enterSelectsOnlyOption: Boolean,
        disabled: Boolean,
        selectedOptionHasRemoveIcon: Boolean,
        hasCircleForSelected: Boolean,
        // Something else controls the popup
        forceDropdownVisible: {
            type: [Boolean, null],
            default: null,
        },
        // Has search functionality but not showing,
        // since query comes from elsewhere
        hideSearch: Boolean,
        hasEmitVisibleOptions: Boolean,
    },
    emits: [
        'update:modelValue',
        'update:searchQuery',
        'addNew',
        'select',
        'close',
        'clear',
        'visibleOptions',
    ],
    data() {
        return {
            dropdownVisible: false,
            finishedMounting: false,
            optionsRefs: new Map(),
        };
    },
    computed: {
        visibleOptions() {
            return this.filterOptions(this.options);
        },
        hasGroups() {
            return !!this.groups?.length;
        },
        groupedOptions() {
            return this.groups?.map((group) => {
                return {
                    ...group,
                    options: this.filterOptions(group.options),
                };
            }).filter(({ options }) => this.showEmptyGroups || !!options.length);
        },
        useShow() {
            return this.popupConditionalDirective === 'show';
        },
        optionsLength() {
            if (this.hasGroups) {
                return _.sumBy(
                    this.groupedOptions,
                    ({ options }) => (_.isObject(options) ? _.keys(options).length : options?.length)
                );
            }
            return _.isObject(this.options) ? _.keys(this.options).length : this.options?.length;
        },
        hasOptions() {
            if (this.hasGroups) {
                // The grouped options are filtered by whether or not they have
                // options, but that may not always be the case, so this is a
                // more robust way of checking.
                return !!_.sumBy(this.groupedOptions, 'options.length');
            }
            return !!this.visibleOptions.length;
        },
        propertyFn() {
            // The `iteratee` method is what lodash uses behind the scenes for
            // things like the `map` function. It returns a function that can
            // be used to extract a value from the argument of the function
            // depending on what was passed to the `iteratee` function.
            //
            // For example if `this.property` is a string then `propertyFn` will
            // be a function that extracts the value from an object defined by
            // the string.
            // If `this.property` is a function then it essentially returns that
            // function.
            // If `this.property` is null then it behaves like the `identity`
            // function.
            return _.iteratee(this.property);
        },
        valueObject() {
            if (this.useFormValueForDisplay) {
                return this.formValue;
            }
            const method = this.isMultiSelect ? 'filter' : 'find';
            if (this.hasGroups) {
                return _.flatMap(this.groups, 'options')[method](this.isSelected);
            }
            return _[method](this.options, this.isSelected);
        },
        shouldRenderPopup() {
            // Need to wait for mounting otherwise the activator ref won't exist
            if (!this.finishedMounting) {
                return false;
            }
            // If the `useShow` prop is true then the popup should always stay
            // rendered and the showing and hiding is controlled by v-show
            if (this.useShow) {
                return true;
            }

            return this.isDropdownVisible;
        },
        shouldShowPopup() {
            // If the `useShow` prop is false then the popup is controlled by
            // the v-if directive so we can always return true here.
            if (!this.useShow) {
                return true;
            }
            return this.isDropdownVisible;
        },
        isMultiSelect() {
            return _.isArray(this.formValue);
        },
        comparatorFn() {
            if (_.isString(this.comparator)) {
                return (value, selectionOption) => {
                    return value && (value[this.comparator] === this.propertyFn(selectionOption)[this.comparator]);
                };
            }
            return this.comparator;
        },
        firstSelected() {
            if (this.hasGroups) {
                for (const group of this.groupedOptions) {
                    const selected = group.options.find((option) => {
                        return this.isSelected(option);
                    });
                    if (selected) {
                        return selected;
                    }
                }
                return null;
            }
            return this.options?.find((option) => {
                return this.isSelected(option);
            });
        },
        emittedVisibleOptions() {
            if (!this.hasEmitVisibleOptions) {
                return false;
            }
            return this.visibleOptions;
        },
        isDropdownVisible() {
            if (_.isBoolean(this.forceDropdownVisible)) {
                return this.forceDropdownVisible;
            }
            return this.dropdownVisible;
        },

    },
    methods: {
        filterOptions(options) {
            if (!options) {
                return [];
            }
            if (!this.isSearchable || !this.searchQuery) {
                return options;
            }
            const predicate = this.searchableRule || this.displayRule;
            const keys = _.isString(predicate) ? [predicate] : [{ name: 'value', getFn: predicate }];

            return _.map((new Fuse(options, {
                ...filterOptionsObj,
                keys,
            })).search(this.searchQuery), 'item');
        },
        isHoveredInGroup(groupIndex, index) {
            return this.hoveredIndex === _(this.groups).slice(0, groupIndex).sumBy((g) => g.options.length) + index;
        },
        isSelected(option) {
            const optionProperty = this.propertyFn(option);
            if (this.isMultiSelect) {
                return this.formValue.some((value) => this.comparatorFn(value, optionProperty));
            }
            return this.comparatorFn(this.formValue, optionProperty);
        },
        displayFunction(value, defaultValue, isAnOption = true) {
            if ((defaultValue || defaultValue === '') && this.placeholderCondition(value)) {
                return defaultValue;
            }
            const fn = _.iteratee(this.displayRule);
            return fn(value, isAnOption) || '';
        },
        groupDisplayFunction(group) {
            const fn = _.iteratee(this.groupDisplayRule);
            return fn(group) || '';
        },
        openPopup() {
            this.dropdownVisible = true;
            this.$nextTick(() => {
                this.goToFirstSelected();
                this.focusOnInput();
            });
        },
        goToFirstSelected() {
            if (this.firstSelected) {
                const ref = this.optionsRefs.get(this.propertyFn(this.firstSelected));

                if (ref) {
                    ref.$el.scrollIntoView({ block: 'center' });
                }
            }
        },
        focusOnInput() {
            this.$refs.input?.focus();
        },
        togglePopup() {
            if (this.dropdownVisible) {
                this.closePopup();
            } else {
                this.openPopup();
            }
        },
        closePopup() {
            const wasOpen = this.isDropdownVisible;
            this.dropdownVisible = false;
            this.clearSearchQuery();
            if (wasOpen) {
                this.$emit('close');
            }
        },
        whichPosition(key) {
            let index;
            if (_.isObject(this.options)) {
                index = _.keys(this.options).indexOf(key);
            } else {
                index = key;
            }
            const length = this.optionsLength;
            if (length === 1) {
                return 'single';
            }
            if (index === 0) {
                return 'first';
            }
            if (index === (length - 1)) {
                return 'last';
            }
            return 'middle';
        },
        onSelectOption(index) {
            let option;
            if (this.hasGroups) {
                let relativeIndex = index;
                for (let i = 0; i < this.groups.length; i += 1) {
                    if (relativeIndex < this.groups[i].options.length) {
                        option = this.groups[i].options[relativeIndex];
                        break;
                    }
                    relativeIndex -= this.groups[i].options.length;
                }
            } else {
                option = this.visibleOptions[index];
            }
            this.selectOption(option, index);
        },
        optionIsUnselectable(option) {
            return this.inactiveOptionCondition && this.inactiveOptionCondition(option);
        },
        selectOption(option, index, group) {
            if (this.optionIsUnselectable(option)) {
                return;
            }
            if (this.isMultiSelect) {
                const i = _.findIndex(this.valueObject, (value) => {
                    return this.comparatorFn(value, option);
                });
                if (~i) {
                    this.emitInput([
                        ...this.formValue.slice(0, i),
                        ...this.formValue.slice(i + 1),
                    ]);
                } else {
                    this.emitInput([
                        ...this.formValue,
                        this.propertyFn(option, index),
                    ]);
                }
            } else {
                this.emitInput(this.propertyFn(option, index));
            }
            this.$emit('select', { option, index, group });
            if (!option.blockClose && !this.blockClose) {
                this.closePopup();
            }
        },
        clearDropdown() {
            this.closePopup();
            if (!this.isMultiSelect) {
                this.emitInput(null);
                this.$emit('clear');
            }
        },
        addNew(query) {
            this.$emit('addNew', query);
            if (!this.isMultiSelect) {
                this.closePopup();
            }
        },
        clearSearchQuery() {
            this.$emit('update:searchQuery', '');
        },
        onEnter() {
            if (this.enterSelectsOnlyOption) {
                if (this.hasGroups && this.groupedOptions.length === 1
                        && this.groupedOptions[0].options?.length === 1) {
                    this.selectOption(this.groupedOptions[0].options[0], 0);
                } else if (this.visibleOptions.length === 1) {
                    this.selectOption(this.visibleOptions[0], 0);
                }
            }
        },
        selectSearch() {
            if (this.addTypedValue && this.searchQuery) {
                this.addNew(this.searchQuery);
            }
        },
        setOptionsRef(option) {
            return (el) => {
                this.optionsRefs.set(this.propertyFn(option), el);
            };
        },
    },
    watch: {
        async options() {
            await this.$nextTick();
            const firstRef = this.options.length && this.optionsRefs.get(this.propertyFn(this.options[0]));
            if (firstRef) {
                firstRef.$el.scrollIntoView();
            }
        },
        emittedVisibleOptions: {
            deep: true,
            immediate: true,
            handler(newVal) {
                // Needs popupConditionalDirective to be show
                this.$emit('visibleOptions', newVal);
            },
        },
    },
    mounted() {
        this.finishedMounting = true;
    },
};
</script>

<style scoped>
    .c-dropdown-basic {
        @apply relative;

    }
</style>
