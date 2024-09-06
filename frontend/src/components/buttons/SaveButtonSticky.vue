<template>
    <div
        class="o-save-button-sticky flex justify-end mt-2 sticky pointer-events-none"
        :class="bottomClass"
    >
        <slot>
        </slot>

        <SaveButton
            v-if="!buttonsLength"
            v-bind="$attrs"
            @save="$emit('save', 'close')"
        >
        </SaveButton>

        <SaveOptions
            v-else
            v-bind="$attrs"
            :buttons="buttons"
            @submit="$emit('save', $event)"
        >
        </SaveOptions>
    </div>
</template>

<script>

import SaveOptions from '@/components/buttons/SaveOptions.vue';

export default {
    name: 'SaveButtonSticky',
    components: {
        SaveOptions,
    },
    mixins: [
    ],
    props: {
        bottomClass: {
            type: String,
            default: 'bottom-2',
        },
        buttons: {
            type: Array,
            default: () => {
                return [];
            },
        },
    },
    emits: [
        'save',
    ],
    data() {
        return {

        };
    },
    computed: {
        buttonsLength() {
            return this.buttons.length;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>
