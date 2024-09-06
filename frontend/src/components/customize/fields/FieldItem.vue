<template>
    <div
        class="o-field-item"
        :class="{ unclickable: disabled }"
    >
        <FieldItemTemplate
            :field="field"
            :mappingSections="mappingSections"
            :showSubFields="showSubFields"
            v-bind="$attrs"
            @toggleSubFields="toggleSubFields"
            @deleteField="deleteField"
        >
        </FieldItemTemplate>

        <div v-if="hasSubFields && showSubFields">
            <div
                v-for="sub in subFields"
                :key="sub.id"
            >
                <FieldItemTemplate
                    :field="sub"
                    :isSub="true"
                >
                </FieldItemTemplate>
            </div>
        </div>
    </div>
</template>

<script>

import FieldItemTemplate from '@/components/customize/fields/FieldItemTemplate.vue';

export default {
    name: 'FieldItem',
    components: {
        FieldItemTemplate,
    },
    mixins: [
    ],
    props: {
        field: {
            type: Object,
            required: true,
        },
        mappingSections: {
            type: Array,
            default: () => ([]),
        },
        disabled: Boolean,
    },
    emits: [
        'deleteField',
    ],
    data() {
        return {
            showSubFields: false,
        };
    },
    computed: {
        subFields() {
            return this.field.options?.fields;
        },
        hasSubFields() {
            return this.subFields?.length;
        },
    },
    methods: {
        toggleSubFields() {
            this.showSubFields = !this.showSubFields;
        },
        deleteField(field) {
            this.$emit('deleteField', field);
        },
    },
    created() {

    },
};
</script>

<style scoped>
.o-field-item {
    @apply
        w-full
    ;
}
</style>
