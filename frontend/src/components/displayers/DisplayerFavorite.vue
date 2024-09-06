<template>
    <component
        :is="isModifiable ? 'button' : 'div'"
        class="c-displayer-favorite bg-cm-00"
        :class="[containerClass, containerValueClass]"
        type="button"
        @click="toggleFavorite"
    >
        <i
            class="fa-heart"
            :class="[iconValueClass, iconClass]"
        >
        </i>
    </component>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

const iconCombos = {
    1: '',
    2: '',
    3: 'text-cm-00',
};

const containerCombos = {
    1: 'h-8 w-8 circle-center border border-dashed text-sm',
    2: '',
    3: 'h-8 w-8 circle-center',
};

export default {
    name: 'DisplayerFavorite',
    components: {
    },
    mixins: [
        interactsWithDisplayers,
    ],
    props: {
    },
    data() {
        return {
            typeKey: 'FAVORITES',
        };
    },
    computed: {
        iconValueClass() {
            if (this.combo === 3) {
                return this.dataValue ? 'fas text-cm-00' : 'far text-cm-00';
            }
            return this.dataValue ? 'fas text-primary-600' : 'far text-cm-400';
        },
        containerValueClass() {
            if (this.combo === 3) {
                return this.dataValue ? 'bg-primary-600' : 'bg-cm-200';
            }
            if (this.combo === 1) {
                return this.dataValue ? 'border-primary-600' : 'border-cm-300';
            }
            return '';
        },
        iconClass() {
            return iconCombos[this.combo];
        },
        containerClass() {
            return containerCombos[this.combo];
        },
    },
    methods: {
        toggleFavorite() {
            if (this.isModifiable) {
                this.updateDisplayerValue('isFavorite', !this.dataValue);
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-favorite {

} */

</style>
