<template>
    <div class="o-mapping-edit-marker-list">
        <h3 class="header-2 mb-3 text-primary-800 flex items-center">
            <i
                class="far mr-2"
                :class="markerIcon"
            >
            </i>
            {{ markerTypeName }}
        </h3>

        <RouterLink
            :to="{ name: 'customizePage', params: { tab: customizeMarkerTab } }"
            class="button--sm button-primary--light mb-4 inline-block"
        >
            <i
                class="fa-regular fa-square-arrow-up-right mr-1"
            >
            </i>
            {{ customizeLinkText }}
        </RouterLink>

        <BooleanButtonList
            v-if="markerGroupsLength"
            :modelValue="activatedMarkerGroups"
            :fullList="markerGroups"
            :confirmationList="markerGroups"
            :emitSingleItem="emitSingleMarker"
            listType="check"
            predicate="id"
            :disableRemoveConfirmation="disableRemoveConfirmation"
            @update:modelValue="$emit('updateMarkerGroups', $event)"
        >
            <template #listItem="{ listItem }">
                {{ listItem.name }}
            </template>

            <template #confirmationContent="{ listItem }">
                <p
                    v-t="blueprintText(listItem, 'deactivation')"
                    class="mb-4"
                >
                </p>
                <p
                    v-t="blueprintText(listItem, 'reactivation')"
                >
                </p>
            </template>
        </BooleanButtonList>

        <p
            v-else
            v-t="noneOfTypePath"
            class="text-sm text-cm-600"
        >
        </p>
    </div>
</template>

<script>

import pluralize from 'pluralize';
import BooleanButtonList from '@/components/inputs/BooleanButtonList.vue';

import { getIcon } from '@/core/display/typenamesList.js';

export default {
    name: 'MappingEditMarkerList',
    components: {
        BooleanButtonList,
    },
    mixins: [
    ],
    props: {
        type: {
            type: String,
            required: true,
        },
        markerGroups: {
            type: Array,
            default: () => [],
        },
        activatedMarkerGroups: {
            type: Array,
            default: () => [],
        },
        mappingName: {
            type: String,
            required: true,
        },
        emitSingleMarker: Boolean,
        disableRemoveConfirmation: Boolean,
    },
    emits: [
        'updateMarkerGroups',
    ],
    data() {
        return {

        };
    },
    computed: {
        markerGroupsLength() {
            return this.markerGroups.length;
        },
        camelCaseType() {
            return _.camelCase(this.type);
        },
        markerTypeName() {
            return this.$t(`customizations.${this.camelCaseType}.name`);
        },
        markerIcon() {
            return getIcon(this.type);
        },
        customizeLinkText() {
            return this.$t(`customizations.${this.camelCaseType}.customize`);
        },
        customizeMarkerTab() {
            return pluralize(this.camelCaseType);
        },
        noneOfTypePath() {
            return `customizations.${this.camelCaseType}.noneOnBase`;
        },
    },
    methods: {
        blueprintText(marker, key) {
            return {
                path: `customizations.blueprint.markers.${key}`,
                args: { mappingName: this.mappingName, markerName: marker.name },
            };
        },
    },
    created() {
    },
};
</script>

<style scoped>
/* .o-mapping-edit-marker-list {

} */
</style>
