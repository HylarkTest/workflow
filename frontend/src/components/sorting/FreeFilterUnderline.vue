<template>
    <!-- <FreeFilter
        class="o-free-filter-underline"
        :modelValue="modelValue"
        @update="$emit('update:modelValue')"
    >
    </FreeFilter> -->
    <FreeFilter
        class="o-free-filter-underline"
        :class="borderColor"
        v-bind="$attrs"
        @setFocus="setFocus"
    >
    </FreeFilter>
</template>

<script>

import FreeFilter from '@/components/sorting/FreeFilter.vue';

export default {
    name: 'FreeFilterUnderline',
    components: {
        FreeFilter,
    },
    mixins: [
    ],
    props: {
    },
    emits: [
    ],
    data() {
        return {
            inFocus: false,
        };
    },
    computed: {
        borderColor() {
            return this.inFocus ? 'border-primary-600' : 'border-cm-400';
        },
    },
    methods: {
        setFocus(focus) {
            this.inFocus = focus;
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*
.o-free-filter-underline {

}
*/

</style>
