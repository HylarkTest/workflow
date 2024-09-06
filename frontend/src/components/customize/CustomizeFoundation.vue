<template>
    <div class="o-customize-foundation flex flex-col min-w-0">
        <div class="flex justify-between mb-4 items-center flex-wrap">
            <h2 class="font-semibold text-3xl text-primary-900">
                <slot name="header">
                </slot>
            </h2>

            <slot
                name="besideHeader"
            >
            </slot>
        </div>
        <div class="o-customize-foundation__content">
            <slot>
            </slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'CustomizeFoundation',
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

/*.o-customize-foundation {
}*/

</style>
