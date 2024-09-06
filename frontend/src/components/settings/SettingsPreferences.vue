<template>
    <div
        v-if="!loadingPreferences"
        class="o-settings-preferences"
    >

        <IconContainer
            class="mb-10"
            header="Appearance"
            icon="fal fa-circle-nodes"
        >
            <PreferencesAppearance
                :colorMode="preferences.colorMode"
                @update:colorMode="updateColorMode"
            >
            </PreferencesAppearance>
        </IconContainer>

        <IconContainer
            class="mb-10"
            header="Dates and time"
            icon="fal fa-clock"
        >
            <PreferencesTime
                :weekdayStart="preferences.weekdayStart"
                :timezone="preferences.timezone"
                :timeFormat="preferences.timeFormat"
                :dateFormat="preferences.dateFormat"
                :timezoneError="timezoneError"
                @update:weekdayStart="updateWeekdayStart"
                @update:timezone="updateTimezone"
                @update:timeFormat="updateTimeFormat"
                @update:dateFormat="updateDateFormat"
            >
            </PreferencesTime>
        </IconContainer>

        <IconContainer
            class="mb-10"
            header="Money"
            icon="fal fa-hand-holding-circle-dollar"
        >
            <PreferencesMoney
                :moneyFormat="preferences.moneyFormat"
                @update:moneyFormat="updateMoneyFormat"
            >
            </PreferencesMoney>
        </IconContainer>

        <IconContainer
            header="Cookies"
            icon="fal fa-cookie-bite"
        >
            <PreferencesCookies>
            </PreferencesCookies>
        </IconContainer>
    </div>
</template>

<script>

import IconContainer from '@/components/display/IconContainer.vue';
import PreferencesAppearance from '@/components/settings/PreferencesAppearance.vue';
import PreferencesTime from '@/components/settings/PreferencesTime.vue';
import PreferencesMoney from '@/components/settings/PreferencesMoney.vue';
import PreferencesCookies from '@/components/settings/PreferencesCookies.vue';

import fetchUserPreferences, {
    updateColorMode,
    updateDateFormat,
    updateTimeFormat,
    updateMoneyFormat,
    updateTimezone,
    updateWeekdayStart,
    userPreferences,
} from '@/core/repositories/preferencesRepository.js';

export default {
    name: 'SettingsPreferences',
    components: {
        IconContainer,
        PreferencesAppearance,
        PreferencesTime,
        PreferencesCookies,
        PreferencesMoney,
    },
    mixins: [
    ],
    props: {

    },
    apollo: {
    },
    data() {
        return {
            loadingPreferences: true,
            preferences: null,
            timezoneError: '',
        };
    },
    computed: {
    },
    methods: {
        updateColorMode(colorMode) {
            updateColorMode(colorMode);
        },
        async updateWeekdayStart(day) {
            await updateWeekdayStart(day);
            this.$saveFeedback({ customMessagePath: 'feedback.responses.saved.preferences' });
        },
        async updateTimezone(timezone) {
            try {
                this.timezoneError = '';
                await updateTimezone(timezone);
                this.$saveFeedback({ customMessagePath: 'feedback.responses.saved.preferences' });
            } catch (e) {
                if (e.response?.status === 422 && e.response.data?.errors?.timezone?.length) {
                    this.timezoneError = e.response.data.errors.timezone[0];
                } else {
                    throw e;
                }
            }
        },
        async updateDateFormat(format) {
            await updateDateFormat(format);
            this.$saveFeedback({ customMessagePath: 'feedback.responses.saved.preferences' });
        },
        async updateTimeFormat(format) {
            await updateTimeFormat(format);
            this.$saveFeedback({ customMessagePath: 'feedback.responses.saved.preferences' });
        },
        async updateMoneyFormat(format) {
            await updateMoneyFormat(format);
            this.$saveFeedback({ customMessagePath: 'feedback.responses.saved.preferences' });
        },
    },
    watch: {

    },
    async created() {
        await fetchUserPreferences();
        this.preferences = userPreferences;
        this.loadingPreferences = false;
    },
};
</script>

<style>
.o-settings-preferences {
    .preferences-label {
        @apply
            block
            font-semibold
            text-cm-600
            text-xs
            uppercase
        ;
    }

    &__container {
        @apply
            border-b
            border-cm-400
            border-solid
            mb-12
            pb-12
        ;
    }
}
</style>
