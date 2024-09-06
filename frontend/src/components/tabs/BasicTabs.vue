<template>
    <div
        class="c-basic-tabs flex"
        :class="{ 'flex-col': direction === 'column' }"
    >
        <div
            v-for="(tab, index) in tabs"
            :key="tab.value"
            name="tab"
            :class="tabClass"
        >
            <slot
                name="item"
                :index="index"
                :tab="tab"
            ></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BasicTabs',
    components: {

    },
    mixins: [
    ],
    props: {
        tabs: {
            type: [Array, Object],
            required: true,
        },
        direction: {
            type: String,
            default: 'row',
        },
        tabClass: {
            type: String,
            default: '',
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
