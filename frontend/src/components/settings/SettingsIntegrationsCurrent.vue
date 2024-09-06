<template>
    <div
        v-if="!$apollo.loading"
        class="o-settings-integrations-current"
    >
        <div
            v-for="integration in integrations"
            :key="integration.id"
            class="flex justify-between mb-8"
        >
            <div>
                <div
                    class="flex items-center mb-1"
                >
                    <div
                        class="o-settings-integrations-current__img mr-4"
                    >
                        <img
                            :src="'/images/integrations/' + integration.provider.toLowerCase() + 'Sm.png'"
                        />
                    </div>

                    <span
                        class="text-cm-600"
                    >
                        {{ integration.accountName }}
                    </span>
                </div>
                <div
                    class="inline-flex bg-cm-100 rounded-md px-3 py-1"
                >
                    <div
                        v-for="(data, index) in integration.scopes"
                        :key="data"
                        class="flex items-center text-sm"
                        :class="{ 'ml-4': index > 0 }"
                    >

                        <div
                            class="o-settings-integrations-current__circle circle-center bg-cm-00 mr-1"
                        >
                            <i
                                class="fal text-primary-600 text-sm"
                                :class="options[data]"
                            >
                            </i>
                        </div>

                        <span
                            v-t="'common.' + data.toLowerCase()"
                        >
                        </span>
                    </div>
                </div>
            </div>

            <DeleteButton
                @click="deleteIntegration(integration)"
            >
            </DeleteButton>
        </div>
    </div>
</template>

<script>

import DeleteButton from '@/components/buttons/DeleteButton.vue';

// const integrations = [
//     {
//         email: 'emma@ezekia.com',
//         type: 'microsoft',
//         id: 1,
//         data: ['calendar', 'emails'],
//     },
//     {
//         email: 'emma.hansen@gmail.com',
//         type: 'google',
//         id: 2,
//         data: ['todos'],
//     },
// ];

const options = {
    CALENDAR: 'fa-calendar-alt',
    TODOS: 'fa-square-check',
    EMAILS: 'fa-envelope',
};

export default {
    name: 'SettingsIntegrationsCurrent',
    components: {
        DeleteButton,
    },
    mixins: [
    ],
    props: {
        integrations: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'deleteIntegration',
    ],
    data() {
        return {
        };
    },
    computed: {

    },
    methods: {
        deleteIntegration(integration) {
            this.$emit('deleteIntegration', integration);
        },
    },
    created() {
        // this.integrations = integrations;
        this.options = options;
    },
};
</script>

<style scoped>

.o-settings-integrations-current {
    &__img {
        width: 20px;
    }

    &__circle {
        height: 23px;
        width: 23px;
    }
}

</style>
