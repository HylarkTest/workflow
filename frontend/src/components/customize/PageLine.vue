<template>
    <div class="o-page-line">
        <TemplateTags
            class="self-end w-24"
            :dataValue="{ name: pageTypeName }"
            :container="{ style: pageTypeSmall }"
        >
        </TemplateTags>

        <div
            class="circle-center h-5 w-5 bg-primary-200 text-sm mr-4"
        >
            <i
                class="fa-duotone"
                :class="page.symbol"
                :style="duotoneColors(accentColor)"
            >
            </i>
        </div>

        <div class="text-sm font-medium text-cm-700">
            {{ page.name }}
        </div>
    </div>
</template>

<script>

import interactsWithPageItem from '@/vue-mixins/customizations/interactsWithPageItem.js';

export default {
    name: 'PageLine',
    components: {

    },
    mixins: [
        interactsWithPageItem,
    ],
    props: {

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

.o-page-line {
    @apply
        flex
        items-center
    ;
}

</style>
