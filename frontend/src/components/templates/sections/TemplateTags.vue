<template>
    <div
        class="c-template-tags"
    >
        <div
            v-if="hasValue"
            class="c-template-tags__tags"
            :class="placementClass"
        >
            <TemplateTag
                v-for="(tag, index) in valueArr"
                :key="tag.id || index"
                class="m-1"
                :showRemove="showRemove"
                :tag="tag"
                :tagStyle="style"
            >
            </TemplateTag>
        </div>

        <div v-else>
            <!-- - -->
        </div>
    </div>
</template>

<script>

import TemplateTag from './TemplateTag.vue';
import providesTemplatesProps from '@/vue-mixins/providesTemplatesProps.js';

const placement = {
    center: 'justify-center',
    end: 'justify-end',
};

export default {
    name: 'TemplateTags',
    components: {
        TemplateTag,
    },
    mixins: [
        providesTemplatesProps,
    ],
    props: {
        showRemove: Boolean,
    },
    data() {
        return {

        };
    },
    computed: {
        isValueArr() {
            return _.isArray(this.dataValue);
        },
        hasValue() {
            return this.dataValue;
        },
        valueArr() {
            return this.isValueArr ? this.dataValue : [this.dataValue];
        },
        style() {
            return this.container.style || {};
        },
        placementClass() {
            return this.container.placement ? placement[this.container.placement] : '';
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<style scoped>
.c-template-tags {
    &__tags {
        @apply
            flex
            flex-wrap
            items-start
        ;
    }
}
</style>
