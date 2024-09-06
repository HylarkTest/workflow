<template>
    <div class="o-associate-email-addresses">
        <h1 class="header-2 text-center mb-2">
            Manually associate email addresses
        </h1>

        <div class="text-sm text-cm-600 bg-cm-100 rounded-xl p-4 mb-4">
            <p>
                Add associations to connect email addresses you correspond with to Hylark data.
                Once connected, you will be able to access associated emails from within your Hylark data directly.
            </p>
        </div>

        <div>
            <div
                v-for="address in emailAddresses"
                :key="address"
                class="mb-6 last:mb-0"
            >
                <div class="text-primary-700 font-semibold">
                    {{ address }}
                </div>

                <div>
                    <EntitiesPicker
                        bgColor="gray"
                        :withFeatures="['EMAILS']"
                        :entityVal="null"
                        @update:entityVal="addAssociation($event, address)"
                    >
                    </EntitiesPicker>
                </div>

                <div
                    v-if="hasEmailConnectedRecordsForAddress(address)"
                    class="flex flex-wrap gap-1 mt-2"
                >
                    <ConnectedRecord
                        v-for="addressAssociation in associationsForAddress(address)"
                        :key="addressAssociation.id"
                        :item="addressAssociation.association"
                        :showClear="true"
                        bgColor="gray"
                        @removeItem="removeAssociation($event, address)"
                    >
                    </ConnectedRecord>
                </div>
            </div>
        </div>

        <!-- <div class="text-sm text-cm-600 bg-gold-100 rounded-xl p-4">
            <p>
                *Hylark does not store your emails. Emails are fetched using partner APIs,
                and, as such, there are some limitations.
            </p>
        </div> -->
    </div>
</template>

<script>

import EntitiesPicker from '@/components/pickers/EntitiesPicker.vue';
import { associateEmailAddress, dissociateEmailAddress } from '@/core/repositories/itemRepository.js';
import EMAIL_ADDRESS_ASSOCIATIONS from '@/graphql/mail/queries/EmailAddressAssociations.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'AssociateEmailAddresses',
    components: {
        EntitiesPicker,
    },
    mixins: [
    ],
    props: {
        email: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        emailAddressAssociations: {
            query: EMAIL_ADDRESS_ASSOCIATIONS,
            variables() {
                return {
                    addresses: this.email.correspondentAddresses(),
                };
            },
            update: (data) => initializeConnections(data).emailAddressAssociations,
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {
        };
    },
    computed: {
        associations() {
            return _.groupBy(this.emailAddressAssociations, 'address');
        },
        emailAddresses() {
            return this.email.correspondentAddresses();
        },

    },
    methods: {
        hasEmailConnectedRecordsForAddress(address) {
            return !!this.associationsForAddress(address).length;
        },
        associationsForAddress(address) {
            return this.associations[address] || [];
        },
        addAssociation(node, address) {
            return associateEmailAddress(address, node, this.email.account);
        },
        removeAssociation(node, address) {
            return dissociateEmailAddress(address, node, this.email.account);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-associate-email-addresses {

} */

</style>
