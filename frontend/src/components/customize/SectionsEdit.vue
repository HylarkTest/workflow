<template>
    <div class="o-sections-edit p-4">
        <div v-if="!hasSections">
            <p class="mb-4">
                You've not created any sections yet.
            </p>

            <p>To create sections, add some fields with sections!</p>
        </div>

        <FormWrapper
            :form="form"
        >
            <div
                v-for="section in sections"
                :key="section.id"
                class="my-2 flex justify-between"
            >
                <div class="relative ml-2 flex-1">
                    <p
                        class="text-smbase font-medium"
                        @click.stop="renameSection(section)"
                    >
                        {{ section.name }}
                    </p>

                    <InputSubtle
                        v-if="form.id === section.id"
                        :ref="setRef(section)"
                        v-blur="saveSection"
                        displayClasses="absolute -top-1 -left-1 w-full"
                        formField="name"
                        :alwaysHighlighted="true"
                        @click.stop
                        @keydown.enter.stop="saveSection"
                        @keydown.space.stop
                    >
                    </InputSubtle>
                </div>

                <ActionButtons
                    @edit="renameSection(section)"
                    @delete="deleteSection(section)"
                >
                </ActionButtons>
            </div>
        </FormWrapper>
    </div>
</template>

<script>

import { deleteMappingSection, updateMappingSection } from '@/core/repositories/mappingRepository.js';

export default {
    name: 'SectionsEdit',
    components: {

    },
    mixins: [
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: this.$apolloForm(() => {
                return {
                    name: '',
                    id: '',
                };
            }).constantData({ mappingId: this.mapping.id }),
            nameRefs: {},
        };
    },
    computed: {
        hasSections() {
            return this.sections?.length;
        },
        sections() {
            return this.mapping.sections;
        },
    },
    methods: {
        async renameSection(section) {
            this.form.id = section.id;
            this.form.name = section.name;
            await this.$nextTick();
            const input = this.nameRefs[section.id];
            input.select();
        },
        deleteSection(section) {
            deleteMappingSection(this.mapping, section);
        },
        async saveSection() {
            await updateMappingSection(this.form);
            this.form.reset();
        },
        setRef(section) {
            return (el) => {
                this.nameRefs[section.id] = el;
            };
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-sections-edit {

} */

</style>
