<template>
    <div
        v-if="dataValue"
        class="c-displayer-email text-sm flex items-baseline"
    >
        <a
            class="c-displayer-email__email transition-2eio hyphens-auto block"
            :class="displayClasses"
            :href="'mailto:' + displayFieldValue"
            @click.stop
        >
            {{ displayFieldValue }}
        </a>

        <button
            v-if="hasIntegrations && isModifiable"
            class="c-displayer-email__send centered button-primary--medium"
            type="button"
            title="Send an email through Hylark"
            @click.stop="openEmail"
        >
            <i class="far fa-envelope">
            </i>
        </button>

        <SideDialog
            :sideOpen="isEmailOpen"
            @closeSide="closeEmail"
        >
            <EmailWrite
                :mailbox="null"
                :email="null"
                :toEmailAddresses="toEmails"
                :emailAddressesForAssociation="emailAddressesForAssociation"
                :node="item"
                action="COMPOSE"
                @closeEmail="closeEmail"
            >
            </EmailWrite>
        </SideDialog>

    </div>
</template>

<script>

import EmailWrite from '@/components/emails/EmailWrite.vue';
import SideDialog from '@/components/dialogs/SideDialog.vue';

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

import fetchesEmailIntegrations from '@/vue-mixins/emails/fetchesEmailIntegrations.js';

export default {
    name: 'DisplayerEmail',
    components: {
        EmailWrite,
        SideDialog,
    },
    mixins: [
        interactsWithDisplayers,
        fetchesEmailIntegrations,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'EMAIL',
            isEmailOpen: false,
        };
    },
    computed: {
        emailRecord() {
            if (this.item) {
                return {
                    id: this.item.id,
                    name: this.item.name,
                };
            }
            return null;
        },
        fieldValue() {
            return this.dataValue.fieldValue;
        },
        hasIntegrations() {
            return !!(this.emailIntegrationsLength && !this.isLoadingIntegrations);
        },
        toEmails() {
            return [
                {
                    email: this.fieldValue,
                    record: this.emailRecord,
                },
            ];
        },
        emailAddressesForAssociation() {
            return [
                this.fieldValue,
            ];
        },
    },
    methods: {
        openEmail() {
            this.isEmailOpen = true;
        },
        closeEmail() {
            this.isEmailOpen = false;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-displayer-email {
    &__email:hover {
        @apply
            text-sky-500
        ;
    }

    &__send {
        @apply
            h-6
            leading-none
            ml-2
            rounded-md
            shrink-0
            text-xssm
            w-6
        ;
    }
}

</style>
