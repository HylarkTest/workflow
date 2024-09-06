<template>
    <div class="c-full-item">
        <div class="mr-8">
            <div
                v-for="(nav, index) in options"
                :key="index"
                class="py-1 px-4 rounded-xl"
                :class="{ 'text-cm-00 bg-primary-600 font-semibold': index === 0 }"
            >
                {{ nav.name || getDisplay(nav.type) }}
            </div>
        </div>
        <div class="flex-1">
            <h1 class="text-2xl mb-4 text-primary-900 font-bold">
                {{ info.name }}
            </h1>

            <div
                v-for="field in filteredFields"
                :key="field.id"
                class="mb-4"
            >
                <div class="text-sm text-cm-600 font-semibold">
                    {{ field.name }}
                </div>

                <component
                    :is="templateComponent(field)"
                    :dataValue="info[field.apiName]"
                    :container="{}"
                >
                </component>
            </div>
        </div>
    </div>
</template>

<script>

import { getTemplateComponent } from '@/core/display/displayTypes.js';

export default {
    name: 'FullItem',
    components: {

    },
    mixins: [
    ],
    props: {
        fullItem: {
            type: Object,
            required: true,
        },
        page: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {

        };
    },
    computed: {
        info() {
            return this.fullItem.data;
        },
        mapping() {
            return this.page.mapping;
        },
        fields() {
            return this.mapping.fields;
        },
        filteredFields() {
            return this.fields.filter((field) => {
                return field.type !== 'NAME';
            });
        },
        features() {
            return this.mapping.features;
        },
        relationships() {
            return this.mapping.relationships;
        },
        allAspects() {
            return _.concat(this.fields, this.features, this.relationships);
        },
        options() {
            const info = {
                name: 'Info',
            };
            return _.concat(info, this.features, this.relationships);
        },
    },
    methods: {
        templateComponent(field) {
            return getTemplateComponent(field.type);
        },
        getDisplay(type) {
            return this.$t(`links.${_.camelCase(type)}`);
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-full-item {
    @apply
        flex
    ;
}

</style>
