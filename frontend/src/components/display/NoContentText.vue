<template>
    <div class="c-no-content-text">
        <div
            v-if="!hideIcon"
            class="relative mb-6"
        >
            <slot
                name="graphic"
            >
                <div
                    class="circle-center h-16 w-16"
                    :class="iconBgClass"
                >
                    <i
                        class="fal text-2xl text-primary-600"
                        :class="icon"
                    >
                    </i>
                </div>
            </slot>
        </div>

        <div class="text-center max-w-sm">
            <h6
                class="font-semibold mb-2 text-base"
            >
                {{ header }}
            </h6>
            <p
                v-if="customMessagePath"
                class="text-sm text-gray-500"
            >
                {{ message }}
            </p>

            <slot>
            </slot>
        </div>
    </div>
</template>

<script>

// Where the content comes from the user
const USER_SET = [
    {
        val: 1,
        icon: 'fa-circle-play',
    },
    {
        val: 2,
        icon: 'fa-thought-bubble',
    },
    {
        val: 3,
        icon: 'fa-grid-2-plus',
    },
    {
        val: 4,
        icon: 'fa-notes',
    },
    {
        val: 5,
        icon: 'fa-shield-plus',
    },
    {
        val: 6,
        icon: 'fa-truck-container-empty',
    },
    {
        val: 7,
        icon: 'fa-globe-stand',
    },
    {
        val: 8,
        icon: 'fa-paintbrush',
    },
    {
        val: 9,
        icon: 'fa-face-smile-plus',
    },
    {
        val: 10,
        icon: 'fa-sparkles',
    },
];

// Where the content comes from the system
const SYSTEM_SET = [
    {
        val: 1,
        icon: 'fa-eyes',
    },
    {
        val: 2,
        icon: 'fa-empty-set',
    },
    {
        val: 3,
        icon: 'fa-hourglass-empty',
    },
    {
        val: 4,
        icon: 'fa-hand-sparkles',
    },
];

export default {
    name: 'NoContentText',
    components: {

    },
    mixins: [
    ],
    props: {
        hideIcon: Boolean,
        specificObjectVal: {
            type: [Number, null],
            default: null,
        },
        customHeaderPath: {
            type: [String, Array],
            default: '',
        },
        customMessagePath: {
            type: [String, Array],
            default: '',
        },
        customIcon: {
            type: String,
            default: '',
        },
        sourceList: {
            type: String,
            default: 'USER_SET',
            validator(val) {
                return ['USER_SET', 'SYSTEM_SET', 'NONE'].includes(val);
            },
        },
        actionButton: {
            type: [Object, null],
            default: null,
            validator(val) {
                if (val) {
                    return _.has(val.action) && _.has(val.textPath);
                }
                return true;
            },
        },
        iconBgClass: {
            type: String,
            default: 'bg-primary-100',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        header() {
            if (this.customHeaderPath) {
                if (_.isArray(this.customHeaderPath)) {
                    return this.$t(...this.customHeaderPath);
                }
                return this.$t(this.customHeaderPath);
            }
            if (this.specificObjectVal) {
                return this.$t(this.standardHeaderPath(this.specifiedObject?.val));
            }
            return this.$t(this.standardHeaderPath(this.randomObject?.val));
        },
        message() {
            if (!this.customMessagePath) {
                return '';
            }
            if (_.isArray(this.customMessagePath)) {
                return this.$t(...this.customMessagePath);
            }
            return this.$t(this.customMessagePath);
        },
        specifiedObject() {
            return _.find(this.usedList, { val: this.specificObjectVal });
        },
        usedList() {
            if (this.sourceList === 'NONE') {
                return null;
            }
            return this[this.sourceList];
        },
        usedListLength() {
            return this.usedList?.length || 0;
        },
        currentObject() {
            return this.specifiedObject || this.randomObject;
        },

        randomVal() {
            return _.random(0, (this.usedListLength - 1));
        },
        randomObject() {
            return this.usedList[this.randomVal];
        },

        icon() {
            return this.customIcon
                || this.currentObject?.icon
                || 'fa-star-shooting';
        },
    },
    methods: {
        standardHeaderPath(val) {
            return `noContent.headers.${_.camelCase(this.sourceList)}.${val}`;
        },
    },
    created() {
        this.USER_SET = USER_SET;
        this.SYSTEM_SET = SYSTEM_SET;
    },
};
</script>

<style scoped>

.c-no-content-text {
    @apply
        flex
        flex-col
        items-center
    ;
}

</style>
