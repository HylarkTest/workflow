<template>
    <div class="c-input-sized relative">
        <span class="invisible">
            <!-- Text that the input should size towards should be in here -->
            <slot>
            </slot>
        </span>
        <InputField
            ref="input"
            class="absolute w-full top-0 left-0"
            v-bind="$attrs"
        >
        </InputField>
    </div>
</template>

<script>

import InputField from '@/components/inputs/InputField.vue';

export default {
    name: 'InputSized',
    components: {
        InputField,
    },
    mixins: [
    ],
    props: {
    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-input-sized {

} */

</style>
