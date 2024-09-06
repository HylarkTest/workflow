<template>
    <div class="c-data-name-display flex items-start flex-wrap u-ellipsis">
        <template
            v-if="!isParentHidden"
        >
            <span
                class="c-data-name-display__name text-cm-500 u-ellipsis"
                :title="parentName"
            >
                {{ parentName }}
            </span>
            <p
                v-if="parentDisplayOption"
                class="c-data-name-display__badge u-ellipsis"
                :title="parentBadge"
            >
                {{ parentBadge }}
            </p>
            <span
                v-if="parent"
                class="mx-1"
            >
                <i
                    class="fal fa-arrow-right"
                >
                </i>
            </span>
        </template>
        <span
            class="c-data-name-display__name font-medium u-ellipsis"
            :title="dataObjMain"
        >
            {{ dataObjMain }}
        </span>
        <p
            v-if="dataObjDisplayOption && !hasDisplayOptionOnly"
            class="c-data-name-display__badge u-ellipsis"
            :title="dataObjBadge"
        >
            {{ dataObjBadge }}
        </p>

        <slot>
        </slot>
    </div>
</template>

<script>

const skipDisplays = ['PRIORITIES', 'FAVORITES', 'ASSIGNEES'];

export default {
    name: 'DataNameDisplay',
    components: {

    },
    mixins: [
    ],
    props: {
        dataObj: {
            type: Object,
            required: true,
        },
        isParentHidden: Boolean,
        isDisplayOptionFocused: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        parent() {
            return this.dataObj.info?.parent;
        },
        parentName() {
            return this.parent?.name;
        },
        parentDisplayOption() {
            const parentDisplay = this.parent?.displayOption;

            if (parentDisplay) {
                const skipDisplay = skipDisplays.includes(parentDisplay);

                return skipDisplay ? null : parentDisplay;
            }
            return null;
        },
        parentBadge() {
            const camel = _.camelCase(this.parentDisplayOption);
            if (!this.parentDisplayOption) {
                return null;
            }
            return this.$t(`views.displayOptions.${camel}`);
        },
        dataObjName() {
            return this.dataObj.name || this.$t('common.noName');
        },
        dataObjMain() {
            if (this.hasDisplayOptionOnly) {
                return this.dataObjBadge;
            }
            return this.dataObjName;
        },
        dataObjDisplayOption() {
            const dataObjDisplay = this.dataObj.displayOption;

            if (dataObjDisplay) {
                const skipDisplay = skipDisplays.includes(dataObjDisplay);

                return skipDisplay ? null : dataObjDisplay;
            }
            return null;
        },
        dataObjBadge() {
            const camel = _.camelCase(this.dataObjDisplayOption);
            if (!this.dataObjDisplayOption) {
                return null;
            }
            return this.$t(`views.displayOptions.${camel}`);
        },
        dataType() {
            return this.dataObj.dataType;
        },
        hasDisplayOptionOnly() {
            const displayOptionDataTypes = ['FEATURES', 'RELATIONSHIPS'];

            return this.isDisplayOptionFocused
                && this.dataObjDisplayOption
                && displayOptionDataTypes.includes(this.dataType);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-data-name-display {
    &__badge {
        @apply
            bg-cm-100
            font-semibold
            ml-1
            px-1
            py-px
            rounded-md
            text-[80%]
            text-cm-600
        ;
    }
}

</style>
