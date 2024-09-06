<template>
    <div class="c-feature-header">
        <div
            class="flex items-center"
            :class="hasReducedPadding ? 'pb-4' : 'px-6 py-4'"
        >
            <MaximizeSide
                v-if="isSideMinimized"
                class="mr-6"
                @click="minimizeSide"
            >
            </MaximizeSide>

            <FormWrapper
                class="flex justify-between flex-wrap flex-1"
                :form="form"
                dontScroll
            >
                <div
                    v-if="!hasActiveFilters && list"
                    class="flex"
                >
                    <i
                        v-if="provider"
                        class="mr-3 mt-1 text-2xl text-primary-300"
                        :class="integrationIcon"
                    >
                    </i>

                    <ColorSquare
                        v-if="!hideColorSquare"
                        v-model:currentColor="form.color"
                        class="mr-3 mt-3"
                        size="lg"
                        :isModifiable="true"
                        @update:currentColor="saveList"
                    >
                    </ColorSquare>

                    <div class="relative">
                        <component
                            :is="!list.canBeRenamed() ? 'div' : 'ButtonEl'"
                            component="h2"
                            class="header-list"
                            :class="{ 'opacity-0': editNameMode }"
                            @click="editListName"
                            @keyup.enter="editListName"
                            @keyup.space="editListName"
                        >
                            {{ list.name }}
                        </component>

                        <template
                            v-if="editNameMode"
                        >
                            <InputSubtle
                                ref="nameInput"
                                v-blur="saveListName"
                                class="header-list"
                                :alwaysHighlighted="true"
                                displayClasses="absolute -top-1 left-0 w-72"
                                formField="name"
                                @keyup.enter="saveListName"
                            >
                            </InputSubtle>
                        </template>
                    </div>
                </div>
                <div
                    v-else
                    class="header-list"
                >
                    {{ resultsHeader }}
                </div>
            </FormWrapper>
        </div>

        <div
            v-show="!isLoading"
            class="relative pb-4 border-b border-solid border-cm-200 mb-6"
        >
            <div
                class="flex flex-wrap justify-end mb-1"
                :class="hasReducedPadding ? '' : 'px-4'"
            >
                <div
                    v-if="hasGrouping"
                >
                    <GroupingSelection
                        :currentGroup="filtersObj.currentGroup"
                        :featureType="featureType"
                        :showListOption="hasActiveFilters"
                        bgColor="gray"
                        :spaceIds="spaceIds"
                        :hideValue="true"
                        :hideToggleButton="true"
                        @update:currentGroup="emitUpdate($event, 'currentGroup')"
                    >
                    </GroupingSelection>
                </div>

                <div
                    v-if="sortablesLength"
                    class="ml-2"
                >
                    <SortingDropdown
                        :sortOrder="filtersObj.sortOrder"
                        :sortables="sortables"
                        bgColor="gray"
                        :hideValue="true"
                        :hideToggleButton="true"
                        @update:sortOrder="emitUpdate($event, 'sortOrder')"
                    >
                    </SortingDropdown>
                </div>
            </div>

            <div
                class="absolute -bottom-4 flex justify-center w-full"
            >
                <slot
                    name="newButton"
                >
                </slot>
            </div>
        </div>
        <div
            ref="headerTeleport"
            class="px-6"
        >

        </div>
    </div>
</template>

<script>

import SortingDropdown from '@/components/sorting/SortingDropdown.vue';
import ColorSquare from '@/components/assets/ColorSquare.vue';
import GroupingSelection from '@/components/assets/GroupingSelection.vue';
import interactsWithMaximize from '@/vue-mixins/common/interactsWithMaximize.js';
import providesFilterProperties from '@/vue-mixins/providesFilterProperties.js';

import { getIntegrationIcon } from '@/core/display/integrationIcons.js';

const featureBehaviors = {
    TODOS: {
        hideColorSquare() {
            return this.isExternalList;
        },
    },
    EMAILS: {
        hideColorSquare: true,
    },
};

export default {
    name: 'FeatureHeader',
    components: {
        SortingDropdown,
        ColorSquare,
        GroupingSelection,
    },
    mixins: [
        interactsWithMaximize,
        providesFilterProperties,
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
        sortables: {
            type: [Array, null],
            default: null,
        },
        hideGrouping: Boolean,
        isLoading: Boolean,
        hasReducedPadding: Boolean,
        featureType: {
            type: String,
            required: true,
        },
        spaceId: {
            type: [String, null],
            default: null,
        },
    },
    emits: [
        'saveList',
        'update:filtersObj',
        'minimizeSide',
    ],
    data() {
        return {
            editNameMode: false,
            form: this.$apolloForm(() => {
                const data = {
                    id: this.list?.id,
                    name: this.list?.name,
                    color: this.list?.color,
                };
                if (this.list?.isExternalList()) {
                    data.sourceId = this.list.account.id;
                }
                return data;
            }),
        };
    },
    computed: {
        hasGrouping() {
            return !this.hideGrouping;
        },
        sortablesLength() {
            return this.sortables?.length;
        },
        filtersSourceKey() {
            return this.filtersObj;
        },
        resultsHeader() {
            const results = this.$t('common.results');
            if (this.mainFilter) {
                const main = this.$t(`labels.${this.mainFilter}`);
                return this.hasContentFilters ? `${main} - ${results}` : main;
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
        featureBehaviorObj() {
            return featureBehaviors[this.featureType] || {};
        },
        hideColorSquare() {
            return this.featureBehaviorCheck('hideColorSquare');
        },
        spaceIds() {
            return this.spaceId ? [this.spaceId] : null;
        },
    },
    methods: {
        featureBehaviorCheck(keyToCheck) {
            const val = this.featureBehaviorObj[keyToCheck];
            if (_.isFunction(val)) {
                return val.call(this);
            }
            return val;
        },
        async editListName() {
            if (this.list.canBeRenamed()) {
                this.editNameMode = true;
                await this.$nextTick();
                const input = this.$refs.nameInput;
                input.focus();
                input.select();
            }
        },
        saveListName() {
            if (this.editNameMode) {
                this.editNameMode = false;

                if (this.form.name) {
                    this.saveList();
                } else {
                    this.form.reset();
                }
            }
        },
        saveList() {
            this.$emit('saveList', { form: this.form, list: this.list });
        },
        emitUpdate(val, filterKey) {
            this.$proxyEvent(val, this.filtersObj, filterKey, 'update:filtersObj');
        },
    },
    watch: {
        list() {
            this.form.reset();
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-feature-header {

} */

</style>
