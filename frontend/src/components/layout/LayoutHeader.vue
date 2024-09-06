<template>
    <div
        class="c-layout-header"
    >
        <div
            v-if="space"
            class="flex items-center mb-2"
        >
            <ProfileNameImage
                class="mr-2"
                :profile="displayedBase"
                hideFullName
                size="sm"
            >
            </ProfileNameImage>
            <h6
                class="font-semibold text-cm-700 text-xs"
            >
                <span
                    class="uppercase text-cm-500 text-xs"
                >
                    {{ spaceName }}
                </span>
                <span
                    v-if="folderLang"
                    class="text-cm-700 text-xs ml-4"
                >
                    <i
                        class="fa-regular fa-folder mr-1"
                    >
                    </i>
                    {{ folderLang }}
                </span>
            </h6>
        </div>
        <div>
            <h1
                class="text-4xl flex flex-wrap"
            >
                <ProfileNameImage
                    v-if="!space"
                    class="mr-3"
                    :profile="displayedBase"
                    hideFullName
                    size="sm"
                >
                </ProfileNameImage>

                <i
                    v-if="icon"
                    class="mr-4"
                    :class="icon"
                >
                </i>

                {{ pageName }}

                <span
                    v-if="subsectionName"
                    class="flex"
                >
                    <span
                        class="block mx-1"
                    >
                        -
                    </span>
                    {{ subsectionName }}
                </span>
            </h1>
            <div
                v-if="mapping"
                class="mt-1"
            >
                <div
                    class="text-xs flex"
                >
                    <label class="c-layout-header__label">
                        {{ $t('labels.blueprint') }}:
                    </label>
                    <button
                        ref="blueprintAccess"
                        class="hover:underline block"
                        type="button"
                        @click="openPageEdit('MAPPING')"
                    >
                        {{ mappingName }}
                    </button>

                    <SupportTip
                        v-if="activeTips && isTipActive('BLUEPRINT_ACCESS')"
                        :activator="$refs.blueprintAccess"
                        :tips="activeTips"
                        :tip="getTip('BLUEPRINT_ACCESS')"
                    >
                    </SupportTip>
                </div>
                <div
                    v-if="isSubset"
                    class="flex items-center text-xs"
                >
                    <label class="c-layout-header__label">
                        {{ $t('common.filteredBy') }}:
                    </label>

                    <div
                        class="flex items-center"
                    >
                        <span
                            class="mr-2"
                        >
                            {{ filterName }} - {{ $t('labels.' + operator) }}:
                        </span>
                        <SubsetValue
                            :fieldValue="fieldValue"
                            :markerFiltersLength="markerFiltersLength"
                            :fieldFiltersLength="fieldFiltersLength"
                            :markerWithValue="markerWithValue"
                            :markerGroupWithValue="markerGroupWithValue"
                            bgColorClass="bg-cm-200"
                        >
                        </SubsetValue>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';

import interactsWithSubsetInfo from '@/vue-mixins/pages/interactsWithSubsetInfo.js';
import interactsWithActiveTips from '@/vue-mixins/support/interactsWithActiveTips.js';

import { isActiveBasePersonal, activeBase } from '@/core/repositories/baseRepository.js';

export default {
    name: 'LayoutHeader',
    components: {
        ProfileNameImage,
    },
    mixins: [
        interactsWithSubsetInfo,
        interactsWithActiveTips,
    ],
    props: {
        page: {
            type: [Object, null],
            default: null,
        },
        mapping: {
            type: [Object, null],
            default: null,
        },
        nameType: {
            type: String,
            default: 'name',
        },
        name: {
            type: [String, null],
            default: null,
        },
        iconProp: {
            type: String,
            default: '',
        },
        subsectionName: {
            type: String,
            default: '',
        },
    },
    emits: [
        'openPageEdit',
    ],
    data() {
        return {

        };
    },
    computed: {
        mappingObj() {
            return this.mapping;
        },
        folderLang() {
            return this.page?.folder?.slice(0, -1);
        },
        space() {
            return this.page?.space;
        },
        spaceName() {
            return this.space?.name;
        },
        user() {
            return this.$root.authenticatedUser;
        },
        activeBase() {
            return activeBase();
        },
        displayedBase() {
            return this.isPersonalActive ? this.user : this.activeBase;
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
        baseImage() {
            return this.activeBase.avatar;
        },
        baseName() {
            return this.activeBase.name;
        },
        pageName() {
            return this.page ? this.page[this.nameType] : this.name;
        },
        icon() {
            if (this.iconProp) {
                return this.iconProp;
            }
            if (this.page?.symbol) {
                return `fa-regular ${this.page.symbol}`;
            }
            return '';
        },
    },
    methods: {
        openPageEdit(selectedView = 'MAPPING') {
            this.$emit('openPageEdit', selectedView);
        },
    },
    created() {

    },
};
</script>

<style scoped>
.c-layout-header {
    &__label {
        @apply
            font-semibold
            mr-1
            text-gray-400
            uppercase
        ;
    }
}
</style>
