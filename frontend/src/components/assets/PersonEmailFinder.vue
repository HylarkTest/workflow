<template>
    <div class="c-person-email-finder">
        <EntitiesPicker
            ref="picker"
            v-model:inputVal="searchParameter"
            :entityVal="null"
            :placeholder="placeholder"
            dropdownComponent="DropdownFree"
            hasEmails
            :error="errorMessage"
            v-bind="$attrs"
        >
            <template
                #wholeOption="{ original, selectedEvents }"
            >
                <div
                    class="text-xssm py-1 px-3"
                    :class="{ 'hover:bg-cm-100': hasOneEmail(original) }"
                    @click="selectRecord(original, selectedEvents)"
                >
                    <div class="font-semibold">
                        {{ original.name }}
                    </div>

                    <div
                        v-if="hasOneEmail(original)"
                        class="text-xs text-cm-600 break-all"
                    >
                        {{ original.emails[0] }}
                    </div>
                    <div
                        v-else
                        class="text-xs text-cm-600 ml-1 flex flex-col items-start"
                    >
                        <div
                            v-for="email in original.emails"
                            :key="email"
                            class="py-0.5 px-2 hover:bg-cm-100 rounded break-all"
                            @click.stop="selectEmail(original, email, selectedEvents)"
                        >
                            {{ email }}
                        </div>

                    </div>
                </div>
            </template>

            <template
                #postTop="{
                    selectedEvents,
                    processing,
                    inputVal,
                    noResults,
                }"
            >
                <button
                    v-if="noResults && inputVal && !processing"
                    class="button--sm button-primary--light mt-2"
                    :class="{ unclickable: searchParameterInvalid }"
                    type="button"
                    @click="selectNewEmail(searchParameter, selectedEvents)"
                >
                    Add email
                </button>
            </template>
        </EntitiesPicker>

        <div
            v-if="selectedRecordsLength"
            class="flex flex-wrap text-xs mt-1 gap-1"
        >
            <EmailDisplay
                v-for="{ record, email } in selectedRecords"
                :key="record ? record.id : email"
                :record="record"
                :email="email"
                :showClear="true"
                @removeEmail="removeEmail"
            >
            </EmailDisplay>
        </div>
    </div>
</template>

<script>

import { arrRemoveIndex } from '@/core/utils.js';
import { checkIsEmailValid } from '@/core/validation.js';
import EntitiesPicker from '@/components/pickers/EntitiesPicker.vue';
import EmailDisplay from '@/components/records/EmailDisplay.vue';
// import DropdownOptions from '@/components/dropdowns/DropdownOptions.vue';

export default {
    name: 'PersonEmailFinder',
    components: {
        EntitiesPicker,
        EmailDisplay,
        // DropdownOptions,
    },
    mixins: [
    ],
    props: {
        findBasedOn: {
            type: Array,
            default() {
                return ['NAME', 'EMAIL'];
            },
        },
        placeholder: {
            type: String,
            default: 'Find people',
        },
        selectedRecords: {
            type: Array,
            default: () => ([]),
        },
        error: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:selectedRecords',
    ],
    data() {
        return {
            searchParameter: '',
            searchParameterInvalid: false,
        };
    },
    computed: {
        errorMessage() {
            return this.searchParameterInvalid
                ? 'Please add a valid email address'
                : this.error;
        },
        selectedRecordsLength() {
            return this.selectedRecords.length;
        },
    },
    methods: {
        getKey(record) {
            return _.isString(record) ? record : record.id;
        },
        selectEmail(record, email, selectedEvents) {
            selectedEvents.click();
            this.searchParameter = '';
            const index = _.findIndex(this.selectedRecords, { email });
            let payload;
            if (!~index) {
                payload = [...this.selectedRecords, { record, email }];
                this.emitRecords(payload);
            }
        },
        removeEmail(email) {
            const index = _.findIndex(this.selectedRecords, { email });
            const payload = arrRemoveIndex(this.selectedRecords, index);
            this.emitRecords(payload);
        },
        selectRecord(record, selectedEvents) {
            if (this.hasOneEmail(record)) {
                this.selectEmail(record, record.emails[0], selectedEvents);
            }
        },
        selectNewEmail(searchParameter, selectedEvents) {
            const isEmailValid = checkIsEmailValid(searchParameter);
            if (isEmailValid) {
                this.selectEmail(null, searchParameter, selectedEvents);
            } else {
                const element = this.$refs.picker?.$refs.dropdownInput?.$refs.input;
                element?.select();
                this.searchParameterInvalid = true;
            }
        },
        hasOneEmail(item) {
            return item.emails.length === 1;
        },
        emitRecords(payload) {
            this.$emit('update:selectedRecords', payload);
        },
    },
    watch: {
        searchParameter() {
            if (this.searchParameterInvalid) {
                this.searchParameterInvalid = false;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-person-finder {

}*/

</style>
