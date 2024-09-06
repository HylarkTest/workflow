<template>
    <div class="o-tag-item">
        <div class="flex items-center flex-1">
            <h5 class="font-semibold w-1/4">
                {{ tag.name }}
            </h5>
            <div class="mr-4 w-1/5">
                <div
                    class="inline rounded-full tag-sm"
                    :class="tagStyle"
                >
                    {{ tagType }}
                </div>
            </div>
            <div
                class="w-1/5"
            >
                <div
                    v-if="tag.relationship"
                    class="flex items-center text-cm-600 w-1/5"
                >
                    <i class="fal fa-draw-circle mr-2 text-primary-600"></i>
                    {{ tag.relationship.name }}
                </div>
            </div>
            <p class="flex items-center text-cm-600 ">
                <i class="fal fa-tag mr-2 text-primary-600"></i>
                {{ tag.group.name }}
            </p>
        </div>
        <div class="flex">
            <IconHover
                @click="$emit('editTag', tag)"
            >
            </IconHover>
            <IconHover
                icon="far fa-trash-alt"
                @click="$emit('deleteTag', tag)"
            >
            </IconHover>
        </div>
    </div>
</template>

<script>

import IconHover from '@/components/buttons/IconHover.vue';

import providesTagColors from '@/vue-mixins/settings/providesTagColors.js';

export default {

    name: 'TagItem',
    components: {
        IconHover,
    },
    mixins: [
        providesTagColors,
    ],
    props: {
        tag: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'editTag',
        'deleteTag',
    ],
    data() {
        return {

        };
    },
    computed: {
        tagType() {
            return _.capitalize(this.tag.type);
        },
        tagStyle() {
            return this.tagClasses(this.tag.type);
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.o-tag-item {
    @apply
        flex
        items-center
        justify-between
        text-sm
    ;
}
</style>
