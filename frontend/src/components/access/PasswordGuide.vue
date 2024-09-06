<template>
    <div class="o-password-guide">
        <div
            v-for="requirement in requirements"
            :key="requirement"
            class="o-password-guide__requirement"
        >
            <i
                class="fas fa-circle o-password-guide__circle"
                :class="iconClass(requirement)"
            >
            </i>

            <p
                class="o-password-guide__explanation"
                :class="textClass(requirement)"
            >
                {{ $t('registration.initial.password.requirements.' + requirement) }}
            </p>
        </div>
    </div>
</template>

<script>

const requirements = [
    'chars',
    'lowercase',
    'uppercase',
    'numberSymbol',
];

export default {
    name: 'PasswordGuide',
    components: {

    },
    mixins: [
    ],
    props: {
        text: {
            type: String,
            default: '',
        },
    },
    emits: [
        'passwordCriteria',
    ],
    data() {
        return {

        };
    },
    computed: {
        meetsChars() {
            return this.text.length >= 8;
        },
        meetsLowercase() {
            return (/[a-z]/.test(this.text));
        },
        meetsUppercase() {
            return (/[A-Z]/.test(this.text));
        },
        meetsNumberSymbol() {
            return (/[0-9\W_]/.test(this.text));
        },
        criteria() {
            return {
                chars: this.meetsChars,
                lowercase: this.meetsLowercase,
                uppercase: this.meetsUppercase,
                numberSymbol: this.meetsNumberSymbol,
            };
        },
        allCriteriaMet() {
            return this.meetsChars
                && this.meetsLowercase
                && this.meetsUppercase
                && this.meetsNumberSymbol;
        },
    },
    methods: {
        iconClass(requirement) {
            const criteria = this.criteria[requirement];
            return criteria ? 'text-green-600' : 'text-peach-300';
        },
        textClass(requirement) {
            const criteria = this.criteria[requirement];
            return criteria ? 'text-cm-700' : 'text-cm-400';
        },
    },
    watch: {
        allCriteriaMet(newValue) {
            this.$emit('passwordCriteria', newValue);
        },
    },
    created() {
        this.requirements = requirements;
    },
};
</script>

<style scoped>

.o-password-guide {
    @apply
        flex
        flex-wrap
        -mx-2
        text-xs
    ;

    &__requirement {
        @apply
            flex
            font-semibold
            items-center
            px-2
            py-1
            w-1/2
        ;
    }

    &__circle {
        @apply
            mr-2
            text-xxxs
        ;
    }
}

</style>
