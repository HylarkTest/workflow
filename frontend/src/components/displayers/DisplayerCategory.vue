<template>
    <div
        class="c-displayer-category"
        :class="displayClasses"
    >
        <div
            v-if="dataValueArr?.length"
            class="list-value-parent"
        >
            <ValueBasic
                v-for="val in dataValueArr"
                :key="val.id"
                :hideRemove="true"
            >
                {{ val.name }}
            </ValueBasic>
        </div>

        <button
            v-else
            class="button-rounded--sm button-primary--light u-ellipsis inline-flex items-baseline"
            type="button"
        >
            <i class="fal fa-circle-plus mr-1">
            </i>
            {{ categoryName }}
        </button>
    </div>
</template>

<script>

import interactsWithMultiDisplayers from '@/vue-mixins/displayers/interactsWithMultiDisplayers.js';
import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';

export default {
    name: 'DisplayerCategory',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithMultiDisplayers,
    ],
    props: {

    },
    data() {
        return {
            typeKey: 'CATEGORY',
        };
    },
    computed: {
        categoryName() {
            return this.dataInfo.name;
        },
        fieldValue() {
            return this.displayFieldValue;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

.c-displayer-category {
    /*&__add {
        transition: 0.2s ease-in-out;
        @apply
            bg-cm-200
            items-center
            px-2
            py-0.5
            rounded-full
            text-cm-600
            text-xs
        ;

        &:hover {
            @apply
                text-primary-600
            ;
        }
    }*/
}

</style>
