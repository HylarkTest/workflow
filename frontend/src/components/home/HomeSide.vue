<template>
    <div
        class="o-home-side"
    >
        <div
            v-if="showCustomizationWidget"
            class="o-home-side__tile mb-4 bg-sky-100 shadow-sky-600/20"
        >
            <h4 class="o-home-side__prompt">
                Personalize now
            </h4>

            <ul
                v-if="isCustomizeFull"
                class="text-xssm mb-2"
            >
                <li class="mb-1">
                    <i class="fa-regular fa-check-circle mr-1 text-sky-600">
                    </i>
                    Add more pages
                </li>
                <li class="mb-1">
                    <i class="fa-regular fa-check-circle mr-1 text-sky-600">
                    </i>
                    Create tags, pipelines, and statuses
                </li>
                <li>
                    <i class="fa-regular fa-check-circle mr-1 text-sky-600">
                    </i>
                    Update navigation, and so much more!
                </li>
            </ul>

            <RouterLink
                class="button--sm button-sky"
                :to="{ name: 'customizePage' }"
            >
                {{ $t(customizeTextPath) }}
            </RouterLink>

            <img
                class="o-home-side__image"
                :src="'/banners/GraphicClouds.png'"
            />
        </div>

        <div
            v-if="showIntegrationsWidget"
            class="o-home-side__tile mb-4 bg-emerald-100 shadow-emerald-600/20"
        >
            <h4 class="o-home-side__prompt">
                Looking to integrate?
            </h4>

            <p
                v-if="isIntegrationsFull"
                class="text-xssm text-cm-600 leading-tight mb-2"
            >
                Bringing it all together on Hylark by syncing emails, calendars, and to-dos.
            </p>

            <div class="centered">
                <RouterLink
                    :to="{ name: 'settings.integrations' }"
                    class="button--sm button-emerald"
                >
                    Integrate
                </RouterLink>
            </div>

            <!-- <img
                class="o-home-side__integrate"
                src="/banners/integration.png"
            /> -->
        </div>

        <div
            class="rounded-2xl bg-cm-00 p-4 relative"
        >
            <CalendarPicker
                v-model:displayedMonth="displayedMonth"
                v-model:displayedYear="displayedYear"
                :class="{ unclickable: isLoading }"
                :events="calendarEvents"
                :dateTime="selectedDate"
                colorName="secondary"
                @update:dateTime="selectDate"
            >
            </CalendarPicker>

            <TriangleBox
                v-if="selectedDate"
                class="bg-secondary-200 rounded-2xl mt-4 relative flex flex-col"
            >
                <HomeFeatureShort
                    class="md:w-full p-3"
                    featureType="EVENTS"
                    :eventDate="selectedDate"
                    :hideRoute="true"
                    @closeDate="selectedDate = null"
                >
                </HomeFeatureShort>
            </TriangleBox>

            <p
                v-else
                v-t="'home.side.events.selectDate'"
                class="italic text-sm text-cm-500 center mt-2"
            >
            </p>

            <div class="mt-8">
                <HomeFeatureShort
                    class="flex-1 mb-10 pl-2 lg:pr-2 md:pr-8"
                    featureType="EVENTS"
                    hideClose
                >
                </HomeFeatureShort>

                <HomeFeatureShort
                    class="flex-1 px-2"
                    featureType="TODOS"
                    hideClose
                >
                </HomeFeatureShort>
            </div>
        </div>
    </div>
</template>

<script>

import { gql } from '@apollo/client';
import CalendarPicker from '@/components/datePicker/CalendarPicker.vue';
import HomeFeatureShort from '@/components/home/HomeFeatureShort.vue';
import TriangleBox from '@/components/containers/TriangleBox.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import interactsWithEventBus from '@/vue-mixins/interactsWithEventBus.js';

import initializeConnections from '@/http/apollo/initializeConnections.js';

import { isActiveBasePersonal } from '@/core/repositories/baseRepository.js';

import { EVENT_CREATED, EVENT_UPDATED, EVENT_DELETED } from '@/core/repositories/eventRepository.js';

export default {
    name: 'HomeSide',
    components: {
        CalendarPicker,
        HomeFeatureShort,
        TriangleBox,
    },
    mixins: [
        interactsWithModal,
        interactsWithEventBus,
    ],
    props: {
        personalBasePreferences: {
            type: Object,
            required: true,
        },
    },
    apollo: {
        calendarEvents: {
            query: gql`
                query QuickEvents(
                    $startsBefore: DateTime
                    $endsAfter: DateTime
                    $includeRecurringInstances: Boolean
                ) {
                    events(
                        startsBefore: $startsBefore,
                        endsAfter: $endsAfter,
                        first: 50,
                        includeRecurringInstances: $includeRecurringInstances
                    ) {
                        edges {
                            node {
                                id
                                date: startAt
                                end: endAt
                                isAllDay
                            }
                        }
                    }
                }
            `,
            variables() {
                const now = this.$dayjs()
                    .month(this.displayedMonth)
                    .year(this.displayedYear);
                return {
                    includeRecurringInstances: true,
                    endsAfter: now.startOf('month'),
                    startsBefore: now.endOf('month'),
                };
            },
            update: (data) => initializeConnections(data).events,
        },
    },
    data() {
        return {
            displayedMonth: new Date().getMonth(),
            displayedYear: new Date().getFullYear(),
            selectedDate: null,
            listeners: {
                refetchOnCalendarChange: [EVENT_CREATED, EVENT_UPDATED, EVENT_DELETED],
            },
        };
    },
    computed: {
        isLoading() {
            return this.$apollo.loading;
        },
        customizeTextPath() {
            return this.isPersonalActive
                ? 'customizations.prompts.myBase'
                : 'customizations.prompts.base';
        },
        isCustomizationAllowed() {
            // For now, everyone can customize, however can expand on this
            return true;
        },
        isPersonalActive() {
            return isActiveBasePersonal();
        },
        customizeWidgetVal() {
            return this.personalBasePreferences?.homepage.shortcuts?.customize;
        },
        isCustomizeHide() {
            return this.customizeWidgetVal === 'HIDE';
        },
        isCustomizeSmall() {
            return this.customizeWidgetVal === 'SMALL';
        },
        isCustomizeFull() {
            return this.customizeWidgetVal === 'FULL';
        },
        showCustomizationWidget() {
            return !this.isCustomizeHide && this.isCustomizationAllowed;
        },
        integrationsWidgetVal() {
            return this.personalBasePreferences?.homepage.shortcuts?.integrations;
        },
        isIntegrationsHide() {
            return this.integrationsWidgetVal === 'HIDE';
        },
        isIntegrationsSmall() {
            return this.integrationsWidgetVal === 'SMALL';
        },
        isIntegrationsFull() {
            return this.integrationsWidgetVal === 'FULL';
        },
        showIntegrationsWidget() {
            return !this.isIntegrationsHide;
        },
    },
    methods: {
        selectDate(date) {
            const isSameDate = date === this.selectedDate;
            this.selectedDate = isSameDate ? null : date;
        },
        refetchOnCalendarChange() {
            this.$apollo.queries.calendarEvents.refetch();
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-home-side {
    &__tile {
        @apply
            p-3.5
            relative
            rounded-2xl
            shadow-lg
        ;
    }

    &__prompt {
        @apply
            font-semibold
            mb-2
            text-base
            text-center
        ;
    }

    &__image {
        max-height: 50px;

        @apply
            absolute
            bottom-0
            right-0
            rounded-br-2xl
        ;
    }

    &__integrate {
        max-height: 40px;

        @apply
            absolute
            bottom-1
            left-1
        ;
    }
}

</style>
