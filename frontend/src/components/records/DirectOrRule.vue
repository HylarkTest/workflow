<template>
    <div
        class="o-direct-or-rule"
        :class="{ unclickable: processing }"
    >
        <h2 class="header-2 text-center">
            What type of association would you like to create?
        </h2>

        <div>
            <SnazzyOption
                class="mb-2"
                :class="{ unclickable: blockSpecific }"
                @click="setSpecificAssociation"
            >
                <p class="font-semibold">
                    Specific
                </p>
                <p class="text-cm-600 font-light leading-snug text-xs">
                    Create an association only between this record and this email.
                </p>
            </SnazzyOption>

            <div
                class="h-divider my-4"
            >
            </div>

            <div class="text-sm px-4">
                <p class="font-semibold">
                    To an email address
                </p>
                <p class="text-cm-600 font-light leading-snug text-xs">
                    Create an association between this record and any email involving the email address.
                    All emails from the linked address will be accessible from this
                    record's email tab.
                </p>

                <div class="mt-2">
                    <SnazzyOption
                        v-for="(email, index) in emailValues"
                        :key="index"
                        class="mb-2 last:mb-2"
                        :class="{ 'bg-emerald-100 pointer-events-none': isConnectedAddress(email) }"
                        @click="setEmailAssociation(email)"
                    >
                        <div class="flex justify-between items-center">
                            {{ email }}

                            <i
                                v-if="isConnectedAddress(email)"
                                class="fa-solid fa-circle-check text-base text-emerald-600"
                            >
                            </i>
                        </div>
                    </SnazzyOption>
                </div>
            </div>

            <div
                v-if="connectedAddressesLength"
                class="flex justify-center mt-8"
            >
                <button
                    class="button button-primary"
                    type="button"
                    @click="closeModal"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import SnazzyOption from '@/components/buttons/SnazzyOption.vue';

import { getFieldValuesByType } from '@/core/display/theStandardizer.js';
import { associateEmailAddress } from '@/core/repositories/itemRepository.js';

export default {
    name: 'DirectOrRule',
    components: {
        SnazzyOption,
    },
    mixins: [
    ],
    props: {
        // Expects record to have the full mapping object
        record: {
            type: Object,
            required: true,
        },
        account: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'setSpecificAssociation',
        'closeModal',
    ],
    data() {
        return {
            connectedAddresses: [],
            processing: false,
        };
    },
    computed: {
        fields() {
            return this.record.mapping.fields;
        },
        emailValues() {
            return getFieldValuesByType(this.fields, this.record.data, 'EMAIL');
        },
        connectedAddressesLength() {
            return this.connectedAddresses.length;
        },
        showDone() {
            return this.connectedAddressesLength;
        },
        blockSpecific() {
            // To avoid double opacity
            return this.connectedAddressesLength && !this.processing;
        },
    },
    methods: {
        setSpecificAssociation() {
            this.$emit('setSpecificAssociation');
        },
        async setEmailAssociation(address) {
            this.processing = true;
            try {
                await associateEmailAddress(address, this.record, this.account);
                this.$saveFeedback(
                    {
                        customMessageString: `Your record was associated to emails involving ${address}`,
                    });
                this.connectedAddresses.push(address);
                if (this.connectedAddressesLength === this.emailValues.length) {
                    this.$emit('closeModal');
                }
            } finally {
                this.processing = false;
            }
        },
        isConnectedAddress(address) {
            return this.connectedAddresses.includes(address);
        },
        closeModal() {
            this.$emit('closeModal');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-direct-or-rule {
}*/

</style>
