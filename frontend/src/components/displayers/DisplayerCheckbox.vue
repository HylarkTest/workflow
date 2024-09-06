<template>
    <div
        class="c-displayer-checkbox flex"
    >
        <div>
            <CheckButton
                :modelValue="!!modelFieldValue"
                @update:modelValue="saveValue($event)"
            >
            </CheckButton>
        </div>
    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithDisplayersSelfEdit from '@/vue-mixins/displayers/interactsWithDisplayersSelfEdit.js';

export default {
    name: 'DisplayerCheckbox',
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
            typeKey: 'CHECKBOX',
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

/*.c-displayer-checkbox {

} */

</style>
