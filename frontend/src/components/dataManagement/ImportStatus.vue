<template>
    <div class="o-import-status centered flex-col">
        <div class="centered bg-primary-200 rounded-full inline-flex h-24 w-24">
            <BirdImage
                class="h-16"
                :whichBird="whichBird"
            >
            </BirdImage>
        </div>
        <div
            v-if="isSuccess"
            class="my-4 text-center"
        >
            <p
                v-md-text="$t('imports.success.header', {
                    importName,
                })"
                class="mb-2 font-bold text-lg"
            >
            </p>
            <p
                v-md-text="$t('imports.success.view', {
                    mappingName,
                })"
            >
            </p>
        </div>

        <div
            v-if="isFailed"
            class="my-4 text-center"
        >
            <p
                v-md-text="$t('imports.failed.header', {
                    importName,
                })"
                class="mb-2 font-bold text-lg"
            >
            </p>
            <p
                v-t="'imports.failed.view'"
            >
            </p>
        </div>

        <button
            class="button button-primary"
            type="button"
            @click="backToTool"
        >
            {{ $t('imports.success.headBack') }}
        </button>
    </div>
</template>

<script>

export default {
    name: 'ImportStatus',
    components: {

    },
    mixins: [
    ],
    props: {
        importName: {
            type: String,
            required: true,
        },
        mappingName: {
            type: String,
            required: true,
        },
        status: {
            type: String,
            required: true,
        },
    },
    emits: [
        'goBack',
    ],
    data() {
        return {

        };
    },
    computed: {
        isSuccess() {
            return this.status === 'COMPLETED';
        },
        isFailed() {
            return this.status === 'FAILED';
        },
        whichBird() {
            if (this.isFailed) {
                return 'ContriteBird_72dpi.png';
            }
            return 'ThumbsUpBird_72dpi.png';
        },
    },
    methods: {
        backToTool() {
            this.$emit('goBack');
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-import-status {

} */

</style>
