<template>
    <div class="o-event-uneditable">
        <FeatureUneditableBase
            :featureItem="event"
        >
            <div class="mb-6">
                <label class="header-form mb-3">
                    {{ $t('features.events.labels.when') }}
                </label>

                <div v-if="event.isAllDay">
                    {{ allDayDate }}
                </div>
                <div
                    v-else
                >
                    {{ formattedStartDate }} - {{ formattedEndDate }}
                </div>

            </div>

            <!-- TODO: add recurrence when returned by b-e -->
            <!-- <div
                v-if="event.recurrence"
                class="mb-6"
            >
                <label class="header-form mb-3">
                    {{ $t('features.events.labels.repeat') }}
                </label>

                <div>
                    {{ event.recurrence }}
                </div>
            </div> -->

            <div
                v-if="event.location"
                class="mb-6"
            >
                <label class="header-form mb-3">
                    {{ $t('features.events.labels.location') }}
                </label>

                <div>
                    {{ event.location }}
                </div>
            </div>

            <div
                v-if="event.description"
                class="mb-6"
            >
                <label class="header-form mb-3">
                    {{ $t('labels.description') }}
                </label>

                <div>
                    {{ event.description }}
                </div>
            </div>
        </FeatureUneditableBase>
    </div>
</template>

<script>

import FeatureUneditableBase from '@/components/features/FeatureUneditableBase.vue';
import { timezone } from '@/core/repositories/preferencesRepository.js';

export default {
    name: 'EventUneditable',
    components: {
        FeatureUneditableBase,
    },
    mixins: [
    ],
    props: {
        event: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
    ],
    data() {
        return {
            timezone,
        };
    },
    computed: {
        allDayDate() {
            return this.startDateObject.format('LL');
        },
        startDateObject() {
            return this.$dayjs.utc(this.event.date);
        },
        endDateObject() {
            return this.$dayjs.utc(this.event.end);
        },
        startDateInUserTimezone() {
            return this.startDateObject.tz(this.timezone);
        },
        endDateInUserTimezone() {
            return this.endDateObject.tz(this.timezone);
        },
        formattedStartDate() {
            return this.startDateInUserTimezone.format('LLLL');
        },
        formattedEndDate() {
            return this.endDateInUserTimezone.format('LLLL');
        },
    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>
/* .o-event-uneditable {

} */
</style>
