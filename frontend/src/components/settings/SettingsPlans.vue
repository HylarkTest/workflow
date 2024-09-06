<template>
    <div class="o-settings-plans">
        <SettingsHeaderLine
            v-if="onCorePlan || subscription"
            class=""
        >
            <template #header>
                {{ $t('settings.plans.currentPlan') }}
            </template>

            <div
                class="bg-cm-100 rounded-xl font-semibold px-6 py-4 inline-flex mb-4"
            >
                <span>
                    {{ planName }}
                </span>
                <span
                    v-if="onCorePlan"
                    class="ml-1"
                >
                    - {{ $t('common.free') }}
                </span>
            </div>

            <div
                v-if="isCollaborativeBase"
                class="mt-6 mb-4"
            >
                <div
                    class="text-smbase font-semibold mb-2"
                >
                    <span>
                        {{ userType }}:
                    </span>
                    <span
                        class="text-primary-600"
                    >
                        {{ membersLength }}
                    </span>
                </div>

                <div class="text-sm text-cm-500">
                    <p
                        v-t="'settings.plans.collaborationLearnMore'"
                        class="mb-1"
                    >
                    </p>
                    <button
                        v-t="'common.learnMore'"
                        class="button--sm button-primary--light"
                        type="button"
                        @click="openLearnMore"
                    >
                    </button>
                </div>
            </div>

            <div
                v-if="onCorePlan || updating || renewing"
            >
                <button
                    v-if="updating"
                    v-t="'common.close'"
                    class="button button-gray mt-5"
                    type="button"
                    @click="updating = false"
                >
                </button>
                <SubscriptionForm
                    class="mt-2"
                    :user="user"
                    :updating="updating"
                    :renewing="renewing"
                    :seatsNumber="membersLength"
                    @subscribe="onSubscribe"
                ></SubscriptionForm>
            </div>

            <div
                v-else-if="subscription"
                class="border-t border-solid border-cm-200"
            >
                <div class="text-sm mt-2">
                    <span class="header-uppercase-light">Subscribed on: </span>
                    {{ date }}
                </div>
                <div class="text-sm mt-2">
                    <span class="header-uppercase-light">Period: </span>
                    {{ period }}
                </div>
                <div class="text-sm mt-2">
                    <span class="header-uppercase-light">Amount: </span>
                    {{ amount }}
                </div>
                <div
                    v-if="discount"
                    class="text-sm mt-2"
                >
                    <span class="header-uppercase-light">Discount: </span>
                    {{ discountText }}
                </div>
                <div
                    v-if="next"
                    class="text-sm mt-2"
                >
                    <span class="header-uppercase-light">Next billing date: </span>
                    {{ next }}
                </div>
                <div
                    v-else
                    class="text-sm mt-2"
                >
                    <span class="header-uppercase-light">Subscription ends at: </span>
                    {{ ends }}
                </div>
                <div
                    v-if="billedUser"
                    class="text-sm mt-6"
                >
                    <span
                        v-t="'settings.plans.billedUser'"
                        class="header-uppercase-light mb-1 block"
                    >
                    </span>

                    <ProfileNameImage
                        :profile="billedUser"
                    >
                    </ProfileNameImage>
                </div>

                <div
                    v-if="!ends"
                    class="mt-6"
                >
                    <button
                        v-t="'common.changeDetails'"
                        class="button button-primary mr-2"
                        type="button"
                        @click="changeDetails"
                    >
                    </button>

                    <button
                        v-t="'settings.plans.cancel'"
                        class="button button-peach"
                        type="button"
                        @click="openModal"
                    >
                    </button>
                </div>
                <div
                    v-else
                    class="mt-6"
                >
                    <button
                        v-t="'settings.plans.renew'"
                        class="button button-primary mr-2"
                        type="button"
                        @click="renew"
                    >
                    </button>
                </div>

                <BillingHistory
                    class="mt-10"
                    :subscription="subscription"
                >
                </BillingHistory>
            </div>

            <div
                class="mt-10 rounded-xl bg-sky-100 p-4 inline-flex font-medium text-sm text-sky-800 items-start"
            >
                <p v-t="'settings.plans.plansLearnMore'">
                </p>
                <a
                    href="https://hylark.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="ml-1 hover:underline text-sky-600"
                >
                    {{ $t('common.learnMore') }}
                </a>
            </div>

        </SettingsHeaderLine>

        <ConfirmModal
            v-if="isModalOpen"
            icon="fal fa-person-walking-arrow-right"
            headerTextPath="settings.plans.cancelHeader"
            actionButtonTextPath="common.yes"
            cancelButtonTextPath="common.no"
            actionButtonColorClasses="bg-peach-600 hover:bg-peach-500"
            @proceedWithAction="cancelPlan"
            @closeModal="closeModal"
            @cancelAction="closeModal"
        >
            <p v-t="'settings.plans.cancelPrompt'"></p>
        </ConfirmModal>

        <AssistModal
            v-if="showLearnMore"
            headerTextString="Upgrading a collaborative base"
            @closeModal="closeLearnMore"
        >
            <QuestionsAnswers
                :qaArr="qaArr"
            >
            </QuestionsAnswers>
        </AssistModal>
    </div>
</template>

<script>

import axios from 'axios';

import { getOperationName } from '@apollo/client/utilities';
import SubscriptionForm from '@/components/billing/SubscriptionForm.vue';
import User from '@/core/models/User.js';

import ConfirmModal from '@/components/assets/ConfirmModal.vue';
import ProfileNameImage from '@/components/profile/ProfileNameImage.vue';
import BillingHistory from '@/components/billing/BillingHistory.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import { defaultApolloClient } from '@/http/apollo/defaultApolloClient.js';
import USER from '@/graphql/Me.gql';
import { getSymbol } from '@/core/helpers/currencyHelpers.js';

import { isActiveBaseCollaborative } from '@/core/repositories/baseRepository.js';
import { inUsersTimezone } from '@/core/repositories/preferencesRepository.js';

const qaArr = [1, 2, 3, 4].map((number) => {
    return {
        questionPath: `upgrade.information.collaborativeBase.${number}.question`,
        answerPath: `upgrade.information.collaborativeBase.${number}.answer`,
    };
});

export default {
    name: 'SettingsPlans',
    components: {
        SubscriptionForm,
        ConfirmModal,
        ProfileNameImage,
        BillingHistory,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        user: {
            type: User,
            required: true,
        },
    },
    data() {
        return {
            updating: false,
            subscription: null,
            showLearnMore: false,
            renewing: false,
        };
    },
    computed: {
        isCollaborativeBase() {
            return isActiveBaseCollaborative();
        },
        activeBase() {
            return this.user.activeBase();
        },
        membersLength() {
            return this.activeBase.members?.length;
        },
        onCorePlan() {
            return !this.activeBase.isSubscribed;
        },
        userType() {
            const baseKey = 'settings.plans';
            const endKey = this.onCorePlan ? 'baseMembers' : 'numberOfSeats';
            return this.$t(`${baseKey}.${endKey}`);
        },
        date() {
            const date = inUsersTimezone(this.subscription?.subscribedAt);
            return date.format('lll');
        },
        planCamel() {
            return this.onCorePlan ? 'core' : _.camelCase(this.subscription?.name) || 'upgraded';
        },
        planName() {
            return this.$t(`plans.names.${this.planCamel}`);
        },
        periodCamel() {
            return _.camelCase(this.subscription?.period);
        },
        period() {
            if (this.subscription?.isSubscribed) {
                const period = `${this.periodCamel}ly`;
                return this.$t(`common.dates.${period}`);
            }
            return null;
        },
        billedUser() {
            return this.subscription?.billedUser;
        },
        amount() {
            if (!this.subscription) {
                return null;
            }
            return `${getSymbol(this.subscription.currency)}${this.subscription.amount / 100}`;
        },
        next() {
            const next = this.subscription?.nextPaymentDate;
            const date = next && inUsersTimezone(next);
            return next && date.format('lll');
        },
        ends() {
            const end = this.subscription?.subscriptionEndsAt;
            const date = end && inUsersTimezone(end);
            return end && date.format('lll');
        },
        discount() {
            return this.subscription?.discount;
        },
        discountText() {
            const discount = this.discount;
            if (!discount) {
                return '';
            }
            const {
                amountOff,
                currency,
                percentOff,
                remaining,
                duration,
            } = discount;
            const discountAmount = amountOff
                ? currency + amountOff
                : `${percentOff}%`;

            if (remaining) {
                return `${discountAmount} off for ${duration} months (${remaining} months remaining)`;
            }

            return `${discountAmount} off forever`;
        },
    },
    methods: {
        changeDetails() {
            this.updating = true;
        },
        async cancelPlan() {
            await axios.delete('/billing/subscription');
            this.fetchSubscription();
            this.resetCache();
            this.closeModal();
        },
        async renew() {
            if (this.billedUser.isAuthenticatedUser) {
                await axios.post('/billing/subscription/renew');
                this.fetchSubscription();
                this.resetCache();
            } else {
                this.renewing = true;
            }
        },
        resetCache() {
            defaultApolloClient().cache.evict(getOperationName(USER));
        },
        async fetchSubscription() {
            const response = await axios.get('/billing/subscription');
            this.subscription = response.data.data;
        },
        onSubscribe() {
            this.fetchSubscription();
            this.resetCache();
            this.updating = false;
        },
        openLearnMore() {
            this.showLearnMore = true;
        },
        closeLearnMore() {
            this.showLearnMore = false;
        },
    },
    created() {
        this.fetchSubscription();
        this.qaArr = qaArr;
    },
};
</script>

<style scoped>

/*.o-settings-plans {

} */

</style>
