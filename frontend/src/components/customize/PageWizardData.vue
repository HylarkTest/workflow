<template>
    <div class="o-page-wizard-data">
        <div class="max-w-xl">
            <div>
                <h2 class="o-creation-wizard__prompt mt-10">
                    {{ title }}
                </h2>

                <div class="flex justify-center">
                    <button
                        class="button--lg mr-4"
                        :class="buttonClass('PERSON')"
                        type="button"
                        @click="setType('PERSON')"
                    >
                        {{ peopleKey }}
                    </button>

                    <button
                        class="button--lg"
                        :class="buttonClass('ITEM')"
                        type="button"
                        @click="setType('ITEM')"
                    >
                        {{ itemsKey }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { basePages } from '@/core/mappings/templates/pages.js';

export default {
    name: 'PageWizardData',
    components: {
    },
    mixins: [
    ],
    props: {
        blueprintForm: {
            type: Object,
            required: true,
        },
        pageForm: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:blueprintForm',
        'update:pageForm',
    ],
    data() {
        return {
        };
    },
    computed: {
        pageType() {
            return this.pageForm.type;
        },
        isEntities() {
            return this.pageType === 'ENTITIES';
        },
        name() {
            return this.blueprintForm.name;
        },
        dataType() {
            return this.blueprintForm.type;
        },
        title() {
            if (this.isEntities) {
                return `What type of records are "${this.name}"?`;
            }
            return `What type of record is "${this.name}"?`;
        },
        peopleKey() {
            return this.isEntities ? 'People' : 'Person';
        },
        itemsKey() {
            return this.isEntities ? 'Items/Other' : 'Item/Other';
        },
        bestMatchTitle() {
            return {
                path: `customizations.pageWizard.blueprint.bestMatches.${this.titleKey}`,
                args: { blueprintName: this.name },
            };
        },
        titleKey() {
            if (this.dataType === 'PEOPLE') {
                return this.isEntities ? 'people' : 'person';
            }
            return this.isEntities ? 'items' : 'item';
        },
    },
    methods: {
        setType(type) {
            const pageInfo = type === 'PERSON' ? basePages.person : basePages.starterItem;
            this.$emit('update:blueprintForm', { valKey: 'type', newVal: type });
            this.$emit('update:blueprintForm', { valKey: 'fields', newVal: pageInfo.fields });
            this.$emit('update:blueprintForm', { valKey: 'features', newVal: pageInfo.features });
            this.$emit('update:pageForm', { valKey: 'newFields', newVal: pageInfo.newFields });
            this.$emit('update:pageForm', { valKey: 'singularName', newVal: pageInfo.singularName });
        },
        isSelected(type) {
            return this.blueprintForm.type === type;
        },
        buttonClass(type) {
            return this.isSelected(type)
                ? 'bg-secondary-600 text-cm-00'
                : 'button-secondary--light';
        },
    },
    created() {
    },
};
</script>

<style scoped>

/*.o-page-wizard-data {
}*/

</style>
