<template>
    <div class="o-boolean-button-list">
        <div
            v-for="listItem in fullList"
            :key="listItem"
            class="flex my-3 mr-1"
        >
            <BooleanButtonHolder
                :modelValue="modelValue"
                :val="listItem"
                :buttonType="listType"
                :deactivated="isItemDeactivated(listItem)"
                :predicate="predicate"
                @click.stop.prevent="handleClick(listItem)"
            >
                <slot
                    name="listItem"
                    :listItem="listItem"
                >
                </slot>
            </BooleanButtonHolder>
        </div>

        <ConfirmModal
            v-if="isModalOpen && removeCache"
            @closeModal="cancelRemove"
            @cancelAction="cancelRemove"
            @proceedWithAction="proceedWithRemove"
        >
            <slot
                name="confirmationContent"
                :listItem="removeCache"
            >
            </slot>
        </ConfirmModal>
    </div>
</template>

<script>
import ConfirmModal from '@/components/assets/ConfirmModal.vue';
import BooleanButtonHolder from '@/components/inputs/BooleanButtonHolder.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import formWrapperChild from '@/vue-mixins/formWrapperChild.js';

export default {
    name: 'BooleanButtonList',
    components: {
        ConfirmModal,
        BooleanButtonHolder,
    },
    mixins: [
        interactsWithModal,
        formWrapperChild,
    ],
    props: {
        modelValue: {
            type: Array,
            required: true,
        },
        fullList: {
            type: Array,
            required: true,
        },
        predicate: {
            type: [String, Function, null],
            default: null,
        },
        listType: {
            type: String,
            default: 'check',
            validator: (type) => ['check', 'toggle'].includes(type),
        },
        confirmationList: {
            type: Array,
            default: () => [],
        },
        deactivatedItems: {
            type: Array,
            default: () => [],
        },
        emitSingleItem: Boolean,
        disableRemoveConfirmation: Boolean,
    },
    emits: [
        'update:modelValue',
    ],
    data() {
        return {
            removeCache: null,
        };
    },
    computed: {
    },
    methods: {
        isItemDeactivated(listItem) {
            return this.isModalOpen || this.deactivatedItems.includes(listItem);
        },
        isItemSelected(listItem) {
            return this.modelValue.includes(listItem);
        },
        isConfirmationRequired(listItem) {
            return this.confirmationList.includes(listItem);
        },
        handleClick(listItem) {
            // Calls handleAdd or handleRemove
            const action = this.isItemSelected(listItem) ? 'Remove' : 'Add';
            this[`handle${action}`](listItem);
        },
        handleAdd(listItem) {
            this.emitAdd(listItem);
        },
        handleRemove(listItem) {
            if (!this.disableRemoveConfirmation && this.isConfirmationRequired(listItem)) {
                this.removeCache = listItem;
                this.openModal();
            } else {
                this.emitDelete(listItem);
            }
        },
        cancelRemove() {
            this.finishRemove();
        },
        proceedWithRemove() {
            this.emitDelete(this.removeCache);
            this.finishRemove();
        },
        finishRemove() {
            this.removeCache = null;
            this.closeModal();
        },
        emitDelete(listItem) {
            const listWithoutDeletedItem = this.modelValue.filter((item) => !_.isEqual(item, listItem));
            const emitVal = this.emitSingleItem
                ? listItem
                : listWithoutDeletedItem;
            this.$emit('update:modelValue', emitVal);
        },
        emitAdd(listItem) {
            const listWithSelectedItem = [...this.modelValue, listItem];
            const emitVal = this.emitSingleItem
                ? listItem
                : listWithSelectedItem;
            this.$emit('update:modelValue', emitVal);
        },
    },
};
</script>

<style scoped>
/* .o-boolean-button-list {
} */
</style>
