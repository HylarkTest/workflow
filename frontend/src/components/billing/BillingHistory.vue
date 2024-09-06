<template>
    <div class="o-billing-history">
        <h5 class="font-semibold mb-2">
            Billing history
        </h5>

        <div class="text-smbase">
            <div
                v-for="item in history"
                :key="item.id"
                class="flex flex-wrap odd:bg-primary-100 py-2 px-4 rounded-lg gap-4"
            >
                <div>
                    {{ formattedDate(item.date) }}
                </div>

                <div>
                    {{ item.amount }}
                </div>

                <div
                    class="font-bold"
                    :class="statusClasses(item.status)"
                >
                    {{ $t('plans.statuses.' + item.status) }}

                    <i
                        v-if="statusIcon(item.status)"
                        class="fa-solid"
                        :class="statusIcon(item.status)"
                    >
                    </i>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { inUsersTimezone } from '@/core/repositories/preferencesRepository.js';

const statusObj = {
    paid: {
        color: 'emerald',
        icon: 'fa-check-circle',
    },
    pending: {
        color: 'gold',
    },
    failed: {
        color: 'rose',
    },
    cancelled: {
        color: 'peach',
    },
};

export default {
    name: 'BillingHistory',
    components: {

    },
    mixins: [
    ],
    props: {
        subscription: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        history() {
            return this.subscription.history;
        },
    },
    methods: {
        formattedDate(date) {
            const tzDate = inUsersTimezone(date);
            return tzDate.format('lll');
        },
        statusClasses(status) {
            const obj = statusObj[status];
            const color = obj.color;
            return `text-${color}-600`;
        },
        statusIcon(status) {
            return statusObj[status]?.icon;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-billing-history {

} */

</style>
