<template>
    <div class="c-trimmed-paragraph">
        {{ formatted }}
    </div>
</template>

<script>

export default {
    name: 'TrimmedParagraph',
    components: {

    },
    mixins: [
    ],
    props: {
        paragraph: {
            type: String,
            required: true,
        },
        charNumber: {
            type: Number,
            default: 120,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        length() {
            return this.paragraph?.length;
        },
        isTooLong() {
            return this.length > this.charNumber;
        },
        formatted() {
            return this.isTooLong ? _.truncate(this.paragraph, { length: 120 }) : this.paragraph;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-trimmed-paragraph {

} */

</style>
