<template>
    <div class="c-value-basic list-value bg-cm-100 flex items-center text-xs">
        <slot>
        </slot>

        <ClearButton
            v-if="!hideRemove"
            positioningClass="relative ml-2"
            @click="$emit('removeValue')"
        >
        </ClearButton>
    </div>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';

export default {
    name: 'ValueBasic',
    components: {
        ClearButton,
    },
    mixins: [
    ],
    props: {
        hideRemove: Boolean,
    },
    emits: [
        'removeValue',
    ],
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

/*.c-value-basic {

} */

</style>
