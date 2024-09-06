<template>
    <DropdownPaged
        :dropdownComponent="dropdownComponent"
        class="c-marker-picker"
        :popupProps="popupProps"
        placeholder="Select a marker"
        v-bind="$attrs"
        :options="dropdownOptions"
        :pageKeys="['items']"
        skipPageIfSingle
        displayRules="name"
        isSearchable
        :showClear="showClear"
        :blockClose="isModalOpen"
        :inputSearchPlaceholder="inputSearchPlaceholder"
        :headerCondition="(page, search) => page === 1 && !search && !isSingleGroup"
    >
        <template
            #additional="{ isOnLastPage, currentPage }"
        >
            <SettingsButton
                v-if="isOnLastPage"
                @showSettings="openModal"
            >
            </SettingsButton>
            <GroupEditModal
                v-if="isModalOpen"
                :group="isSingleGroup ? groupInfo : currentPage"
                :groupType="isSingleGroup ? groupType : currentPage.type"
                :itemDisplayComponent="getMarkerComponent(isSingleGroup ? groupType : currentPage.type)"
                :repository="repository"
                @closeModal="closeModal"
            >
            </GroupEditModal>
        </template>

        <template
            #selected="{
                display, popupState, selectedEvents, original, closePopup,
            }"
        >
            <slot
                name="selected"
                :display="display"
                :popupState="popupState"
                :selectedEvents="selectedEvents"
                :original="original"
                :closePopup="closePopup"
            >
                <component
                    v-if="original"
                    :is="getMarkerComponent(original?.group.type)"
                    :item="original"
                    size="sm"
                >
                </component>
            </slot>
        </template>

        <template
            #popupStart
        >
            Marker groups
        </template>
        <template
            #group="scope"
        >
            <!--
            Here we use v-show because of how Vue checks if a slot exists.
            If we use v-if then Vue will think the slot doesn't exist and will
            render the default content in `DropdownBasic` but we don't want
            anything to show, so we use v-show instead.
            -->
            <div
                v-show="!group"
                class="pl-3 pr-2 first:pt-0 pt-3 pb-0.5 font-semibold text-xs flex"
            >
                <i
                    class="fal mr-2 mt-1"
                    :class="getIcon(scope.group.type)"
                >
                </i>
                {{ scope.group.name }}
            </div>
        </template>

        <template
            #[whichOptionSlot]="{
                original,
                page,
                search,
                isSelected,
            }"
        >
            <slot
                name="option"
                :original="original"
                :isSelected="isSelected"
            >
                <div
                    v-if="page === 1 && !search && !isSingleGroup"
                    class="flex"
                >
                    <i
                        class="fal mr-2 text-cm-400 mt-1"
                        :class="getIcon(original.type)"
                    >
                    </i>
                    {{ original.name }}
                </div>

                <template
                    v-if="page === 2 || search || isSingleGroup"
                >
                    <div class="relative">
                        <component
                            :is="getMarkerComponent(original?.group.type)"
                            :item="original"
                            size="sm"
                        >
                        </component>
                    </div>
                </template>
            </slot>
        </template>
        <template
            v-if="isSingleGroup"
            #popupEnd="{ selectedEvents }"
        >
            <slot
                name="popupEnd"
                :selectedEvents="selectedEvents"
            >
            </slot>
        </template>
    </DropdownPaged>
</template>

<script>

import DropdownPaged from '@/components/dropdowns/DropdownPaged.vue';
import GroupEditModal from '@/components/customize/GroupEditModal.vue';
import SettingsButton from '@/components/buttons/SettingsButton.vue';
import StageDisplay from '@/components/customize/StageDisplay.vue';
import StatusDisplay from '@/components/customize/StatusDisplay.vue';
import TagDisplay from '@/components/customize/TagDisplay.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import MARKER_GROUPS from '@/graphql/markers/queries/MarkerGroups.gql';
import MARKER_GROUP from '@/graphql/markers/queries/MarkerGroup.gql';
import { initializeMarkers, groupRepository } from '@/core/repositories/markerRepository.js';

import { getIcon } from '@/core/display/typenamesList.js';
import { getMarkerComponent } from '@/core/display/markerHelpers.js';

export default {
    name: 'MarkerPicker',
    components: {
        DropdownPaged,
        GroupEditModal,
        SettingsButton,
        StageDisplay,
        StatusDisplay,
        TagDisplay,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
        mappingId: {
            type: [String, Array],
            default: null,
            validate(val, props) {
                if (val && props.mapping) {
                    return 'You cannot use both mappingId and mapping props at the same time. Please use only one.';
                }
                return true;
            },
        },
        mapping: {
            type: [Object, null],
            default: null,
        },
        feature: {
            type: [String, Array, null],
            default: null,
        },
        popupProps: {
            type: Object,
            default: () => ({ maxHeightProp: '9.375rem' }),
        },
        type: {
            type: [String, Array],
            default: '',
        },
        inputSearchPlaceholder: {
            type: String,
            default: 'Find a marker...',
        },
        group: {
            type: [String, Object, null],
            default: null,
        },
        showClear: Boolean,
        whichOptionSlot: {
            type: String,
            default: 'option',
        },
    },
    apollo: {
        markerGroups: {
            query() {
                return this.group ? MARKER_GROUP : MARKER_GROUPS;
            },
            variables() {
                if (this.group) {
                    return { id: this.group.id || this.group };
                }
                const variables = {};
                const mappingId = this.mapping?.id || this.mappingId;
                if (mappingId) {
                    variables.usedByMappings = _.isArray(mappingId) ? mappingId : [mappingId];
                }
                if (this.type) {
                    variables.types = _.isArray(this.type) ? this.type : [this.type];
                }
                if (this.feature) {
                    variables.usedByFeatures = _.isArray(this.feature) ? this.feature : [this.feature];
                }
                return variables;
            },
            update: (data) => {
                return data.markerGroup
                    ? [data.markerGroup]
                    : initializeMarkers(data).markerGroups;
            },
        },
    },
    data() {
        return {
        };
    },
    computed: {
        isSingleGroup() {
            return this.markerGroupsWithContext?.length === 1;
        },
        markerGroupsWithContext() {
            if (this.mapping?.markerGroups) {
                return this.mapping.markerGroups.flatMap((blueprintGroup) => {
                    const markerGroup = _.find(this.markerGroups, { id: blueprintGroup.group.id });
                    if (!markerGroup) {
                        return [];
                    }
                    return [{
                        ...markerGroup,
                        name: blueprintGroup.name,
                        context: blueprintGroup.id,
                    }];
                });
            }
            return this.markerGroups;
        },
        dropdownOptions() {
            return this.markerGroupsWithContext;
        },
        groupType() {
            return this.markerGroupsWithContext?.[0]?.type;
        },
        groupInfo() {
            return this.isSingleGroup ? this.markerGroupsWithContext?.[0] : null;
        },
    },
    methods: {
        getIcon(val) {
            return getIcon(val);
        },
        getMarkerComponent(type) {
            return getMarkerComponent(type);
        },
    },
    created() {
        this.repository = groupRepository;
    },
};
</script>

<style scoped>

/* .c-marker-picker {

} */

</style>
