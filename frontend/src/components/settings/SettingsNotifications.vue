<template>
    <div
        v-if="!loadingPreferences"
        class="o-settings-notifications"
    >
        <IconContainer
            class="mb-10"
            header="In-app web notifications"
            icon="fal fa-bell"
        >
            <div
                v-for="type in appNotifications"
                :key="type.val"
                class="o-settings-notifications__item"
            >
                <div
                    class="w-16 shrink-0"
                >
                    <ToggleButton
                        :modelValue="notificationValue(type)"
                        :disabled="type.forcedTrue"
                        @update:modelValue="updateAppNotifications(type, $event)"
                    >
                    </ToggleButton>
                </div>

                <div>
                    <h5
                        v-t="getPath(type.val)"
                        class="font-semibold text-cm-700"
                    >
                    </h5>
                    <p
                        v-t="descriptionPath(type.val)"
                        class="text-smbase"
                    >

                    </p>
                </div>
            </div>
        </IconContainer>

        <IconContainer
            header="Email notifications"
            icon="fal fa-envelope-dot"
        >
            <div class="mb-2">
                Security and essential account-related emails cannot be disabled.
            </div>

            <div>
                At this time, no other emails are sent from Hylark.
            </div>
        </IconContainer>
    </div>
</template>

<script>

import { arrRemove } from '@/core/utils.js';

import IconContainer from '@/components/display/IconContainer.vue';
import fetchUserPreferences, {
    updateActiveNotifications,
    userPreferences,
} from '@/core/repositories/preferencesRepository.js';

const appNotifications = [
    {
        val: 'ACCOUNT',
        forcedTrue: true,
    },
    {
        val: 'NEW_FEATURES',
    },
    {
        val: 'TIPS',
    },
];

export default {
    name: 'SettingsNotifications',
    components: {
        IconContainer,
    },
    mixins: [
    ],
    props: {

    },
    data() {
        return {
            loadingPreferences: true,
            preferences: null,
        };
    },
    computed: {
    },
    methods: {
        getPath(val) {
            return `notifications.${_.camelCase(val)}`;
        },
        descriptionPath(val) {
            return `settings.notifications.app.${_.camelCase(val)}.description`;
        },
        notificationValue(type) {
            if (type.forcedTrue) {
                return true;
            }
            return this.isNotificationActive(type);
        },
        isNotificationActive(type) {
            return this.preferences.activeAppNotifications.includes(type.val);
        },
        updateAppNotifications(type, val) {
            let newNotifications;
            if (val && !this.isNotificationActive(type)) {
                newNotifications = [...this.preferences.activeAppNotifications, type.val];
            } else if (!val && this.isNotificationActive(type)) {
                newNotifications = arrRemove(this.preferences.activeAppNotifications, type.val);
            }
            updateActiveNotifications(newNotifications);
        },
    },
    async created() {
        this.appNotifications = appNotifications;
        await fetchUserPreferences();
        this.preferences = userPreferences;
        this.loadingPreferences = false;
    },
};
</script>

<style scoped>
.o-settings-notifications {
    &__item {
        @apply
            flex
        ;

        &:not(:last-child) {
            @apply
                mb-6
            ;
        }
    }
}
</style>
