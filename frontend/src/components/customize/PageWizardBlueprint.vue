<template>
    <div class="o-page-wizard-blueprint">
        <div class="max-w-xl">
            <h2 class="o-creation-wizard__prompt pt-12">
                What blueprint do you want to use for "{{ pageForm.name }}"?
            </h2>
            <p
                class="o-creation-wizard__description text-center mb-8"
            >
                Record pages require blueprints specifying the data they display.
                You can select an existing blueprint for "{{ pageForm.name }}" or create a new blueprint.
            </p>

            <div class="flex flex-col items-center">
                <button
                    class="button button-secondary--light mb-6 relative"
                    type="button"
                    @click="setBlueprint('NEW')"
                >
                    Create a new blueprint

                    <i
                        v-if="isNewBlueprint"
                        class="fas fa-circle-check absolute -top-2 -right-2 text-xl"
                    >
                    </i>
                </button>

                <p
                    v-if="!blueprintsLength"
                    class="text-sm text-cm-700 italic"
                >
                    There are no existing blueprints on this base yet. Create a new one!
                </p>

                <template
                    v-if="blueprintsLength"
                >
                    <p
                        class="text-sm text-cm-700 mb-2"
                    >
                        Or use an existing blueprint
                    </p>

                    <div class="flex flex-wrap w-full">
                        <div
                            v-for="item in blueprints"
                            :key="item.id"
                            class="w-1/2 p-1"
                        >
                            <div class="button bg-cm-100 hover:shadow-lg">
                                <CheckHolder
                                    :modelValue="pageForm.mapping"
                                    :val="item.id"
                                    type="radio"
                                    @update:modelValue="setBlueprint"
                                >
                                    {{ item.name }}
                                </CheckHolder>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script>

import MAPPINGS from '@/graphql/mappings/queries/Mappings.gql';

export default {
    name: 'PageWizardBlueprint',
    components: {

    },
    mixins: [
    ],
    props: {
        pageForm: {
            type: Object,
            required: true,
        },
        space: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:pageForm',
    ],
    apollo: {
        mappings: {
            query: MAPPINGS,
            variables() {
                return { spaceId: this.space.id };
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        edges() {
            return this.mappings?.edges || [];
        },
        blueprints() {
            return this.edges.map((edge) => {
                return edge.node;
            });
        },
        blueprintsLength() {
            return this.blueprints.length;
        },
        isNewBlueprint() {
            return this.pageForm.mapping === 'NEW';
        },
    },
    methods: {
        setBlueprint(newVal) {
            let nextStep = 'NEXT';
            if (this.pageForm.type === 'ENTITY' && newVal !== 'NEW') {
                // If not new and entity, this is the last step. Let users
                // have a moment before finishing.
                nextStep = 'SAME';
            }
            this.$emit('update:pageForm', { valKey: 'mapping', newVal, nextStep });
        },
    },
    created() {

    },
};
</script>

<style scoped>

/*.o-page-wizard-blueprint {

} */

</style>
