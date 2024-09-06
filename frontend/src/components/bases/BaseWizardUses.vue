<template>
    <div
        class="o-base-wizard-uses"
    >
        <h1 class="o-creation-wizard__header">
            What will you use this base for?
        </h1>

        <BaseUses
            :base="base"
            :useCases="useCases"
            v-bind="$attrs"
            @toggleUse="toggleUse"
        >
        </BaseUses>
    </div>
</template>

<script>

import BaseUses from './BaseUses.vue';

import { arrRemoveId } from '@/core/utils.js';

export default {
    name: 'BaseWizardUses',
    components: {
        BaseUses,
    },
    mixins: [
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
        useCases: {
            type: Array,
            required: true,
        },
    },
    emits: [
        'update:base',
    ],
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
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
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-base-wizard-uses {

} */

</style>
