<template>
    <button
        class="c-displayer-icon-toggle"
        :class="[containerClass, containerValueClass]"
        type="button"
        @click="toggleIcon"
    >
        <i
            class="far"
            :class="[icon, iconClass, iconValueClass]"
        >
        </i>
    </button>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithDisplayersSelfEdit from '@/vue-mixins/displayers/interactsWithDisplayersSelfEdit.js';

const iconCombos = {
    1: '',
    2: 'text-cm-00',
    3: '',
    4: '',
};

const containerCombos = {
    1: 'h-8 w-8 rounded-md centered',
    2: 'h-8 w-8 circle-center',
    3: '',
    4: 'h-8 w-8 circle-center border border-dashed text-sm',
};

export default {
    name: 'DisplayerIconToggle',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithDisplayersSelfEdit,
    ],
    props: {

    },
    emits: [
        'saveField',
        'update:dataValue',
    ],
    data() {
        return {
            typeKey: 'ICON_TOGGLE',
        };
    },
    computed: {
        iconValueClass() {
            if (this.combo === 1) {
                return this.modelFieldValue ? 'fas text-primary-600' : 'far text-cm-400';
            }
            if (this.combo === 2) {
                return this.modelFieldValue ? 'fas text-cm-00' : 'far text-cm-00';
            }
            return this.modelFieldValue ? 'fas text-primary-600' : 'far text-cm-400';
        },
        containerValueClass() {
            if (this.combo === 1) {
                return this.modelFieldValue ? 'bg-primary-200' : 'bg-cm-200';
            }
            if (this.combo === 2) {
                return this.modelFieldValue ? 'bg-primary-600' : 'bg-cm-200';
            }
            if (this.combo === 4) {
                return this.modelFieldValue ? 'border-primary-600' : 'border-cm-300';
            }
            return '';
        },
        icon() {
            return this.dataInfo.info?.meta?.symbol;
        },
        iconClass() {
            return iconCombos[this.combo];
        },
        containerClass() {
            return containerCombos[this.combo];
        },
    },
    methods: {
        toggleIcon() {
            this.saveValue(!this.modelFieldValue);
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-icon-toggle {

} */

</style>
