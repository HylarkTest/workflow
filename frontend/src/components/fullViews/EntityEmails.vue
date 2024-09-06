<template>
    <div class="o-entity-emails pb-4">
        <EmailsLayout
            v-if="emailIntegrationsLength"
            featureType="EMAILS"
            :page="null"
            :suggestedEmailAddresses="suggestedEmailAddresses"
            :emailAddressesForAssociation="emailAddressesForAssociation"
            :lastUsedIntegration="lastUsedIntegration"
            :integrations="integrations"
            :emailIntegrationsLength="emailIntegrationsLength"
            :integrationLists="integrationLists"
            :renewals="renewals"
            :isLoading="isLoading"
            :sourceLists="sourceLists"
            :node="item"
            :showTotal="true"
            defaultFilter="all"
            :forceResponsiveDisplay="true"
            :hasReducedPadding="true"
            :contextSideFilters="['all']"
            :topHeaderClass="topHeaderClass"
            :deleteListFunction="deleteListFunction"
            :createListFromObjectFunction="createListFromObjectFunction"
            :updateListFunction="updateListFunction"
            :createListFunction="createListFunction"
            :moveListFunction="moveListFunction"
        >
            <template
                #headerButtonOption
            >
                <button
                    class="button--sm button-primary relative ml-2"
                    type="button"
                    @click="openModal"
                >
                    <i class="fa-regular fa-at mr-0.5">
                    </i>
                    Link email addresses to this record

                    <span
                        v-if="addressAssociationsLength"
                        class="o-entity-emails__number circle-center"
                    >
                        {{ addressAssociationsLength }}
                    </span>
                </button>
            </template>
        </EmailsLayout>

        <LoaderFetch
            v-if="isLoading"
            class="py-10"
            :isFull="true"
            :sphereSize="40"
        >
        </LoaderFetch>

        <AssociateEmailsModal
            v-if="isModalOpen"
            :record="item"
            :suggestedEmailAddresses="mappingEmailAddresses"
            viewType="FROM_RECORD"
            @closeModal="closeModal"
        >
        </AssociateEmailsModal>
    </div>
</template>

<script>

import EmailsLayout from '@/components/emails/EmailsLayout.vue';
import AssociateEmailsModal from '@/components/records/AssociateEmailsModal.vue';

import providesEmailsPageEssentials from '@/vue-mixins/emails/providesEmailsPageEssentials.js';
import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'EntityEmails',
    components: {
        EmailsLayout,
        AssociateEmailsModal,
    },
    mixins: [
        providesEmailsPageEssentials,
        interactsWithModal,
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
        topHeaderClass: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        sourceLists() {
            return [];
        },
        lastUsedIntegration() {
            // TODO: Figure out another way to do this
            return null;
        },
        fields() {
            return this.mapping.fields;
        },
        emailFields() {
            return this.fields.filter((field) => {
                return field.type === 'EMAIL';
            });
        },
        itemName() {
            return this.item.name;
        },
        mappingEmailAddresses() {
            // Need to pick out from multifield eventually
            // Potentially use full data functions
            const recordObj = {
                name: this.itemName,
                id: this.item.id,
            };
            return _(this.emailFields).flatMap((field) => {
                const item = this.item.data;
                const id = field.id;
                const baseVal = item[id];

                if (!baseVal) {
                    return null;
                }

                // List fields
                const options = field.options;
                if (options?.list) {
                    return baseVal.listValue.map((val) => {
                        return {
                            email: val.fieldValue,
                            record: recordObj,
                        };
                    });
                }

                // Single value fields
                let email = baseVal;
                if (baseVal.fieldValue) {
                    email = baseVal.fieldValue;
                }
                return {
                    email,
                    record: recordObj,
                };
            }).compact().value();
        },
        emailAddressesForAssociation() {
            return this.mappingEmailAddresses.map((emailObj) => {
                return emailObj.email;
            });
        },
        connectedEmailAddresses() {
            return this.item.features?.EMAILS_ASSOCIATED_ADDRESSES || [];
        },
        allEmailAddresses() {
            return this.mappingEmailAddresses.concat(this.connectedEmailAddresses);
        },
        suggestedEmailAddresses() {
            const emails = [];
            this.allEmailAddresses.forEach((emailObj) => {
                const index = _.findIndex(emails, { email: emailObj.email });

                if (~index) {
                    const newVal = {
                        ...emailObj,
                        ...emails[index],
                    };
                    emails.splice(index, 1, newVal);
                } else {
                    emails.push(emailObj);
                }
            });
            return emails;
        },
        addressAssociationsLength() {
            return this.connectedEmailAddresses?.length;
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

.o-entity-emails {
    &__number {
        height: 20px;
        min-width: 20px;

        @apply
            absolute
            bg-cm-00
            border
            border-primary-600
            border-solid
            -right-2
            text-primary-600
            -top-2
            z-over
        ;
    }
}

</style>
