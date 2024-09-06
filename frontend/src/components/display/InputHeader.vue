<template>
    <div class="c-input-header flex items-center">
        <div class="pr-4 w-20">
            <slot></slot>
        </div>
        <div class="text-sm">
            <h5 class="font-semibold">
                <slot name="header"></slot>
            </h5>
            <p
                v-if="$slots.paragraph"
                class="leading-snug mt-2"
            >
                <slot name="paragraph"></slot>
            </p>
        </div>
    </div>
</template>

<script>
export default {

    name: 'InputHeader',
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
<!--
<style scoped>
.c-input-header {

}
</style> -->
