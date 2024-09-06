<template>
    <div
        class="c-displayer-toggle"
    >
        <ToggleButton
            :modelValue="!!modelFieldValue"
            @update:modelValue="saveValue($event)"
        >
        </ToggleButton>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithDisplayersSelfEdit from '@/vue-mixins/displayers/interactsWithDisplayersSelfEdit.js';

export default {
    name: 'DisplayerToggle',
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
            typeKey: 'TOGGLE',
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

/*.c-displayer-toggle {

} */

</style>
