<template>
    <div class="c-link-basic flex items-baseline">

        <i
            class="fal mr-2 fa-fw"
            :class="[link.symbol, linkColorClass]"
        >
        </i>

        <div
            class="text-sm"
            :class="textColorClass"
        >
            {{ name }}
        </div>
    </div>
</template>

<script>

export default {
    name: 'LinkBasic',
    components: {

    },
    mixins: [
    ],
    props: {
        link: {
            type: Object,
            required: true,
        },
        textColorClass: {
            type: String,
            default: 'text-cm-600',
        },
        linkColorClass: {
            type: String,
            default: 'text-primary-500',
        },
        textPathStart: {
            type: String,
            default: 'links',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        name() {
            return this.link.name || this.$t(`${this.textPathStart}.${_.camelCase(this.link.val)}`);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-link-basic {

} */

</style>
