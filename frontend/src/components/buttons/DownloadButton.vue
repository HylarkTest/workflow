<template>
    <a
        :href="url"
        rel="noreferrer noopener"
        target="_blank"
        download
        class="o-download-button"
        @click.stop
    >
        <slot>
        </slot>
    </a>
</template>

<script>

export default {
    name: 'DownloadButton',
    components: {

    },
    mixins: [
    ],
    props: {
        url: {
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

/*.o-download-button {

} */

</style>
