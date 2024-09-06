<template>
    <div
        class="o-field-item-template"
        :class="{ 'o-field-item-template--sub': isSub }"
    >
        <div class="flex flex-1 items-center mr-8">
            <div
                class="o-field-item-template__name w-28"
                :class="isSub ? 'pl-6' : 'font-semibold'"
            >
                {{ field.name }}
            </div>
            <div
                class="o-field-item-template__info mr-8 w-28 flex items-center"
                :class="{ 'pl-6': isSub }"
            >
                {{ fieldTypeName }}

                <button
                    v-if="hasSubFields"
                    class="circle-center h-4 w-4 button-primary ml-2"
                    type="button"
                    @click="$emit('toggleSubFields')"
                >
                    <i
                        class="far"
                        :class="showSubFields ? 'fa-angle-up' : 'fa-angle-down'"
                    >
                    </i>
                </button>
            </div>
            <div class="o-field-item-template__info mr-8 w-28 u-ellipsis">
                {{ field.section ? sectionName : '' }}
            </div>
            <!-- <div class="o-field-item-template__info mr-8 text-right w-28">
                {{ $dayjs(field.updatedAt).format('ll') }}
            </div>
            <div class="o-field-item-template__info text-right w-28">
                {{ $dayjs(field.createdAt).format('ll') }}
            </div> -->
        </div>

        <div
            class="o-field-item-template__list o-field-item-template__info"
            title="List"
        >
            <i
                class="o-field-item-template__bars fa-fw far"
                :class="{ 'fa-bars': isList }"
            ></i>
        </div>
        <div class="o-field-item-template__icons">
            <ActionButtons
                v-if="!isSub"
                :hideDelete="isSystemName"
                @edit="$emit('editField', field)"
                @delete="$emit('deleteField', field)"
            >
            </ActionButtons>
        </div>
    </div>
</template>

<script>

import ActionButtons from '@/components/buttons/ActionButtons.vue';

export default {
    name: 'FieldItemTemplate',
    components: {
        ActionButtons,
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
            default: () => { return []; },
        },
        isSub: Boolean,
        showSubFields: Boolean,
    },
    emits: [
        'editField',
        'deleteField',
        'toggleSubFields',
    ],
    data() {
        return {

        };
    },
    computed: {
        isSystemName() {
            return this.fieldVal === 'SYSTEM_NAME';
        },
        isList() {
            return !!this.field.options?.list;
        },
        fieldVal() {
            return this.field.val;
        },
        fieldTypeName() {
            const camelType = _.camelCase(this.fieldVal);
            return this.$t(`fields.types.${camelType}`);
        },
        sectionName() {
            const section = _(this.mappingSections).find(['id', this.field.section]);
            return section && section.name;
        },
        subFields() {
            return this.field.options?.fields;
        },
        hasSubFields() {
            return this.subFields?.length;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>
.o-field-item-template {
    @apply
        flex
        text-cm-800
        text-sm
        w-full
    ;

    &__name {
        @apply
            flex-1
            mr-8
            text-cm-800
        ;
    }

    &__list {
        @apply
            mr-2
            p-1
            text-primary-600
        ;
    }

    &__bars {
        width: 20px;
    }

    &--sub {
        .o-field-item-template__info {
            @apply
                text-cm-500
            ;
        }
    }

    &__icons {
        width: 64px;
    }
}
</style>
