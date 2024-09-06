<template>
    <Modal
        class="o-integrations-selection-modal"
        containerClass="p-8"
        v-bind="$attrs"
        @closeModal="$emit('closeModal')"
    >
        <h3 class="text-xl font-semibold mb-4 text-center">
            {{ title }}
        </h3>

        <div class="flex flex-col items-center">
            <button
                v-for="integration in integrationsForType"
                :key="integration.id"
                class="button--sm button-primary--border mb-1 last:mb-0 items-center flex"
                type="button"
                @click="selectIntegration(integration)"
            >
                <i
                    class="mr-2 text-primary-600"
                    :class="integrationIcon(integration)"
                >
                </i>

                {{ integration.accountName }}
            </button>
        </div>
    </Modal>
</template>

<script>

import { getIntegrationIcon } from '@/core/display/integrationIcons.js';

export default {
    name: 'IntegrationsSelectionModal',
    components: {
    },
    mixins: [
    ],
    props: {
        integrations: {
            type: Array,
            required: true,
        },
        integrationType: {
            type: String,
            default: 'ALL',
            validation(val) {
                return ['ALL', 'EMAILS', 'TODOS', 'EVENTS'].includes(val);
            },
        },
        title: {
            type: String,
            default: 'Select integration',
        },
    },
    emits: [
        'closeModal',
        'selectIntegration',
    ],
    data() {
        return {

        };
    },
    computed: {
        integrationsForType() {
            if (this.integrationType === 'ALL') {
                return this.integrations;
            }
            return this.integrations?.filter((integration) => {
                return integration.scopes.includes(this.integrationType);
            });
        },
    },
    methods: {
        selectIntegration(integration) {
            this.$emit('selectIntegration', integration);
        },
        integrationIcon(integration) {
            return getIntegrationIcon(integration.provider);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-integrations-selection-modal {

} */

</style>
