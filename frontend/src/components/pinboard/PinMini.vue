<template>
    <div
        class="o-pin-mini feature__item--mini"
        @click="$emit('selectPin', pin)"
    >
        <div class="text-xssm font-semibold text-cm-800 mb-1">
            {{ name }}
        </div>

        <div>
            <img
                class="w-full w-16 rounded-xl"
                :src="image"
                :title="name"
            />
        </div>
    </div>
</template>

<script>

import interactsWithPinItem from '@/vue-mixins/pinboard/interactsWithPinItem.js';

export default {
    name: 'PinMini',
    components: {

    },
    mixins: [
        interactsWithPinItem,
    ],
    props: {

    },
    emits: [
        'selectPin',
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

/*.o-pin-mini {

} */

</style>
