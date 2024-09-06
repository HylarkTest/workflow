<template>
    <div class="c-top-tip rounded-lg px-4 py-2">
        <div class="font-semibold mb-1">
            <i
                class="fa-regular fa-lightbulb-on"
            >
            </i>
            Tip
        </div>
        <div class="text-xssm leading-snug">
            <slot>
            </slot>
        </div>
    </div>
</template>

<script>

export default {
    name: 'TopTip',
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

/*.c-top-tip {

} */

</style>
