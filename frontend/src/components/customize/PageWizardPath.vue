<template>
    <div class="o-page-wizard-path">
        <div class="max-w-xl">
            <h1 class="o-creation-wizard__header">
                {{ message }}
            </h1>

            <h2 class="o-creation-wizard__prompt mt-4">
                How would you like to create your page?
            </h2>

            <div class="flex flex-col max-w-xl">
                <SnazzyOption
                    v-for="option in pathOptions"
                    :key="option.nextPage"
                    class="m-2"
                    :isSelected="selectedPath === option.nextPage"
                    @click="setOption(option.nextPage)"
                    @keyup.enter="setOption(option.nextPage)"
                    @keyup.space="setOption(option.nextPage)"
                >
                    <div class="text-center p-2">

                        <i
                            class="fa-regular mb-2 text-2xl text-secondary-600"
                            :class="option.icon"
                        >
                        </i>
                        <p class="text-base text-primary-800">
                            {{ option.title }}
                        </p>

                        <p class="text-xssm font-normal text-cm-500">
                            {{ option.subtitle }}
                        </p>
                    </div>
                </SnazzyOption>
            </div>
            <div
                v-if="onCorePlan"
                class="mt-9 p-4 rounded-lg bg-secondary-100"
            >
                <p
                    class="font-bold text-lg text-secondary-800 mb-2 text-center"
                >
                    You are using Hylark's free Core Plan!
                </p>

                <div class="flex items-baseline">
                    <p class="leading-snug text-smbase text-cm-600">
                        Once you reach the maximum number of pages and features allowed by the Core Plan,
                        upgrade your account to add more.
                    </p>

                    <RouterLink
                        :to="{ name: 'settings.plans' }"
                        class="button--sm button-secondary ml-2 shrink-0"
                    >
                        <i
                            class="fa-regular fa-square-arrow-up-right mr-1"
                        >
                        </i>
                        Upgrade
                    </RouterLink>
                </div>

                <div class="mt-6">
                    <p class="font-semibold mb-1">
                        Remaining on your account
                    </p>

                    <div class="text-cm-600 text-smbase">
                        <div
                            v-for="(factor, factorKey) in factors"
                            :key="factorKey"
                        >
                            <span
                                class="font-medium mr-1"
                            >
                                {{ $t(`customizations.allowedLabels.${factorKey}`) }}:
                            </span>
                            {{ getRemainingFactor(factor.remaining) }} / {{ factor.allowed }}

                            <i
                                v-if="factor.remaining < 1"
                                class="text-peach-600 fa-solid fa-exclamation-circle ml-2"
                            >
                            </i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import SnazzyOption from '@/components/buttons/SnazzyOption.vue';
import { activeBase } from '@/core/repositories/baseRepository.js';

const messageOptions = [
    'Let\'s get started!',
    'New page, let\'s do this!',
];

const customMessages = {
    2: ['Hi again!'],
    3: ['Third page, let\'s do this', 'Third new page, let\'s go'],
    4: ['You\'re on a roll with pages!', 'Hello again!'],
    5: ['Back again, let\'s do this'],
    10: ['10 new pages in one session... epic'],
    20: ['20 new pages? We\'re impressed'],
    50: ['50 new pages? You\'ve got this.'],
    100: ['Have you been counting? We have. This is page #100.'],
};

const pathOptions = [
    {
        title: 'Pick from pre-set pages',
        subtitle: 'Find the page you want to add among our pre-defined pages',
        icon: 'fa-regular fa-treasure-chest',
        nextPage: 'PAGES',
    },
    {
        title: 'Make a custom page',
        subtitle: 'Build your new page from a blank slate (advanced).',
        icon: 'fa-regular fa-magic-wand-sparkles',
        nextPage: 'TYPE',
    },
];

export default {
    name: 'PageWizardPath',
    components: {
        SnazzyOption,
    },
    mixins: [
    ],
    props: {
        selectedPath: {
            type: [String, null],
            required: true,
        },
        pageCounter: {
            type: Number,
            required: true,

        },
    },
    emits: [
        'setSelectedPath',
    ],
    data() {
        return {

        };
    },
    computed: {
        messageList() {
            const custom = customMessages[this.pageCounter];
            return custom || messageOptions;
        },
        randomInt() {
            return _.random(1, this.messageList.length);
        },
        message() {
            return this.messageList[this.randomInt - 1];
        },
        activeBase() {
            return activeBase();
        },
        onCorePlan() {
            return !this.activeBase.isSubscribed;
        },
        user() {
            return this.$root.authenticatedUser;
        },
        plan() {
            return this.activeBase.plan;
        },
        features() {
            return this.plan.features;
        },
        used() {
            return this.plan.used;
        },
        factors() {
            const planKeys = ['pages', 'categories', 'pipelineGroups', 'statusGroups', 'tagGroups'];
            const obj = {};

            planKeys.forEach((key) => {
                const used = this.used[key];
                const allowed = this.features[key];
                const remaining = allowed - used;
                const factor = { remaining, allowed };
                obj[key] = factor;
            });

            return obj;
        },
    },
    methods: {
        setOption(path) {
            this.$emit('setSelectedPath', path);
        },
        getRemainingFactor(remaining) {
            return remaining < 1 ? 0 : remaining;
        },
    },
    created() {
        this.pathOptions = pathOptions;
    },
};
</script>

<style scoped>

/*.o-page-wizard-path {

} */

</style>
