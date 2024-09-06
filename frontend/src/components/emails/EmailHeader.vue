<template>
    <div class="o-email-header">
        <div
            class="border-b border-solid border-cm-200 w-full"
            :class="hasReducedPadding ? 'pb-4' : 'px-6 py-4'"
        >
            <div
                v-if="!hideHeader"
                class="flex mb-2"
            >
                <MaximizeSide
                    v-if="isSideMinimized"
                    class="mr-6"
                    @click="minimizeSide"
                >
                </MaximizeSide>

                <div class="flex justify-between flex-wrap flex-1">
                    <div
                        v-if="!hasFreeFilterOn && list"
                        class="flex items-baseline"
                    >
                        <i
                            v-if="provider"
                            class="mr-3 text-2xl text-primary-300"
                            :class="integrationIcon"
                        >
                        </i>

                        <h2
                            class="header-list"
                        >
                            {{ list.name }}
                        </h2>
                    </div>
                    <div
                        v-else
                        class="header-list"
                    >
                        {{ resultsHeader }}
                    </div>
                </div>
            </div>
            <div
                class="flex justify-end"
            >
                <button
                    class="button--sm button-secondary"
                    type="button"
                    @click="composeEmail"
                >
                    <i class="fa-solid fa-pencil mr-1">
                    </i>

                    Compose

                </button>

                <slot
                    name="headerButtonOption"
                >
                </slot>
            </div>
        </div>

        <SideDialog
            :sideOpen="isEmailOpen"
            @closeSide="closeEmail"
        >
            <EmailWrite
                :mailbox="list"
                :email="emailWriteObj?.email"
                :action="emailWriteObj?.action"
                :suggestedEmailAddresses="suggestedEmailAddresses"
                :lastUsedIntegration="lastUsedIntegration"
                @closeEmail="closeEmail"
            >
            </EmailWrite>
        </SideDialog>
    </div>
</template>

<script>

import EmailWrite from './EmailWrite.vue';
import SideDialog from '@/components/dialogs/SideDialog.vue';

import interactsWithMaximize from '@/vue-mixins/common/interactsWithMaximize.js';

import { getIntegrationIcon } from '@/core/display/integrationIcons.js';

export default {
    name: 'EmailHeader',
    components: {
        EmailWrite,
        SideDialog,
    },
    mixins: [
        interactsWithMaximize,
    ],
    props: {
        filtersObj: {
            type: Object,
            required: true,
        },
        list: {
            type: [Object, null],
            default: null,
        },
        hideHeader: Boolean,
        isLoading: Boolean,
        hasReducedPadding: Boolean,
        lastUsedIntegration: {
            type: [String, null],
            default: '',
        },
        suggestedEmailAddresses: {
            type: [Array, null],
            default: null,
        },
    },
    emits: [
        'minimizeSide',
    ],
    data() {
        return {
            isEmailOpen: false,
            emailWriteObj: null,
        };
    },
    computed: {
        resultsHeader() {
            const results = this.$t('common.results');
            if (this.hasFreeFilterOn) {
                return results;
            }
            if (this.onAll) {
                return this.$t('common.all');
            }
            return results;
        },
        listAccount() {
            return this.list?.account;
        },
        provider() {
            return this.listAccount?.provider;
        },
        integrationIcon() {
            return getIntegrationIcon(this.provider);
        },
        isExternalList() {
            return this.list?.isExternalList();
        },
        hasFreeFilterOn() {
            return this.filtersObj.freeText;
        },
        onAll() {
            return this.filtersObj.filter === 'all';
        },
    },
    methods: {
        composeEmail() {
            this.openEmail({ action: 'COMPOSE' });
        },
        openEmail(emailObj) {
            this.emailWriteObj = emailObj;
            this.isEmailOpen = true;
        },
        closeEmail() {
            this.isEmailOpen = false;
            this.emailWriteObj = null;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-email-header {

} */

</style>
