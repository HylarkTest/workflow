export default {
    data() {
        return {
            currentStep: '', // In component
            processing: false,
            forcedNextAction: null,
        };
    },
    computed: {
        stepPascal() {
            return _.pascalCase(this.currentStep);
        },
        currentStepIndex() {
            return _.findIndex(this.steps, { step: this.currentStep });
        },
        backAction() {
            const back = this.currentStepObj.goBack;
            if (back) {
                return back;
            }
            const backIndex = this.currentStepIndex - 1;
            return this.steps[backIndex].step;
        },
        nextAction() {
            if (this.forcedNextAction) {
                return this.forcedNextAction;
            }
            const next = this.currentStepObj.goNext;
            if (next) {
                return next;
            }
            const nextIndex = this.currentStepIndex + 1;
            return this.steps[nextIndex]?.step;
        },
        seeBack() {
            return !this.currentStepObj.hideBack;
        },
        seeNext() {
            return !!this.currentStepObj.seeNext;
        },
        nextText() {
            const nextKey = this.currentStepObj.nextText || 'next';
            return `common.${nextKey}`;
        },
        currentStepObj() {
            return this.steps[this.currentStepIndex];
        },
        steps() {
            return []; // In component
        },
    },
    methods: {
        pressedEnter() {
            if (this.seeNext) {
                this.goNext();
            }
        },
        goBack() {
            if (this.currentStepObj.backFunction) {
                this[this.currentStepObj.backFunction]();
            }
            this.currentStep = this.backAction;
        },
        goNext() {
            // In component
        },
    },
};
