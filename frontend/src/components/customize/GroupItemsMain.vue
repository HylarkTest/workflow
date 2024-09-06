<template>
    <div class="c-group-items-main">
        <div class="mb-4">
            <GroupForge
                ref="groupForge"
                class="c-group-items-main__forge"
                :group="group"
                :groupType="groupType"
                :hideColor="hideColor"
                :bgClass="bgClass"
                :processing="processingNew"
                v-bind="$attrs"
                @submitGroupItem="addItem"
            >
            </GroupForge>
        </div>

        <Draggable
            class="flex flex-wrap"
            :modelValue="group.items"
            :group="group.id"
            itemKey="id"
            @update="moveItem"
        >
            <template #item="{ element, index }">

                <component
                    :is="itemDisplayComponent"
                    class="flex items-center my-2 mx-2 cursor-move"
                    :item="element"
                    :index="index"
                    :bgClass="bgClass"
                    :isModifiable="true"
                    :markerCount="group.markerCount"
                    @editMarker="editItem"
                    @deleteMarker="confirmItemDelete"
                >
                </component>
            </template>
        </Draggable>

        <Modal
            v-if="isEditItemOpen"
            containerClass="p-6 w-600p"
            @closeModal="closeEditItem"
        >
            <GroupItemEdit
                :groupType="groupType"
                :item="selectedItem"
                :hideColor="hideColor"
                :processing="processing"
                @saveChanges="saveEditChanges"
            >
            </GroupItemEdit>
        </Modal>

        <GroupItemDelete
            v-if="isDeleteItemOpen"
            :groupType="groupType"
            :item="selectedItem"
            :processing="processingDelete"
            @deleteItem="deleteItem(selectedItem)"
            @closeModal="closeDeleteItem"
        >
        </GroupItemDelete>
    </div>
</template>

<script>

import Draggable from 'vuedraggable';
import GroupForge from './GroupForge.vue';
import GroupItemEdit from './GroupItemEdit.vue';
import GroupItemDelete from './GroupItemDelete.vue';
import CategoryDisplay from './CategoryDisplay.vue';
import TagDisplay from './TagDisplay.vue';
import StatusDisplay from './StatusDisplay.vue';
import PhaseDisplay from './PhaseDisplay.vue';
import StageDisplay from './StageDisplay.vue';

export default {
    name: 'GroupItemsMain',
    components: {
        Draggable,
        GroupForge,
        GroupItemEdit,
        GroupItemDelete,
        CategoryDisplay,
        TagDisplay,
        StageDisplay,
        PhaseDisplay,
        StatusDisplay,
    },
    mixins: [
    ],
    props: {
        group: {
            type: Object,
            required: true,
        },
        groupType: {
            type: String,
            required: true,
        },
        hideColor: Boolean,
        itemDisplayComponent: {
            type: String,
            required: true,
        },
        bgClass: {
            type: String,
            default: 'bg-cm-00',
        },
        repository: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'deleteItem',
        'moveMarker',
        'saveEditChanges',
    ],
    data() {
        return {
            isEditItemOpen: false,
            isDeleteItemOpen: false,
            selectedItem: null,
            processing: false,
            processingNew: false,
            processingDelete: false,
        };
    },
    computed: {
        markersList() {
            return this.group.items.map((item) => {
                return {
                    name: item.name,
                    color: item.color,
                };
            });
        },
    },
    methods: {
        editItem(item) {
            this.selectedItem = item;
            this.isEditItemOpen = true;
        },
        closeEditItem() {
            this.clearSelected();
            this.isEditItemOpen = false;
        },
        confirmItemDelete(item) {
            this.selectedItem = item;
            this.isDeleteItemOpen = true;
        },
        closeDeleteItem() {
            this.clearSelected();
            this.isDeleteItemOpen = false;
        },
        clearSelected() {
            this.selectedItem = null;
        },
        moveItem(event) {
            const marker = this.group.items[event.oldIndex];
            const from = event.oldIndex;
            const to = event.newIndex;

            const previousMarker = to === 0
                ? null
                : this.group.items[to < from ? to - 1 : to];
            return this.repository.moveGroupItem(marker, previousMarker);
        },
        async saveEditChanges(form) {
            this.processing = true;
            try {
                await this.repository.updateGroupItem(form);
                this.clearSelected();
                this.closeEditItem();
                this.$saveFeedback();
            } finally {
                this.processing = false;
            }
        },
        async deleteItem(item) {
            this.processingDelete = true;
            try {
                await this.repository.deleteGroupItem(item);
                this.clearSelected();
                this.closeDeleteItem();
            } finally {
                this.processingDelete = false;
            }
        },
        async addItem(form) {
            this.processingNew = true;
            try {
                await this.repository.createGroupItem(form);
            } finally {
                this.processingNew = false;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-group-items-main {
    &__forge {
        @apply
            pb-8
            pt-4
            px-8
            rounded-xl
        ;
    }
}

</style>
