<template>
    <div class="o-base-spaces">
        <div class="flex w-full flex-wrap items-start">
            <div
                class="o-base-spaces__spaces flex-wrap"
            >
                <div
                    v-for="space in spaces"
                    :key="space.id"
                    class="o-base-spaces__space w-full mb-4 md:mb-0 md:flex-1"
                >
                    <div
                        class="o-base-spaces__header"
                    >
                        <h4
                            class="font-semibold"
                            @click="changeName(space)"
                        >
                            {{ space.name }}
                        </h4>

                        <button
                            v-t="'common.rename'"
                            type="button"
                            class="o-base-spaces__rename button--sm transition-2eio"
                            @click="changeName(space)"
                        >
                        </button>
                    </div>

                    <Draggable
                        :modelValue="space.uses"
                        itemKey="val"
                        group="space"
                        class="o-base-spaces__container"
                        @update:modelValue="setUses($event, space)"
                    >
                        <template #item="{ element }">
                            <RegistrationSpacesUse
                                class="o-base-spaces__use"
                                :use="element"
                                :space="space"
                                :base="base"
                                @duplicateUse="duplicateUse"
                                @deleteUse="deleteUse"
                            >
                            </RegistrationSpacesUse>
                        </template>
                    </Draggable>
                </div>
            </div>

            <div
                class="w-full mt-4 lg:w-300p lg:mt-0 lg:ml-10"
            >
                <Explanation
                    :mainTitle="'helper.basic.spaces.title'"
                >
                    <p
                        v-for="explanation in [1, 2, 3, 4]"
                        :key="explanation"
                        v-t="'helper.basic.spaces.explanation' + explanation"
                        class="o-base-spaces__explanation"
                    >
                    </p>
                </Explanation>
            </div>
        </div>

        <Modal
            v-if="isModalOpen"
            containerClass="p-4 w-600p"
            @closeModal="closeModal"
        >
            <SpaceNameEdit
                :space="modalSpace"
                @closeModal="closeModal"
                @saved="replaceName"
            >
            </SpaceNameEdit>
        </Modal>
    </div>
</template>

<script>

import Draggable from 'vuedraggable';
import RegistrationSpacesUse from '@/components/access/RegistrationSpacesUse.vue';
import SpaceNameEdit from '@/components/access/SpaceNameEdit.vue';
import Explanation from '@/components/display/Explanation.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

export default {
    name: 'BaseSpaces',
    components: {
        RegistrationSpacesUse,
        SpaceNameEdit,
        Draggable,
        Explanation,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        base: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'update:base',
    ],
    data() {
        return {

        };
    },
    computed: {
        spaces() {
            return this.base.spaces;
        },
    },
    methods: {
        changeName(space) {
            this.isModalOpen = true;
            this.modalSpace = space;
        },
        replaceName(space) {
            const foundSpace = _.find(this.spaces, { id: space.id });
            foundSpace.name = space.name;
            this.modalSpace = null;
        },
        otherSpaces(space) {
            return this.spaces.filter((item) => {
                return item.id !== space.id;
            });
        },
        deleteUse({ use, space }) {
            const base = _.cloneDeep(this.base);
            const targetSpaceIndex = _.findIndex(base.spaces, { id: space.id });
            const useIndex = _.findIndex(base.spaces[targetSpaceIndex].uses, { val: use.val });
            if (~useIndex) {
                base.spaces[targetSpaceIndex].uses.splice(useIndex, 1);
                this.$emit('update:base', base);
            }
        },
        duplicateUse({ use, target }) {
            const base = _.cloneDeep(this.base);
            const targetSpaceIndex = _.findIndex(base.spaces, { id: target.id });
            const foundUse = _.find(base.spaces[targetSpaceIndex].uses, { val: use.val });
            if (!foundUse) {
                base.spaces[targetSpaceIndex].uses.push(use);
                this.$emit('update:base', base);
            }
        },
        setUses(newVal, space) {
            // Done like this because otherwise this fired twice, the second one invalidating the first
            const base = _.cloneDeep(this.base);
            const targetSpaceIndex = _.findIndex(base.spaces, { id: space.id });
            const targetSpaceUses = base.spaces[targetSpaceIndex]?.uses;
            const wasSomethingAdded = targetSpaceUses.length < newVal.length;
            if (~targetSpaceIndex && wasSomethingAdded) {
                // Unique in case something was dropped where there was already one
                const newUses = _.uniqBy(newVal, 'val');

                // Find which one is the new one
                const editedUse = _.differenceBy(newUses, targetSpaceUses, 'val')[0];

                // Add to the space that is new
                base.spaces[targetSpaceIndex].uses = newUses;

                // Get index of old space
                const oldSpaceIndex = 1 - targetSpaceIndex;

                // Remove the newly added use from the old space
                const oldUseIndex = _.findIndex(base.spaces[oldSpaceIndex].uses, { val: editedUse.val });
                base.spaces[oldSpaceIndex].uses.splice(oldUseIndex, 1);
                // Emit
                this.$emit('update:base', base);
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-base-spaces {
    &__explanation {
        @apply
            mb-3
        ;

        &:last-child {
            @apply
                mb-0
            ;
        }
    }

    &__spaces {
        @apply
            flex
            flex-1
            -mx-2
        ;
    }

    &__space {
        @apply
            flex
            flex-col
            px-2
        ;
    }

    &__header {
        @apply
            bg-gray-100
            flex
            items-center
            justify-between
            px-6
            py-3
            rounded-t-lg
        ;
    }

    &__rename {
        @apply
            bg-primary-600
            text-white
        ;

        &:hover {
            @apply
                bg-primary-500
            ;
        }
    }

    &__container {
        @apply
            border-2
            border-dashed
            border-gray-200
            border-t-0
            flex-1
            p-6
            rounded-b-lg
            text-sm
        ;
    }

    &__use {
        @apply
            cursor-move
            my-2
        ;

        &:first-child {
            @apply
                mt-0
            ;
        }

        &:last-child {
            @apply
                mb-0
            ;
        }

    }
}

</style>
