<template>
    <div class="c-yes-and-no">
        <h3 class="font-bold text-lg text-center mb-4">
            <slot
                name="header"
            >
            </slot>
        </h3>

        <div class="text-smbase">
            <div
                class="mb-3"
            >
                <p class="font-semibold mb-1 text-emerald-600">
                    <slot
                        name="yesHeader"
                    >
                    </slot>
                </p>
                <div
                    v-for="yes in yeses"
                    :key="yes"
                    class="flex items-baseline my-1"
                >
                    <i
                        class="fas fa-check text-emerald-600 mr-2"
                    >
                    </i>

                    {{ $t(`explanations.yesNo.${pathKey}.yes.${yes}`) }}
                </div>
            </div>

            <div>
                <p class="font-semibold mb-1 text-peach-600">
                    <slot
                        name="noHeader"
                    >
                    </slot>
                </p>
                <div
                    v-for="no in nos"
                    :key="no"
                    class="flex items-baseline my-1"
                >
                    <i
                        class="fas fa-xmark text-peach-600 mr-2"
                    >
                    </i>

                    {{ $t(`explanations.yesNo.${pathKey}.no.${no}`) }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'YesAndNo',
    components: {

    },
    mixins: [
    ],
    props: {
        yeses: {
            type: Array,
            required: true,
        },
        nos: {
            type: Array,
            required: true,
        },
        pathKey: {
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

/*.c-yes-and-no {

} */

</style>
