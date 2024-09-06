<template>
    <RegistrationBase
        class="o-registration-uses"
        :showNext="!!selectedUsesLength"
        :showPrevious="showPrevious"
        @previousStep="previousStep"
        @nextStep="$emit('nextStep', 'uses')"
    >
        <template
            #title
        >
            {{ $t(`registration.uses.${baseTypeFormatted}.title`) }}
        </template>

        <template
            #subtitle
        >
            {{ $t(`registration.uses.${baseTypeFormatted}.subtitle`, { group: baseName }) }}
        </template>

        <BaseUses
            :base="base"
            :preSelectedUses="preSelectedUses"
            :useCases="useCases"
            @toggleUse="toggleUse"
            @registerWithoutUse="registerWithoutUse"
        >
        </BaseUses>

        <template
            v-if="selectedUsesLength"
            #footer
        >
            <div class="flex-1 flex items-center flex-col">
                <div class="flex gap-3 mb-1">
                    <ButtonEl
                        v-for="use in selectedUses"
                        :key="use.val"
                        class="relative"
                        @click="toggleUse(use)"
                    >
                        <UseMini
                            :use="use"
                            :base="base"
                        >

                        </UseMini>
                        <ClearButton
                            positioningClass="-top-1 -right-1 absolute"
                        >
                        </ClearButton>
                    </ButtonEl>
                </div>

                <div class="flex flex-col items-center">
                    <div class="font-medium text-xs text-cm-500 flex">
                        Selected

                        <div class="ml-2">
                            <span
                                class="font-bold text-primary-600"
                            >
                                {{ selectedUsesLength }}
                            </span> / {{ maxChoices }}
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </RegistrationBase>
</template>

<script>

import BaseUses from '@/components/bases/BaseUses.vue';
import RegistrationBase from '@/components/access/RegistrationBase.vue';
import UseMini from '@/components/access/UseMini.vue';
import ClearButton from '@/components/buttons/ClearButton.vue';

import { arrRemoveId } from '@/core/utils.js';

export default {
    name: 'RegistrationUses',
    components: {
        RegistrationBase,
        BaseUses,
        UseMini,
        ClearButton,
    },
    mixins: [
    ],
    props: {
        useCases: {
            type: Array,
            required: true,
        },
        base: {
            type: Object,
            required: true,
        },
        preSelectedUses: {
            type: Array,
            default: () => [],
        },
    },
    emits: [
        'nextStep',
        'mounted',
        'update:base',
        'registerWithoutUse',
        'previousStep',
    ],
    data() {
        return {
            maxChoices: 3,
            showPrevious: true,
        };
    },
    computed: {
        selectedUsesLength() {
            return this.selectedUses.length;
        },
        selectedUses() {
            return _.uniqBy(this.useCases, 'val');
        },
        baseType() {
            return this.base.baseType;
        },
        baseTypeFormatted() {
            return _.camelCase(this.baseType);
        },
        baseName() {
            return this.base.name;
        },
    },
    methods: {
        previousStep() {
            this.showPrevious = false;
            this.$emit('previousStep', 'uses');
        },
        selectedUse(use) {
            return _.filter(this.useCases, { val: use.val });
        },
        toggleUse(use) {
            const selectedUseArr = this.selectedUse(use);
            const newBase = _.cloneDeep(this.base);

            if (selectedUseArr.length) {
                selectedUseArr.forEach((selectedUseObj) => {
                    const spaceIndex = selectedUseObj.spaceIndex;
                    newBase.spaces[spaceIndex].uses = arrRemoveId(this.base.spaces[spaceIndex].uses, use.val, 'val');
                });
                this.$emit('update:base', newBase);
            } else {
                const newUse = {
                    val: use.val,
                    refinement: use.refinement,
                };
                newBase.spaces[0].uses.push(newUse);
                this.$emit('update:base', newBase);
            }
        },
        registerWithoutUse() {
            this.$emit('registerWithoutUse');
        },
    },
    created() {

    },
    mounted() {
        this.$emit('mounted');
    },
};
</script>

<style scoped>

/*.o-registration-uses {

} */

</style>
