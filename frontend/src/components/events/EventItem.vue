<template>
    <ButtonEl
        class="o-event-line feature__item feature__item--style relative"
        :class="mainFeatureItemClasses"
        @click="selectEvent"
    >
        <div class="">
            <div
                class="flex mb-1"
            >
                <div class="font-semibold text-primary-900 text-base flex-1 mr-2">
                    {{ event.name }}
                </div>

                <div
                    class="flex items-center -mr-3"
                >
                    <div
                        v-if="event.description"
                        class="mr-2 text-cm-300"
                        :title="$t('labels.description')"
                    >
                        <i
                            class="fa-regular fa-memo-pad"
                        >
                        </i>
                    </div>

                    <div
                        class="bg-cm-00 -mr-px h-7 w-7 centered rounded-l-full shadow-md"
                    >
                        <ExtrasButton
                            :options="['DUPLICATE', 'DELETE']"
                            :item="featureItem"
                            contextItemType="FEATURE_ITEM"
                            alignRight
                            nudgeDownProp="0.375rem"
                            nudgeRightProp="0.375rem"
                            :duplicateItemMethod="duplicateItem"
                            @click.stop
                            @selectOption="selectOption"
                        >
                        </ExtrasButton>
                    </div>
                </div>
            </div>

            <div class="flex items-center">
                <i
                    class="fal fa-calendar-alt mr-2"
                    :style="{ color: textColor }"
                >
                </i>

                <div class="text-sm">
                    <div v-if="isSingleDay">
                        <span
                            class="font-bold mr-2"
                            :style="{ color: textColor }"
                        >
                            {{ startDayMonthFormatted }}
                        </span>
                        <span
                            class="text-cm-500"
                        >
                            <template
                                v-if="!isAllDay"
                            >
                                {{ startTimeFormatted }} - {{ endTimeFormatted }}
                            </template>
                            <template
                                v-else
                            >
                                All day
                            </template>
                        </span>
                    </div>
                    <div v-else>
                        <span
                            class="font-bold mr-2"
                            :style="{ color: textColor }"
                        >
                            {{ startDayMonthFormatted }}
                        </span>
                        <span
                            v-if="!isAllDay"
                            class="text-cm-500"
                        >
                            {{ startTimeFormatted }}
                        </span> -
                        <span
                            class="text-cm-500 mr-1"
                        >
                            {{ endDayMonthFormatted }}
                        </span>
                        <span
                            v-if="!isAllDay"
                            class="text-cm-500"
                        >
                            {{ endTimeFormatted }}
                        </span>
                    </div>
                </div>
                <RecurrenceDisplay
                    v-if="recurrence"
                    class="bg-cm-00 px-2 py-0.5 rounded ml-2"
                    :recurrence="recurrence"
                >
                </RecurrenceDisplay>
            </div>
        </div>

        <div
            v-if="markersLength || associationsLength"
            class="flex flex-wrap mt-2"
            :class="!markersLength ? 'justify-end' : 'justify-between'"
        >
            <EditableMarkerSet
                v-if="markersLength"
                :item="event"
                :tags="tags"
                :pipelines="pipelines"
                :statuses="statuses"
            >
            </EditableMarkerSet>

            <div
                v-if="associationsLength"
                class="flex flex-wrap gap-0.5"
            >
                <div
                    v-for="association in associations"
                    :key="association.id"
                    class="w-6 h-6"
                >
                    <ConnectedRecord
                        class="h-full w-full text-xssm"
                        :item="association"
                        :isMinimized="true"
                        imageSize="full"
                        @click.stop
                    >
                    </ConnectedRecord>
                </div>
            </div>
        </div>

        <div
            v-if="!calendar || showAssignees"
            class="flex flex-wrap justify-end mt-2 items-center"
        >
            <FeatureSource
                v-if="!calendar"
                :featureItem="event"
                listKey="calendar"
            >
            </FeatureSource>

            <AssigneesPicker
                v-if="showAssignees"
                v-model:assigneeGroups="assigneeGroups"
                class="ml-2"
                bgColor="white"
            >
            </AssigneesPicker>
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
import RecurrenceDisplay from '@/components/time/RecurrenceDisplay.vue';
import FeatureSource from '@/components/features/FeatureSource.vue';

import handlesFeatureItemSelection from '@/vue-mixins/features/handlesFeatureItemSelection.js';
import interactsWithEventItem from '@/vue-mixins/events/interactsWithEventItem.js';
import interactsWithFeatureItem from '@/vue-mixins/features/interactsWithFeatureItem.js';
import { duplicateEvent } from '@/core/repositories/eventRepository.js';

import useEventItem from '@/composables/useEventItem.js';

export default {
    name: 'EventItem',
    components: {
        RecurrenceDisplay,
        FeatureSource,
    },
    mixins: [
        interactsWithFeatureItem,
        interactsWithEventItem,
        handlesFeatureItemSelection,
    ],
    props: {
        event: {
            type: Object,
            required: true,
        },
        calendar: {
            type: [Object, null],
            default: null,
        },
        actionProcessing: Boolean,
        deleteProcessing: Boolean,
    },
    emits: [
        'selectEvent',
    ],
    setup(props, context) {
        const {
            textColor,
            isSingleDay,
            isAllDay,
            startTimeFormatted,
            endTimeFormatted,
            startDayMonthFormatted,
            endDayMonthFormatted,
            actionProcessingClass,
            deleteProcessingClass,
            selectEvent,
        } = useEventItem(props, context);

        return {
            textColor,

            isSingleDay,
            isAllDay,

            startTimeFormatted,
            endTimeFormatted,

            startDayMonthFormatted,
            endDayMonthFormatted,

            actionProcessingClass,
            deleteProcessingClass,

            selectEvent,
        };
    },
    data() {
        return {
        };
    },
    computed: {
        featureItem() {
            return this.event;
        },
        recurrence() {
            return this.event.recurrence;
        },
    },
    methods: {
        duplicateItem(records) {
            return duplicateEvent(this.event, records);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-event-line {
    @apply
        p-3
        rounded-xl
        text-xssm
    ;
}

</style>
