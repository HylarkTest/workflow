<template>
    <div class="c-link-checkbox">
        <CheckHolder
            v-bind="$attrs"
        >
            <LinkBasic
                class="mt-1"
                :link="link"
                :textPathStart="textPathStart"
            >
            </LinkBasic>

            <div
                v-if="link.sublinks"
                class="ml-6"
            >
                <LinkBasic
                    v-for="item in link.sublinks"
                    :key="item.id"
                    :link="item"
                    :textPathStart="textPathStart"
                    textColorClass="text-cm-500"
                    linkColorClass="text-primary-400"
                >
                </LinkBasic>

            </div>
        </CheckHolder>
    </div>
</template>

<script>

import LinkBasic from '@/components/assets/LinkBasic.vue';

export default {
    name: 'LinkCheckbox',
    components: {
        LinkBasic,
    },
    mixins: [
    ],
    props: {
        link: {
            type: Object,
            required: true,
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

    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-link-checkbox {

} */

</style>
