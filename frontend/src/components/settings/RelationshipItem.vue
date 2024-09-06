<template>
    <div
        class="o-relationship-item"
        :class="{ unclickable: processingDelete }"
    >
        <div class="flex flex-1 items-center">
            <h5 class="font-semibold pr-2 w-1/4">
                {{ relationship.name }}
            </h5>
            <!-- <p class="w-1/4">
                {{ relationshipText }}
            </p> -->
            <div class="flex items-center">
                <p class="mr-2">
                    {{ current }}
                </p>
                <div>
                    <div class="o-relationship-item__direction">
                        <label class="o-relationship-item__label text-primary-600">
                            {{ relationshipObj.to.text }}
                        </label>
                        <div class="o-relationship-item__arrow o-relationship-item__arrow--top bg-primary-600"></div>
                    </div>
                    <div class="o-relationship-item__direction o-relationship-item__direction--bottom">
                        <div class="o-relationship-item__arrow o-relationship-item__arrow--bottom bg-primary-600"></div>
                        <label class="o-relationship-item__label text-primary-600">
                            {{ relationshipObj.from.text }}
                        </label>
                    </div>
                </div>
                <p class="ml-2">
                    {{ toName }}
                </p>
            </div>
        </div>
        <ActionButtons
            @edit="$emit('editRelationship', relationship)"
            @delete="deleteRelationship"
        >
        </ActionButtons>
    </div>
</template>

<script>

import ActionButtons from '@/components/buttons/ActionButtons.vue';

import relationshipTypes from '@/core/mappings/relationships/relationshipTypes.js';

export default {

    name: 'RelationshipItem',
    components: {
        ActionButtons,
    },
    mixins: [
    ],
    props: {
        relationship: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'deleteRelationship',
        'editRelationship',
    ],
    data() {
        return {
            processingDelete: false,
        };
    },
    computed: {
        relationshipType() {
            return this.relationship.type;
        },
        relationshipObj() {
            return _.find(relationshipTypes, ['type', this.relationshipType]);
        },
        // relationshipText() {
        //     return this.relationshipObj.text;
        // },
        current() {
            return this.fromType === 'ONE'
                ? this.mapping.singularName
                : this.mapping.name;
        },
        toName() {
            return this.toType === 'ONE'
                ? this.relationship.to.singularName
                : this.relationship.to.name;
        },
        fromType() {
            return this.relationshipType.split('_TO_')[0];
        },
        toType() {
            return this.relationshipType.split('_TO_')[1];
        },
    },
    methods: {
        deleteRelationship() {
            this.processingDelete = true;
            this.$emit('deleteRelationship', this.relationship);
        },
    },
    created() {
        this.relationshipTypes = relationshipTypes;
    },
};
</script>

<style scoped>
.o-relationship-item {
    @apply
        bg-cm-100
        flex
        items-center
        justify-between
        px-6
        py-3
        rounded-xl
        text-sm
    ;

    &__direction {
        @apply
            text-center
            w-16
        ;

        &--bottom {
            margin-top: 2px;
        }
    }

    &__label {
        @apply
            font-semibold
            text-xxs
            uppercase
        ;
    }

    &__arrow {
        height: 2px;

        @apply
            relative
            w-full
        ;

        &--top {
            &::before {
                right: 0;
                top: -3px;
                transform: rotate(45deg);
            }
        }

        &--bottom {
            &::before {
                bottom: -3px;
                left: 0;
                transform: rotate(45deg);
            }
        }

        &::before {
            background-color: inherit;
            content: "";
            height: 2px;

            @apply
                absolute
                w-2

            ;
        }
    }
}
</style>
