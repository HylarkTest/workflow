<template>
    <SettingsHeaderLine class="o-field-section">
        <template
            #header
        >
            <div class="flex">
                <p class="mr-2">
                    Section
                </p>
                <NewAndPicker
                    :modelValue="section"
                    :options="sectionsCollection"
                    displayRule="name"
                    @addNew="addNew"
                    @update:modelValue="emitSection"
                >
                    <template
                        #selected="{ selectedEvents }"
                    >
                        <button
                            ref="button"
                            class="tag-sm button-primary--light rounded-full"
                            type="button"
                            @click.stop="selectedEvents.click"
                        >
                            {{ section ? 'Edit' : 'Add' }}
                        </button>
                    </template>
                </NewAndPicker>
            </div>
        </template>

        <template
            #description
        >
            Want to organize or group your fields? Give your fields a section.
        </template>

        <div
            v-if="section"
            class="bg-cm-100 py-1 px-3 rounded-lg inline-flex mt-1"
        >
            {{ section.name }}

            <ClearButton
                positioningClass="relative ml-2 mt-1"
                @click="emitSection(null)"
            >
            </ClearButton>
        </div>

    </SettingsHeaderLine>
</template>

<script>

import ClearButton from '@/components/buttons/ClearButton.vue';
import NewAndPicker from '@/components/pickers/NewAndPicker.vue';
import { createMappingSection } from '@/core/repositories/mappingRepository.js';

export default {
    name: 'FieldSection',
    components: {
        NewAndPicker,
        ClearButton,
    },
    mixins: [
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
        section: {
            type: [Object, null],
            default: null,
        },
    },
    emits: [
        'update:section',
    ],
    data() {
        return {
        };
    },
    computed: {
        sectionsCollection() {
            return this.mapping.sections;
        },
    },
    methods: {
        async addNew(query) {
            const { mapping } = await createMappingSection(this.mapping, query);
            const newSection = _.last(mapping.sections);
            this.emitSection(newSection);
        },
        emitSection(section) {
            this.$emit('update:section', section);
        },
    },
    created() {
    },
};
</script>

<style scoped>
.o-field-section {
    @apply
        text-sm
    ;

    &__input {
        @apply
            bg-cm-100
            flex
            items-center
            justify-between
            px-2
            py-3
        ;
    }

    &__box {
        @apply
            block
            flex
            justify-between
            px-2
            py-3
            text-cm-800
            w-full
        ;

        &:hover {
            @apply
                bg-cm-100
            ;
        }
    }

    &__scroll {
        max-height: 154px;
        overflow-y: auto;
    }
}
</style>
