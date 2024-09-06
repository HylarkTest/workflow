<template>
    <div class="o-registration-steps">
        <StepsDisplay
            :steps="shownSteps"
            :currentStep="currentStep"
        >
        </StepsDisplay>
        <slot>
        </slot>
    </div>
</template>

<script>

import StepsDisplay from '@/components/display/StepsDisplay.vue';

const steps = [
    {
        val: 'start',
    },
    {
        val: 'uses',
        icon: 'fa-play',
    },
    {
        val: 'refine',
        icon: 'fa-play',
    },
    // {
    //     val: 'templates',
    //     icon: 'fa-object-group',
    // },
    {
        val: 'spaces',
        icon: 'fa-file',
    },
    // {
    //     val: 'pages',
    //     icon: 'fa-file',
    // },
    // {
    //     val: 'extras',
    //     icon: 'fa-plus',
    // },
    // {
    //     val: 'interests',
    //     icon: 'fa-pencil-paintbrush',
    // },
    {
        val: 'confirm',
        icon: 'fa-check',
    },
];

export default {
    name: 'RegistrationSteps',
    components: {
        StepsDisplay,
    },
    mixins: [
    ],
    props: {
        selectedSteps: {
            type: Array,
            required: true,
        },
        currentStepVal: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
        };
    },
    computed: {
        shownSteps() {
            return this.selectedSteps.map((step) => {
                return _.find(this.steps, { val: step });
            });
        },
        currentStep() {
            return _.findIndex(this.shownSteps, { val: this.currentStepVal });
        },
    },
    methods: {

    },
    created() {
        this.steps = steps;
    },
};
</script>

<style scoped>

.o-registration-steps {
    @apply
        bg-white
        fixed
        flex
        justify-center
        left-0
        py-8
        top-0
        w-screen
        z-cover
    ;
}

</style>
