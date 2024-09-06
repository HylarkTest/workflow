<template>
    <div
        v-show="!isModalOpen && showPrompt"
        class="c-support-prompts flex stop-blur items-center"
    >
        <div class="text-xs font-bold text-primary-600 mr-2">
            {{ sectionName }}
        </div>
        <div class="text-xssm font-semibold flex">
            <ButtonEl
                v-if="tipsLength"
                class="button-primary h-6 centered rounded-full px-3 shadow-lg"
                @click="toggleTips"
            >
                <i
                    v-if="activeTipsLength"
                    class="far fa-times mr-1"
                >
                </i>

                Tips ({{ tipsLength }})
            </ButtonEl>
            <ButtonEl
                v-if="!hideSearch"
                class="circle-center button-primary w-6 h-6 shadow-lg ml-1"
                @click="openSupportModal"
            >
                <i
                    class="fas fa-question"
                >
                </i>
            </ButtonEl>
        </div>
    </div>

    <SupportModal
        v-if="isModalOpen"
        :sectionTitle="sectionTitle"
        :sectionName="sectionName"
        :tips="tips"
        :relevantTopics="relevantTopics"
        :contentQuery="contentQuery"
        @closeModal="closeModal"
    >
    </SupportModal>

</template>

<script>

import { unref } from 'vue';

import SupportModal from '@/components/support/SupportModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import interactsWithActiveTips from '@/vue-mixins/support/interactsWithActiveTips.js';

import UPDATE_ACTIVE_TIPS from '@/graphql/client/UpdateActiveTips.gql';

export default {
    name: 'SupportPrompts',
    components: {
        SupportModal,
    },
    mixins: [
        interactsWithModal,
        interactsWithActiveTips,
    ],
    props: {
        supportArr: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        firstSupport() {
            return unref(this.supportArr[0]);
        },
        sectionName() {
            return this.firstSupport.sectionName;
        },
        tips() {
            return this.firstSupport.tips;
        },
        relevantTopics() {
            return this.firstSupport.relevantTopics;
        },
        contentQuery() {
            return this.firstSupport.contentQuery;
        },
        sectionTitle() {
            return this.firstSupport.sectionTitle || this.sectionName;
        },
        hidePromptIf() {
            return this.firstSupport.hidePromptIf;
        },
        hideSearch() {
            return this.firstSupport.hideSearch;
        },
        showPrompt() {
            return !this.hidePromptIf;
        },
        firstTip() {
            return {
                ...this.tips[0],
                active: true,
            };
        },
        remainingTips() {
            return this.tips.slice(1);
        },
        tipsLength() {
            return this.tips?.length;
        },
        activeTipsLength() {
            return this.activeTips?.length;
        },
    },
    methods: {
        openSupportModal() {
            this.openModal();
        },
        toggleTips() {
            const tipsVal = this.activeTipsLength
                ? null
                : [
                    this.firstTip,
                    ...this.remainingTips,
                ];
            this.$apollo.mutate({
                mutation: UPDATE_ACTIVE_TIPS,
                variables: {
                    tips: tipsVal,
                },
                client: 'defaultClient',
            });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-support-prompts {
}*/

</style>
