<template>
    <div class="c-entity-extras flex items-center ml-2">

        <ViewButton
            class="mr-2"
            :item="item"
            v-bind="$attrs"
        >
        </ViewButton>

        <ExtrasButton
            :options="fullRecordOptions"
            :pageCreatePathsObj="pageCreatePathsObj"
            :propPageName="propPageName"
            :mapping="mapping"
            :item="item"
            contextItemType="RECORD"
            :behaviors="pageCreateBehaviors"
            :duplicateItemMethod="duplicateItemMethod"
            :showConfirmDelete="true"
            v-bind="$attrs"
        >
        </ExtrasButton>
    </div>
</template>

<script>

import ExtrasButton from '@/components/buttons/ExtrasButton.vue';

import {
    fullRecordOptions,
    pageCreateBehaviors,
    getModalHeaders,
    getDefaultPageName,
} from '@/composables/getsItemOptionsHelpers.js';

export default {
    setup() {
        return {
            fullRecordOptions,
            pageCreateBehaviors,
        };
    },
    name: 'EntityExtras',
    components: {
        ExtrasButton,
    },
    mixins: [
    ],
    props: {
        // Should only be null if in a preview, refine conditions
        // TODO prop validation
        item: {
            type: [Object, null],
            default: null,
        },
        mapping: {
            type: [Object, null],
            default: null,
        },
        duplicateItemMethod: {
            type: Function,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        name() {
            return this.item?.name;
        },
        pageCreatePathsObj() {
            return getModalHeaders(this.name);
        },
        propPageName() {
            return getDefaultPageName(this.name);
        },
    },
    methods: {

    },
    created() {
    },
};
</script>

<style scoped>

/*.c-entity-extras {

} */

</style>
