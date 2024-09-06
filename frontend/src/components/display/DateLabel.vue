<template>
    <div
        class="c-date-label flex items-center text-xs"
        :title="textLabel"
    >
        <i
            class="fal mr-1"
            :class="[icon, iconColorClass]"
        >
        </i>

        <label
            v-if="includeLabel"
            class="mr-1"
            :class="labelColorClass"
        >
            {{ textLabel }}
        </label>

        <span
            class="font-medium"
            :class="textColorClass"
        >
            {{ dateFormatted }}
        </span>

        <ImageName
            v-if="performer"
            class="ml-2"
            size="sm"
            colorName="turquoise"
            :image="performer.image"
            :name="performer.name"
            :hideFullName="true"
            :titleProp="performerTitle"
        >
        </ImageName>
    </div>
</template>

<script>

import ImageName from '@/components/images/ImageName.vue';

const icons = {
    CREATED_AT: 'fa-calendar-circle-plus',
    UPDATED_AT: 'fa-calendar-lines-pen',
};

export default {
    name: 'DateLabel',
    components: {
        ImageName,
    },
    mixins: [
    ],
    props: {
        date: {
            type: String,
            required: true,
        },
        includeLabel: Boolean,
        fullTime: Boolean,
        mode: {
            type: String,
            default: 'CREATED_AT',
            validator(val) {
                return ['CREATED_AT', 'UPDATED_AT', 'OTHER'].includes(val);
            },
        },
        iconColorClass: {
            type: String,
            default: 'text-cm-500',
        },
        labelColorClass: {
            type: String,
            default: 'text-cm-700',
        },
        textColorClass: {
            type: String,
            default: 'text-cm-800',
        },
        labelProp: {
            type: String,
            default: '',
        },
        iconProp: {
            type: String,
            default: '',
        },
        performer: {
            type: [Object, null],
            default: null,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        dateFormat() {
            let dateFormat = 'll';
            let timeFormat = '';
            if (this.fullTime) {
                timeFormat = ', LT';
            }
            if (this.dateIsThisYear) {
                dateFormat = 'MMM D';
            }
            return `${dateFormat}${timeFormat}`;
        },
        dateObj() {
            return this.$dayjs(this.date);
        },
        dateFormatted() {
            return this.dateObj.format(this.dateFormat);
        },
        labelVal() {
            return _.camelCase(this.mode);
        },
        textLabel() {
            return this.labelProp || this.$t(`labels.${this.labelVal}`);
        },
        icon() {
            return this.iconProp || icons[this.mode];
        },
        dateIsThisYear() {
            return this.dateObj.get('year') === this.$dayjs().get('year');
        },
        hasPerformer() {
            return !!this.performer;
        },
        performerTitle() {
            if (!this.hasPerformer) {
                return '';
            }
            let label;
            if (this.mode === 'CREATED_AT') {
                label = this.$t('labels.createdBy');
            } else if (this.mode === 'UPDATED_AT') {
                label = this.$t('labels.updatedBy');
            }
            const labelVal = label ? `${label}: ` : '';
            return `${labelVal}${this.performer.name}`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-created-at {

} */

</style>
