<template>
    <div class="o-email-associations flex items-end flex-col">
        <div class="flex gap-2 flex-wrap">
            <EntitiesPicker
                bgColor="gray"
                :withFeatures="['EMAILS']"
                :entityVal="null"
                :showCreateNew="true"
                :suggestedRecords="suggestedRecords"
                :nodeToAssociate="email"
                placeholder="Associate message"
                @update:entityVal="addAssociation"
            >
            </EntitiesPicker>

            <button
                class="button--sm button-primary--light relative"
                type="button"
                @click="openModal"
            >
                <i class="fa-regular fa-at mr-0.5">
                </i>

                Associate email addresses

                <span
                    v-if="addressAssociationsLength"
                    class="o-email-associations__number circle-center"
                >
                    {{ addressAssociationsLength }}
                </span>
            </button>
        </div>

        <div
            v-if="associationsLength"
            class="mt-2 flex gap-1 flex-wrap"
        >
            <ConnectedRecord
                v-for="association in associations"
                :key="association.id"
                :item="association"
                bgColor="gray"
                :showClear="true"
                :deactivated="nodeIsBeingRemoved(association.id)"
                @removeItem="removeAssociation"
                @click.stop
            >
            </ConnectedRecord>
        </div>

        <AssociateEmailsModal
            v-if="isModalOpen"
            :email="email"
            @closeModal="closeModal"
        >
        </AssociateEmailsModal>
    </div>
</template>

<script>

import EntitiesPicker from '@/components/pickers/EntitiesPicker.vue';
import AssociateEmailsModal from '@/components/records/AssociateEmailsModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import { associateItem, removeItem } from '@/core/repositories/itemRepository.js';
import { arrRemove } from '@/core/utils.js';

import EMAIL_ADDRESS_ASSOCIATIONS from '@/graphql/mail/queries/EmailAddressAssociations.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'EmailAssociations',
    components: {
        EntitiesPicker,
        AssociateEmailsModal,
    },
    mixins: [
        interactsWithModal,
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
            nodesForRemoval: [],
        };
    },
    computed: {
        associations() {
            return this.email.associations;
        },
        associationsLength() {
            return this.associations.length;
        },
        addressAssociationsLength() {
            return this.emailAddressAssociations?.length || 0;
        },
        correspondents() {
            return this.email.correspondents();
        },
        suggestedRecords() {
            return this.correspondents?.map((person) => {
                return {
                    SYSTEM_NAME: person.name,
                    EMAIL: person.address,
                    id: person.address,
                };
            });
        },
    },
    methods: {
        addAssociation(node) {
            return associateItem(this.email, node);
        },
        async removeAssociation(node) {
            this.nodesForRemoval.push(node.id);
            await removeItem(this.email, node);
            this.nodesForRemoval = arrRemove(this.nodesForRemoval, node.id);
        },
        nodeIsBeingRemoved(id) {
            return this.nodesForRemoval.includes(id);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-email-associations {
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
