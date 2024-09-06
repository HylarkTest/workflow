<template>
    <div class="o-data-info text-sm">
        <div class="flex justify-between text-cm-400">
            <div class="text-xs mb-1">
                <span>
                    {{ fullHeader }}
                </span>
                <span
                    v-if="subtype"
                >
                    {{ itemName }}
                </span>
                <span
                    v-if="isList"
                >
                    (List)
                </span>
            </div>
        </div>

        <DataNameDisplay
            class="text-xssm"
            :dataObj="item"
        >
        </DataNameDisplay>
    </div>
</template>

<script>

import DataNameDisplay from '@/components/customize/DataNameDisplay.vue';

import {
    getTextStrings,
} from '@/core/display/theStandardizer.js';

export default {
    name: 'DataInfo',
    components: {
        DataNameDisplay,
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        subtype() {
            return this.item.info?.subType;
        },
        parent() {
            return this.item.info?.parent;
        },
        parentType() {
            return getTextStrings(this.parent);
        },
        isParentList() {
            return this.parent?.info?.options?.list;
        },
        fullHeader() {
            let string = this.dataHeader;
            if (this.parent) {
                string = string.concat(` - ${this.parentType}`);

                if (this.isParentList) {
                    string = string.concat(' (List)');
                }
            }
            if (this.subtype) {
                string = string.concat(' - ');
            }
            return string;
        },
        dataHeader() {
            return this.$t(`labels.${_.camelCase(this.item.dataType)}`);
        },
        itemName() {
            return getTextStrings(this.item);
        },
        isList() {
            return this.item.info?.options?.list;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-data-info {

} */

</style>
