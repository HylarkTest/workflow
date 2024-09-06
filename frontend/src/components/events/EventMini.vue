<template>
    <ButtonEl
        class="o-event-mini feature__item--mini"
        :class="itemClasses"
        @click="$emit('selectEvent', event)"
    >
        <div class="flex items-center">
            <div>
                <div
                    class="font-bold text-xs mr-1.5 w-16"
                    :style="{ color: textColor }"
                >
                    <template
                        v-if="cellDateObj"
                    >
                        <span
                            v-if="!isAllDay && (isSingleDay || isFirstDay)"
                        >
                            {{ startTimeFormatted }}
                        </span>

                        <span v-if="!isAllDay && isLastDay">
                            Ends {{ endTimeFormatted }}
                        </span>

                        <span v-else-if="isAllDay || isMiddleDay">
                            All day
                        </span>
                    </template>
                    <template
                        v-else
                    >
                        <span
                            v-if="!isAllDay"
                        >
                            {{ startTimeFormatted }}
                        </span>
                        <span v-else>
                            All day
                        </span>
                    </template>
                </div>
                <div
                    v-if="!assumeToday"
                    class="text-xxs text-cm-500"
                >
                    <template
                        v-if="isSingleDay"
                    >
                        {{ startDayMonthFormatted }}
                    </template>

                    <template
                        v-else
                    >
                        {{ startDayMonthFormatted }} - {{ endDayMonthFormatted }}
                    </template>
                </div>
            </div>
            <p
                class="text-cm-700 font-medium flex-1"
            >
                {{ displayedName }}
            </p>

            <div v-if="showExtras">
                <ExtrasButton
                    :options="['DELETE']"
                    alignRight
                    nudgeDownProp="0.375rem"
                    nudgeRightProp="0.375rem"
                    @click.stop
                    @selectOption="selectOption"
                >
                </ExtrasButton>
            </div>
        </div>

        <EventRepeatConfirm
            v-if="isModalOpen"
            :action="recurrenceModalAction"
            :external="event?.isExternalItem()"
            @closeModal="closeModal"
            @cancelAction="closeModal"
            @triggerAction="completeAction"
        >
        </EventRepeatConfirm>
    </ButtonEl>
</template>

<script>

import ExtrasButton from '@/components/buttons/ExtrasButton.vue';

import interactsWithEventItem from '@/vue-mixins/events/interactsWithEventItem.js';
import interactsWithFeatureMini from '@/vue-mixins/features/interactsWithFeatureMini.js';

import useEventItem from '@/composables/useEventItem.js';

export default {
    name: 'EventMini',
    components: {
        ExtrasButton,
    },
    mixins: [
        interactsWithEventItem,
        interactsWithFeatureMini,
    ],
    props: {
        event: {
            type: Object,
            required: true,
        },
        cellDateObj: {
            type: [Object, null],
            default: null,
        },
        bgClass: {
            type: String,
            default: 'bg-cm-100',
        },
        showExtras: Boolean,
        hoverable: Boolean,
        tinyVersion: Boolean,
        assumeToday: Boolean,
        actionProcessing: Boolean,
        deleteProcessing: Boolean,
    },
    setup(props, context) {
        const {
            textColor,
            startTimeFormatted,
            endTimeFormatted,
            startDayMonthFormatted,
            endDayMonthFormatted,
            isAllDay,
            isSingleDay,
            isFirstDay,
            isLastDay,
            isMiddleDay,
            actionProcessingClass,
            deleteProcessingClass,
            selectEvent,
        } = useEventItem(props, context);

        return {
            textColor,
            startTimeFormatted,
            endTimeFormatted,
            startDayMonthFormatted,
            endDayMonthFormatted,
            isAllDay,
            isSingleDay,
            isFirstDay,
            isLastDay,
            isMiddleDay,
            actionProcessingClass,
            deleteProcessingClass,
            selectEvent,
        };
    },
    emits: [
        'selectEvent',
    ],
    data() {
        return {

        };
    },
    computed: {
        featureItem() {
            return this.event;
        },
        itemClasses() {
            return [
                this.bgClass,
                this.hoverableClass,
                this.actionProcessingClass,
                this.deleteProcessingClass,
            ];
        },
        hoverableClass() {
            return { 'o-event-mini--unopenable': !this.hoverable };
        },
        name() {
            return this.event.name;
        },
        nameTruncated() {
            return _.truncate(this.name, { length: 40 });
        },
        displayedName() {
            return this.tinyVersion ? this.nameTruncated : this.name;
        },
    },
    methods: {
        selectOption(option) {
            if (option === 'DELETE') {
                this.deleteItem();
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-event-mini {
    @apply
        px-2
        rounded-lg
        text-xssm
    ;

    &--unopenable {
        @apply
            shadow-none
        ;
    }
}

</style>
