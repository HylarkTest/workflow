<template>
    <div
        class="o-link-mini feature__item--mini"
        @click="$emit('selectLink', link)"
    >
        <div class="flex items-start justify-between">
            <div class="text-xssm font-semibold text-cm-800 mb-1">
                {{ nameTruncated }}
            </div>
            <!-- eslint-disable-next-line -->
            <a
                class="button--xs button-primary shrink-0 ml-1"
                rel="noreferrer noopener"
                :href="url"
                target="_blank"
            >
                <i
                    class="far fa-up-right-from-square"
                >
                </i>
            </a>
        </div>

        <div class="text-xs text-cm-600">
            {{ descriptionTruncated }}
        </div>
    </div>
</template>

<script>

import interactsWithLinkItem from '@/vue-mixins/links/interactsWithLinkItem.js';

export default {
    name: 'LinkMini',
    components: {

    },
    mixins: [
        interactsWithLinkItem,
    ],
    props: {
    },
    emits: [
        'selectLink',
    ],
    data() {
        return {

        };
    },
    computed: {
        descriptionTruncated() {
            return _.truncate(this.link.description, { length: 50 });
        },
        nameTruncated() {
            return _.truncate(this.link.name, { length: 40 });
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.o-link-mini {

} */

</style>
