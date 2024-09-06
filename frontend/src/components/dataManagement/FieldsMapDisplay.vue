<template>
    <div class="c-fields-map-display flex gap-6 flex-wrap">
        <div
            class="w-1/3 min-w-[200px]"
        >
            <slot
                name="one"
            >
            </slot>

        </div>

        <div
            class="flex-1 grow-[3] min-w-[200px]"
        >
            <slot
                name="two"
            >
            </slot>
        </div>

        <div
            class="flex items-end flex-col grow w-[150px]"
        >
            <slot
                name="three"
            >
            </slot>
        </div>

    </div>
</template>

<script>

export default {
    name: 'FieldsMapDisplay',
    components: {

    },
    mixins: [
    ],
    props: {

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

/*.c-fields-map-display {

} */

</style>
