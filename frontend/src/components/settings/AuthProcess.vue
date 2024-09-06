<template>
    <ol class="o-auth-process text-smbase">
        <li
            v-for="stage in activeStages"
            :key="stage.val"
            class="flex mb-4"
        >
            <span
                class="relative mr-4"
                :class="iconClass(stage.val)"
            >
                <i class="fal fa-circle">
                </i>
                <i class="far fa-check absolute -top-1 -right-1 text-lg">
                </i>
            </span>

            <div>
                <div
                    class="flex"
                    :class="textClass(stage.val)"
                >
                    <div class="mr-1">
                        {{ stage.val }}.
                    </div>

                    <p>
                        {{ stage.text }}
                    </p>
                </div>

                <div
                    v-if="svg && currentStage >= 2 && stage.val === 2"
                    class="mt-3 ml-4"
                >
                    <div class="flex flex-wrap">
                        <div
                            v-dompurify-html:svg="svg"
                            class="mr-4"
                        >
                        </div>

                        <div class="mt-4">
                            <p class="uppercase text-xs text-cm-400 font-semibold">
                                Code:
                            </p>
                            {{ code }}
                        </div>
                    </div>

                    <button
                        v-if="currentStage === 2"
                        v-t="'common.done'"
                        class="bg-primary-600 hover:bg-primary-500 text-cm-00 button mt-2"
                        type="button"
                        @click="nextStep(2)"
                    >
                    </button>
                </div>

                <div
                    v-if="currentStage === 3 && stage.val === 3"
                    class="o-auth-process__final"
                >
                    <FormWrapper
                        :form="confirmationCodeForm"
                    >
                        <InputLine
                            formField="code"
                            placeholder="Type code here"
                        >
                        </InputLine>
                    </FormWrapper>

                    <button
                        v-if="confirmationCodeForm.code"
                        v-t="'common.confirm'"
                        type="button"
                        class="bg-primary-600 hover:bg-primary-500 text-cm-00 button ml-4"
                        @click="confirmCode"
                    >
                    </button>
                </div>
            </div>
        </li>
    </ol>
</template>

<script>

/* eslint-disable */
// Language later ones text formalized
const stages = [
    {
        val: 1,
        text: 'Activate two-factor authentication',
    },
    {
        val: 2,
        text: 'Scan the QR code below on the authenticator app on your mobile. Or if you prefer, manually input the code into your authenticator app.',
    },
    {
        val: 3,
        text: 'Your authenticator app should now display a 6-digit code to check that the connection between Hylark and the app was made correctly. Type this 6-digit code below.',
    },
];
/* eslint-enable */

export default {
    name: 'AuthProcess',
    components: {

    },
    mixins: [
    ],
    props: {
        currentStage: {
            type: Number,
            required: true,
        },
        svg: {
            type: String,
            default: '',
        },
        code: {
            type: String,
            default: '',
        },
    },
    emits: [
        'set2faStep',
        'confirmCode',
    ],
    data() {
        return {
            confirmationCodeForm: this.$form({
                code: '',
            }),
        };
    },
    computed: {
        activeStages() {
            return stages.slice(0, this.currentStage);
        },
    },
    methods: {
        isBeforeCurrent(stage) {
            return stage < this.currentStage;
        },
        isCurrent(stage) {
            return stage === this.currentStage;
        },
        isLater(stage) {
            return stage > this.currentStage;
        },
        iconClass(stage) {
            if (this.isBeforeCurrent(stage)) {
                return 'text-emerald-600';
            }
            return 'text-cm-400';
        },
        textClass(stage) {
            if (this.isBeforeCurrent(stage) || this.isCurrent(stage)) {
                return 'font-semibold text-cm-600';
            }
            return 'text-cm-500';
        },
        nextStep(currentStep) {
            const nextStep = currentStep + 1;
            this.$emit('set2faStep', nextStep);
        },
        confirmCode() {
            this.$emit('confirmCode', this.confirmationCodeForm);
        },
    },
    created() {
        this.stages = stages;
    },
};
</script>

<style scoped>

.o-auth-process {
    &__final {
        height:  36px;
        @apply
            flex
            items-end
            ml-4
            mt-2
        ;
    }
}

</style>
