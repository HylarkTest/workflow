<template>
    <RegistrationBase
        class="o-registration-start"
        :showNext="!!baseType && !!baseName"
        @nextStep="$emit('nextStep', 'start')"
    >
        <template
            #title
        >
            {{ $t('registration.start.title') }}
        </template>

        <div class="o-registration-start__options">
            <ButtonEl
                v-for="option in options"
                :key="option.val"
                :value="option.val"
                class="o-registration-start__option"
                :class="optionClasses(option)"
                @click="selectBaseType(option)"
                @keyup.enter="selectBaseType(option)"
                @keyup.space="selectBaseType(option)"
            >
                <QuarterCircle
                    class="overflow-hidden h-28 w-28 absolute bg-white"
                    :class="option.quarterPosition"
                    :point="option.quarterPoint"
                >
                    <img
                        class="h-full w-full object-cover rounded-xl opacity-70"
                        :src="getImageSrc(option)"
                    >
                </QuarterCircle>

                <div class="relative z-over">
                    <div class="centered">
                        <div class="o-registration-start__circle centered">
                            <i
                                class="fa-regular"
                                :class="[option.icon, textColor(option, '500')]"
                            >
                            </i>
                        </div>
                    </div>
                    <p
                        v-md-text="$t(optionName(option))"
                        class="o-registration-start__text max-w-full"
                        :class="textColor(option, '700')"
                    >
                    </p>

                    <div
                        class="mt-1 text-xssm"
                    >
                        <div
                            v-for="line in option.for"
                            :key="line"
                            class="rounded-full py-1 px-3 flex items-baseline"
                        >
                            <i
                                class="fa-regular fa-thumbs-up mr-2"
                                :class="textColor(option, '400')"
                            >
                            </i>
                            <p
                                class="text-cm-600"
                            >
                                {{ $t(`registration.start.for.${line}`) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="isSelected(option)"
                    class="o-registration-start__check circle-center"
                    :class="bgColor(option, '600')"
                    :title="$t('common.selected')"
                >
                    <i
                        class="far fa-check"
                    >
                    </i>
                </div>
            </ButtonEl>
        </div>

        <div
            v-if="isCollaborative"
            class="centered mt-8 flex-col"
        >
            <label
                class="header-2 mb-2"
            >
                What is the name of your group?
            </label>

            <div
                class="o-registration-start__input"
            >
                <InputBox
                    v-model="baseName"
                    class="w-full"
                    :maxLength="maxBaseNameLength"
                    bgColor="gray"
                    placeholder="Type the name here!"
                >
                </InputBox>
            </div>
        </div>
    </RegistrationBase>
</template>

<script>

import RegistrationBase from '@/components/access/RegistrationBase.vue';
import providesColors from '@/vue-mixins/style/providesColors.js';
import { maxBaseNameLength } from '@/core/data/bases.js';

const options = [
    {
        val: 'PERSONAL',
        icon: 'fa-user',
        color: 'gold',
        for: ['individuals'],
        quarterPosition: '-bottom-4 -left-4',
        quarterPoint: 'bottom-left',
    },
    {
        val: 'COLLABORATIVE',
        icon: 'fa-users',
        color: 'azure',
        for: ['groups', 'businesses', 'clubs', 'friends'],
        quarterPosition: '-top-4 -right-4',
        quarterPoint: 'top-right',
    },
];

export default {
    name: 'RegistrationStart',
    components: {
        RegistrationBase,
    },
    mixins: [
        providesColors,
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'nextStep',
        'mounted',
        'update:base',
    ],
    data() {
        return {
        };
    },
    computed: {
        baseType() {
            return this.base.baseType;
        },
        isCollaborative() {
            return this.baseType === 'COLLABORATIVE';
        },
        baseName: {
            get() {
                return this.base.name;
            },
            set(val) {
                this.$emit('update:base', {
                    ...this.base,
                    name: val,
                });
            },
        },
    },
    methods: {
        optionClasses(option) {
            return [this.selectedClass(option), this.colorClasses(option)];
        },
        colorClasses(option) {
            return `shadow-${option.color}-300/40 ${this.bgColor(option, '100')}`;
        },
        bgColor(option, intensity) {
            return `bg-${option.color}-${intensity}`;
        },
        isSelected(option) {
            return this.base.baseType === option.val;
        },
        selectedClass(option) {
            return this.isSelected(option) ? `border-${option.color}-600 shadow-lg` : 'border-transparent';
        },
        textColor(option, intensity) {
            return `text-${option.color}-${intensity}`;
        },
        optionName(option) {
            return `registration.start.${_.camelCase(option.val)}.title`;
        },
        selectBaseType(option) {
            this.$emit('update:base', {
                ...this.base,
                baseType: option.val,
            });
        },
        getImageSrc(option) {
            const camelOption = _.camelCase(option.val);
            return `/images/bases/${camelOption}.jpeg`;
        },
    },
    created() {
        this.options = options;
        this.maxBaseNameLength = maxBaseNameLength;
    },
    mounted() {
        this.$emit('mounted');
    },
};
</script>

<style scoped>

.o-registration-start {
    @apply
        min-h-full
    ;

    &__options {
        grid-template-columns: repeat(auto-fit, 280px);

        @apply
            gap-8
            grid
            justify-center

        ;

    }

    &__option {
        min-height: 220px;
        transition: 0.2s ease-in-out;

        @apply
            border
            border-solid
            p-3
            relative
            rounded-xl
        ;

        &:hover {
            @apply
                shadow-md
            ;
        }
    }

    &__circle {
        @apply
            bg-white
            h-10
            mb-4
            overflow-hidden
            rounded-full
            text-lg
            w-10
        ;
    }

    &__text {
        @apply
            font-medium
            text-center
            text-sm
        ;
    }

    &__input {
        max-width: 300px;
        width: 80%;
    }

    &__check {
        left: 6px;
        top: 6px;

        @apply
            absolute
            h-5
            text-white
            text-xs
            w-5
            z-over
        ;
    }
}

@media (min-width: 768px) {
    .o-registration-start {

        &__circle {
            @apply
                h-16
                text-3xl
                w-16
            ;
        }

        &__text {
            @apply
                text-base
            ;
        }
    }
}
</style>
