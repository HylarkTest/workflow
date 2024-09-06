<template>
    <component
        :is="dropdownComponent"
        v-model="blueprint"
        class="c-blueprint-picker"
        :groups="blueprintFormatted"
        :popupProps="{ maxHeightProp: '7.5rem' }"
        :displayRule="blueprintOptionsDisplay"
        :groupDisplayRule="blueprintOptionsDisplay"
        :options="blueprintOptions"
        placeholder="Select a blueprint"
        v-bind="$attrs"
    >
        <template
            #selected="{
                display,
                original,
            }"
        >
            <div class="flex items-center">
                {{ display }}

                <SpaceNameLabel
                    v-if="original && original.space"
                    :spaceName="original.space.name"
                    size="sm"
                    class="ml-2"
                >
                </SpaceNameLabel>
            </div>
        </template>
    </component>
</template>

<script>

import SpaceNameLabel from '@/components/display/SpaceNameLabel.vue';

import MAPPINGS from '@/graphql/mappings/queries/Mappings.gql';

export default {
    name: 'BlueprintPicker',
    components: {
        SpaceNameLabel,
    },
    mixins: [
    ],
    props: {
        dropdownComponent: {
            type: String,
            default: 'DropdownBox',
        },
        spaceId: {
            type: [String, null],
            required: true,
        },
        modelToId: Boolean,
        modelValue: {
            type: [String, Object],
            default: null,
        },
    },
    emits: ['update:modelValue'],
    apollo: {
        mappings: {
            query: MAPPINGS,
            variables() {
                return { spaceId: this.spaceId };
            },
        },
    },
    data() {
        return {

        };
    },
    computed: {
        blueprintOptions() {
            return _.map(this.mappings?.edges, 'node');
        },
        blueprint: {
            get() {
                if (this.modelToId && this.modelValue) {
                    return _.find(this.blueprintOptions, ['id', this.modelValue]);
                }
                return this.modelValue;
            },
            set(blueprint) {
                this.$emit('update:modelValue', this.modelToId && blueprint ? blueprint.id : blueprint);
            },
        },
        groupedBlueprints() {
            return _.groupBy(this.blueprintOptions, 'space.id');
        },
        blueprintFormatted() {
            return _(this.groupedBlueprints).map((blueprints) => {
                return {
                    group: blueprints[0].space,
                    options: blueprints,
                };
            }).value();
        },
    },
    methods: {
        nameWithSpace(blueprint) {
            return blueprint.name;
        },
    },
    created() {
        this.blueprintOptionsDisplay = (blueprint) => blueprint.name;
    },
};
</script>

<style scoped>

/*.c-field-picker {

} */

</style>
