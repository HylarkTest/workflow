<template>
    <component
        :is="recordComponent"
        class="c-email-display text-xssm"
    >
        <div
            v-if="record"
            class="mr-1 font-semibold p-1 rounded"
            :class="recordColorClasses"
        >
            {{ initials }}
        </div>

        <div class="break-all">
            {{ email }}
        </div>

        <slot>
        </slot>

        <ClearButton
            v-if="showClear"
            positioningClass="ml-2"
            @click="$emit('removeEmail', email)"
        >
        </ClearButton>
    </component>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';

import { getInitials } from '@/core/utils.js';
import { getColorName } from '@/core/display/nameColors.js';

export default {
    name: 'RecordDisplay',
    components: {
        ClearButton,
    },
    mixins: [
    ],
    props: {
        record: {
            type: [Object, null],
            default: null,
        },
        email: {
            type: String,
            required: true,
        },
        recordComponent: {
            type: String,
            default: 'div',
        },
        showClear: Boolean,
    },
    emits: [
        'removeEmail',
    ],
    data() {
        return {

        };
    },
    computed: {
        name() {
            return this.record.name;
        },
        initials() {
            return getInitials(this.name);
        },
        recordColorClasses() {
            const colorString = getColorName(this.name[0], this.name[1]);
            return `bg-${colorString}-200 text-${colorString}-600`;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-email-display {
    @apply
        bg-cm-100
        flex
        items-center
        px-2
        py-0.5
        rounded-md
    ;
}

</style>
