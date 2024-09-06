<template>
    <div class="c-duotone-icon-text flex items-center">
        <div class="circle-center h-7 w-7 mr-2 bg-primary-100">
            <i
                class="fa-duotone"
                :class="icon"
                :style="duotoneColors(accentColor)"
            >
            </i>
        </div>
        {{ name }}
    </div>
</template>

<script>

import providesColors from '@/vue-mixins/style/providesColors.js';

export default {
    name: 'DuotoneIconText',
    components: {

    },
    mixins: [
        providesColors,
    ],
    props: {
        name: {
            type: String,
            required: true,
        },
        icon: {
            type: String,
            required: true,
        },
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

/*.c-duotone-icon-text {

} */

</style>
