<template>
    <div class="o-feature-source flex flex-wrap text-xs gap-3">
        <div
            v-if="provider"
            class="flex items-center"
        >
            <i
                class="mr-1 text-primary-300"
                :class="integrationIcon"
            >
            </i>
            <span
                class="font-medium text-cm-600"
            >
                {{ email }}
            </span>
        </div>
        <div
            v-if="list && !onlyExternal"
            class="flex items-center"
        >
            <i
                class="mr-1 text-primary-300 fa-regular"
                :class="listIcon"
            >
            </i>
            <span
                class="font-medium text-cm-600"
            >
                {{ listName }}
            </span>
        </div>
    </div>
</template>

<script>

import { getIntegrationIcon } from '@/core/display/integrationIcons.js';
import { getEmailIcon } from '@/core/display/emailFolderIcons.js';

export default {
    name: 'FeatureSource',
    components: {

    },
    mixins: [
    ],
    props: {
        featureItem: {
            type: Object,
            required: true,
        },
        listKey: {
            type: String,
            required: true,
        },
        onlyExternal: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        account() {
            return this.featureItem.account;
        },
        list() {
            return this.featureItem[this.listKey];
        },
        listName() {
            return this.list?.name;
        },
        provider() {
            return this.account?.provider;
        },
        email() {
            return this.account?.accountName;
        },
        integrationIcon() {
            return getIntegrationIcon(this.provider);
        },
        isEmail() {
            return this.listKey === 'mailbox';
        },
        listIcon() {
            if (this.isEmail) {
                return getEmailIcon(this.listName);
            }
            return 'fa-list-radio';
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-feature-source {

} */

</style>
