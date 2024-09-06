<template>
    <div class="o-mapping-edit-relationships">
        <div
            class="flex justify-end mb-4"
        >
            <button
                v-t="'relationships.add'"
                class="button button-primary--light"
                type="button"
                @click="openRelationship({})"
            >
            </button>
        </div>

        <div
            v-if="relationshipsLength"
        >
            <div
                v-for="relationship in relationships"
                :key="relationship.id"
                class="mb-2"
            >
                <RelationshipItem
                    :relationship="relationship"
                    :mapping="mapping"
                    @editRelationship="openRelationship"
                    @deleteRelationship="deleteRelationship"
                >
                </RelationshipItem>
            </div>
        </div>

        <NoContentText
            v-else
            class="mt-8"
            customIcon="fa-draw-circle"
            :customHeaderPath="customHeaderPath"
            :customMessagePath="customMessagePath"
        >
        </NoContentText>

        <Modal
            v-if="isModalOpen"
            containerClass="w-600p"
            :header="whichHeader"
            @closeModal="closeRelationship"
        >
            <RelationshipForm
                :relationship="selectedRelationship"
                :mapping="mapping"
                @closeModal="closeRelationship"
            >
            </RelationshipForm>
        </Modal>

    </div>
</template>

<script>

import RelationshipItem from '@/components/settings/RelationshipItem.vue';
import RelationshipForm from '@/components/settings/RelationshipForm.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

import { deleteMappingRelationship } from '@/core/repositories/mappingRepository.js';

export default {
    name: 'MappingEditRelationships',
    components: {
        RelationshipItem,
        RelationshipForm,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            selectedRelationship: null,
        };
    },
    computed: {
        relationships() {
            return this.mapping.relationships;
        },
        whichHeader() {
            return this.hasSelectedRelationship ? 'Edit relationship' : 'Create a relationship';
        },
        hasSelectedRelationship() {
            return !_.isEmpty(this.selectedRelationship);
        },
        relationshipsLength() {
            return this.relationships.length;
        },
        blueprintName() {
            return this.mapping.name;
        },
        customHeaderPath() {
            return [
                'relationships.noContent.header',
                { blueprintName: this.blueprintName },
            ];
        },
        customMessagePath() {
            return [
                'relationships.noContent.message',
                { blueprintName: this.blueprintName },
            ];
        },
    },
    methods: {
        openRelationship(relationship) {
            this.selectedRelationship = relationship;
            this.openModal();
        },
        deleteRelationship(relationship) {
            return deleteMappingRelationship(this.mapping, relationship);
        },
        closeRelationship() {
            this.selectedRelationship = null;
            this.closeModal();
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-mapping-edit-relationships {

} */

</style>
