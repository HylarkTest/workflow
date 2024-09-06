<template>
    <DropdownPaged
        :modelValue="flattenedAssignees"
        dropdownComponent="DropdownFree"
        searchableRule="name"
        :pageKeys="isSingleGroup ? [] : ['options']"
        :options="assigneeOptions"
        class="c-assignees-picker"
        :optionsPopupProps="mergedPopupOptions"
        :isSearchable="true"
        :blockClose="true"
        :collapseOnSearch="!isSingleGroup"
        :headerCondition="(page, search) => page === 1 && !search && !isSingleGroup"
        v-bind="$attrs"
        @select="emitAssignees"
        @visibleOptions="formatVisibleOptions"
    >
        <!-- popupState, closePopup, original -->
        <template
            #selected="{
                display, selectedEvents, popupState,
            }"
        >
            <template
                v-if="isButtonless"
            >
                <span>
                </span>
            </template>

            <ButtonEl
                v-if="!isButtonless"
                @click.stop="selectedEvents.click"
            >
                <div
                    v-if="noAssignees"
                    class="c-assignees-picker__empty centered"
                    :class="addClasses(popupState)"
                >
                    <i
                        class="fa-light fa-user"
                    >
                    </i>

                    <i
                        class="c-assignees-picker__add fa-solid fa-circle-plus"
                    >
                    </i>
                </div>

                <div
                    v-else
                    class="flex flex-wrap z-0 relative"
                >
                    <div
                        v-for="(assignee, index) in display"
                        :key="assignee.id"
                        class="c-assignees-picker__user -ml-3 first:ml-0"
                        :style="{ zIndex: 100 - (1 + index) }"
                    >
                        <ProfileNameImage
                            :profile="assignee"
                            :hideFullName="true"
                            :size="displaySize"
                            colorName="turquoise"
                        >
                        </ProfileNameImage>

                        <ClearButton
                            class="c-assignees-picker__clear"
                            :size="clearSize"
                            @click.stop="removeAssignee(assignee)"
                        >
                        </ClearButton>
                    </div>
                </div>
            </ButtonEl>

        </template>

        <template
            #wholeOption="{ original, isSelected }"
        >
            <div
                class="flex items-center py-1 px-2 relative"
            >
                <ProfileNameImage
                    :profile="original"
                    :hideFullName="true"
                    size="sm"
                    colorName="turquoise"
                >
                </ProfileNameImage>

                <span
                    class="ml-2"
                    :class="{ 'font-semibold text-turquoise-600': isSelected }"
                >
                    {{ original.name }}
                </span>

                <i
                    v-if="isSelected"
                    class="c-assignees-picker__check fa-solid fa-circle-check"
                >
                </i>
            </div>
        </template>
        <!-- Ignore the group header-->
        <template
            #group
        >
            <span></span>
        </template>
    </DropdownPaged>
</template>

<script>

import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';
import DropdownPaged from '@/components/dropdowns/DropdownPaged.vue';
import { arrRemoveId } from '@/core/utils.js';

export default {
    name: 'AssigneesPicker',
    components: {
        DropdownPaged,
        ProfileNameImage,
        ClearButton,
    },
    mixins: [
    ],
    props: {
        assigneeGroups: {
            type: Array,
            default: () => ([]),
        },
        displaySize: {
            type: String,
            default: 'sm',
            validator(val) {
                return ['sm', 'xs'].includes(val);
            },
        },
        isButtonless: Boolean,
        optionsPopupProps: {
            type: Object,
            default: () => ({}),
        },
    },
    emits: [
        'update:assigneeGroups',
        'assigneeGroupOptions',
    ],
    data() {
        return {
            filteredAssigneeOptions: [],
        };
    },
    computed: {
        mergedPopupOptions() {
            return {
                widthProp: '12.5rem',
                ...this.optionsPopupProps,
            };
        },
        flattenedAssignees() {
            return _.flatMap(this.assigneeGroups, 'assignees');
        },
        noAssignees() {
            return !this.assigneeGroups.length
                || this.allLabelsEmpty;
        },
        allLabelsEmpty() {
            return this.assigneeGroups.every((assigneeGroup) => {
                return !assigneeGroup.assignees?.length;
            });
        },
        user() {
            return this.$root.authenticatedUser;
        },
        activeBase() {
            return this.user.activeBase();
        },
        possibleAssignees() {
            return this.activeBase.assigneeGroups.map((group) => ({
                group,
                options: group.members,
            }));
        },
        isSingleGroup() {
            return this.possibleAssignees.length === 1;
        },
        assigneeOptions() {
            return this.isSingleGroup ? this.possibleAssignees?.[0]?.options : this.possibleAssignees;
        },
        clearSize() {
            return this.displaySize === 'xs' ? 'sm' : 'base';
        },
        addSizeClass() {
            return `c-assignees-picker__empty--${this.displaySize}`;
        },
        formattedFilteredOptions() {
            const groups = [];
            this.possibleAssignees.forEach((group) => {
                const filteredAssignees = group.options.filter((assignee) => {
                    return this.filteredAssigneeOptions.find((option) => {
                        return option.id === assignee.id;
                    });
                });

                if (filteredAssignees?.length) {
                    groups.push({
                        group: group.group,
                        assignees: filteredAssignees,
                    });
                }
            });

            return groups;
        },
    },
    methods: {
        removeAssignee(assignee) {
            const group = this.activeBase.assigneeGroups[0];
            const payload = this.assigneeGroups.map((assigneeGroup) => {
                const groupId = assigneeGroup.group?.id || assigneeGroup.groupId;
                const assignees = groupId === group.id
                    ? arrRemoveId(assigneeGroup.assignees, assignee.id)
                    : [...assigneeGroup.assignees];
                return {
                    groupId,
                    assignees,
                };
            });
            const filteredPayload = payload.filter((assigneeGroup) => assigneeGroup.assignees.length);
            this.$emit('update:assigneeGroups', filteredPayload);
        },

        emitAssignees({ page, value }) {
            const payload = (this.assigneeGroups || []).map((assigneeGroup) => ({
                groupId: assigneeGroup.groupId || assigneeGroup.group.id,
                assignees: [...assigneeGroup.assignees],
            }));
            const groupId = this.isSingleGroup ? this.possibleAssignees[0].group.id : page.group.id;
            const group = page || this.possibleAssignees[0];
            let groupIndex = _.findIndex(payload, { groupId });
            if (groupIndex === -1) {
                groupIndex = payload.length;
                payload.push({ groupId: group.group.id, assignees: [] });
            }

            let assignees = payload[groupIndex].assignees;
            if (_.find(assignees, ['id', value.id])) {
                assignees = arrRemoveId(assignees, value.id);
            } else {
                assignees = [...assignees, value];
            }
            payload[groupIndex].assignees = assignees;

            this.$emit('update:assigneeGroups', payload);
        },
        addClasses(popupState) {
            return [this.activePickerClass(popupState), this.addSizeClass];
        },
        activePickerClass(popupState) {
            return { 'c-assignees-picker__empty--active': popupState };
        },
        formatVisibleOptions(visibleOptions) {
            this.filteredAssigneeOptions = visibleOptions || [];
            this.$emit('assigneeGroupOptions', this.formattedFilteredOptions);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-assignees-picker {
    &__empty {
        transition: 0.2s ease-in-out;

        @apply
            bg-cm-00
            border
            border-cm-300
            border-solid
            h-8
            relative
            rounded-md
            text-cm-400
            w-8
        ;

        &--sm {
            @apply
                h-8
                w-8
            ;
        }

        &--xs {
            @apply
                h-6
                w-6
            ;

            .c-assignees-picker__add {
                @apply
                    text-xs
                    -top-2
                ;
            }
        }

        &--active {
            @apply
                border-turquoise-500
                text-turquoise-500
            ;

            .c-assignees-picker__add {
                @apply
                    text-turquoise-500
                ;
            }
        }

        &:hover {
            @apply
                border-turquoise-500
                text-turquoise-500
            ;

            .c-assignees-picker__add {
                @apply
                    text-turquoise-500
                ;
            }
        }
    }

    &__add {
        transition: 0.2s ease-in-out;

        @apply
            absolute
            -right-1
            text-cm-300
            text-xssm
            -top-1
        ;
    }

    &__user {
        transition: 0.2s ease-in-out;

        @apply
            bg-cm-00
            border-2
            border-cm-200
            border-solid
            relative
            rounded-lg
        ;

        &:hover {
            @apply
                border-turquoise-400
            ;
        }
    }

    &__check {
        @apply
            absolute
            left-0.5
            text-turquoise-500
            text-xs
            top-0.5
            z-over
        ;
    }

    &__clear {
        right: -4px;
        top: -4px;

        @apply
            absolute
            z-over
        ;
    }
}

</style>
