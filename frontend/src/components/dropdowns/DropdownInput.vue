<template>
    <component
        :is="dropdownComponent"
        class="c-dropdown-input"
        :options="validOptions"
        :groups="groups"
        enterSelectsOnlyOption
        :bgColor="bgColor"
        :showClear="showClear"
        v-bind="$attrs"
    >
        <!-- #selected="{ display, selectedEvents, original, closePopup }" -->
        <template
            #selected="{ selectedEvents, popupState }"
        >
            <component
                ref="input"
                :is="inputComponent"
                class="mr-1 w-full"
                :modelValue="inputVal"
                :focusClasses="focusClasses"
                :placeholder="placeholder"
                :bgColor="bgColor"
                name="other"
                :neverHighlighted="neverHighlighted"
                @update:modelValue="updateInput"
                @click.stop="onClick($event, selectedEvents.open, popupState)"
                @keydown="onClick($event, selectedEvents.open, popupState)"
                @keyup.enter.stop="selectedEvents.enter"
                @keydown.enter.prevent
                @keydown.space.prevent.stop="clickSpace"
            >
            </component>
        </template>

        <template
            v-for="(_, slot) in proxySlots()"
            #[slot]="scope"
        >
            <slot
                :name="slot"
                v-bind="scope"
            />
        </template>
        <template
            #noResults="{ selectedEvents }"
        >
            <div
                v-if="noResults || $slots.postTop"
                class="py-2 text-xs flex flex-col items-center"
            >
                <template
                    v-if="processing"
                >
                    <LoaderFetch
                        :sphereSize="20"
                    >
                    </LoaderFetch>
                </template>

                <template
                    v-if="!processing && inputVal && inputVal.length && !resultsLength"
                >
                    No matches found
                </template>

                <template v-if="!processing && (!inputVal || !inputVal.length)">
                    Type to fetch results!
                </template>

                <template
                    v-if="$slots.postTop"
                >
                    <slot
                        name="postTop"
                        :inputVal="inputVal"
                        :selectedEvents="selectedEvents"
                        :processing="processing"
                        :noResults="noResults"
                    >
                    </slot>
                </template>

            </div>
        </template>
    </component>
</template>

<script>

import LoaderProcessing from '@/components/loaders/LoaderProcessing.vue';

export default {
    name: 'DropdownInput',
    components: {
        LoaderProcessing,
    },
    mixins: [
    ],
    props: {
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
        inputVal: {
            type: String,
            required: true,
        },
        options: {
            type: Array,
            default: () => ([]),
        },
        allOptions: {
            type: [null, Array],
            default: null,
        },
        inputComponent: {
            type: String,
            default: 'InputSubtle',
        },
        groups: {
            type: Array,
            default: null,
        },
        focusClasses: {
            type: String,
            default: 'bg-primary-100 shadow-lg shadow-primary-600/20',
        },
        placeholder: {
            type: String,
            default: '',
        },
        neverHighlighted: Boolean,
        processing: Boolean,
        bgColor: {
            type: String,
            default: 'white',
            validator(value) {
                return ['white', 'gray'].includes(value);
            },
        },
        showClear: Boolean,
    },
    emits: [
        'update:inputVal',
    ],
    data() {
        return {
            firstDropdownOpen: true,
        };
    },
    computed: {
        noResults() {
            return this.resultsLength === 0;
        },
        resultsLength() {
            if (this.groups) {
                return this.groups.length;
            }
            return this.options.length;
        },
        validOptions() {
            if (this.allOptions) {
                return this.firstDropdownOpen ? this.allOptions : this.options;
            }
            return this.options;
        },
    },
    methods: {
        // proxySlots needs to be a method because $slots is not reactive (See DropdownBox for more info).
        proxySlots() {
            return _.omit(this.$slots, ['selected', 'popupEnd']);
        },
        updateInput(val) {
            if (this.firstDropdownOpen) {
                this.firstDropdownOpen = false;
            }
            this.$emit('update:inputVal', val);
        },
        onClick(event, focusCallback, popupState) {
            if (!popupState) {
                focusCallback(event);
                this.$refs.input.select();
            }
        },
        clickSpace() {
            // Please improve if you have a straight forward way.
            // For Dropdown Inputs, the space closes the popup due the the function
            // onClick, along with the focus on the DropdownDisplay.
            // To prevent that and still let the space work, the code below was added.
            // Maybe a revision of key shortcuts required.
            const val = `${this.inputVal} `;
            this.updateInput(val);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-dropdown-input {

} */

</style>
