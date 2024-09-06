<template>
    <div class="c-set-options relative">
        <transition name="t-fade">
            <AlertTooltip
                v-if="error"
            >
                {{ error }}
            </AlertTooltip>
        </transition>

        <button
            class="button-rounded--sm mb-2 button-primary--light"
            :class="{ unclickable: hitMax }"
            :disabled="hitMax"
            type="button"
            @click="addOption"
        >
            Add
        </button>
        <div
            v-for="(option, key, index) in options"
            :key="option.key"
            class="mb-1"
        >
            <InputBox
                :modelValue="option"
                v-bind="$attrs"
                size="sm"
                :placeholder="'Option ' + (index + 1)"
                @update:modelValue="updateOptions(key, $event)"
            >
                <template
                    v-if="showDelete"
                    #afterInput
                >
                    <ClearButton
                        positioningClass="relative"
                        @click="removeOption(key)"
                    >
                    </ClearButton>
                </template>
            </InputBox>
        </div>
    </div>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';
import AlertTooltip from '@/components/popups/AlertTooltip.vue';

export default {
    name: 'SetOptions',
    components: {
        ClearButton,
        AlertTooltip,
    },
    mixins: [
    ],
    props: {
        options: {
            type: Object,
            required: true,
        },
        max: {
            type: Number,
            default: 6,
        },
        error: {
            type: String,
            default: '',
        },
    },
    emits: [
        'update:options',
    ],
    data() {
        return {

        };
    },
    computed: {
        hitMax() {
            return this.optionsKeysLength === 6;
        },
        optionsKeysLength() {
            return _.keys(this.options).length;
        },
        showDelete() {
            return this.optionsKeysLength > 1;
        },
    },
    methods: {
        addOption() {
            const clone = _.clone(this.options);
            const random = new Date().getTime();
            clone[random] = '';
            this.emitObj(clone);
        },
        updateOptions(key, option) {
            const clone = _.clone(this.options);
            clone[key] = option;
            this.emitObj(clone);
        },
        removeOption(key) {
            const clone = _.clone(this.options);
            delete clone[key];
            this.emitObj(clone);
        },
        emitObj(obj) {
            this.$emit('update:options', obj);
        },
    },
    created() {
        if (_.isEmpty(this.options)) {
            this.addOption();
        }
    },
};
</script>

<style scoped>

/*.c-set-options {

} */

</style>
