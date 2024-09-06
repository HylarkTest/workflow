<template>
    <div
        class="o-document-mini feature__item--mini"
        @click="$emit('selectDocument', document)"
    >
        <div
            class="flex items-center min-w-0"
        >
            <div
                class="h-5 w-5 bg-primary-200 text-primary-600 circle-center shadow mr-1"
            >
                <i
                    class="far text-xs"
                    :class="icon"
                >
                </i>
            </div>
            <div
                class="o-document-mini__name font-medium text-cm-700 text-xs u-ellipsis mr-1"
                :title="name"
            >
                {{ name }}
            </div>
            <!-- eslint-disable-next-line -->
            <DownloadButton
                :url="url"
            >
                <IconHover
                    class="c-icon-hover--sm"
                    icon="far fa-download"
                    iconColor="text-primary-600"
                >
                </IconHover>
            </DownloadButton>
        </div>
    </div>
</template>

<script>

import IconHover from '@/components/buttons/IconHover.vue';

import interactsWithDocumentItem from '@/vue-mixins/documents/interactsWithDocumentItem.js';

export default {
    name: 'DocumentMini',
    components: {
        IconHover,
    },
    mixins: [
        interactsWithDocumentItem,
    ],
    props: {

    },
    emits: [
        'selectDocument',
    ],
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

/*.o-document-mini {

}*/

</style>
