<template>
    <div
        v-blur="closePopup"
        class="c-extras-button"
        @click.stop
        @keydown.stop
        @keydown.tab="closePopup"
    >
        <component
            ref="button"
            :is="cantModify ? 'div' : 'button'"
            class="c-extras-button__button centered"
            :class="buttonClasses"
            :type="cantModify ? '' : 'button'"
            @click="togglePopup"
            @keydown.down="seeDropdown"
        >
            <i
                class="fas fa-ellipsis-vertical c-extras-button__icon"
                :class="{ 'text-secondary-600': dropdownVisible }"
            >
            </i>
        </component>

        <OptionsPopup
            v-if="dropdownVisible"
            v-bind="$attrs"
            :activator="$refs.button"
            :options="options"
            @selectOption="selectOption"
        >
        </OptionsPopup>

        <EntityConfirmDelete
            v-if="isModalOpen"
            :itemName="itemName"
            @cancelDelete="closeModal"
            @deleteItem="confirmAction('DELETE')"
        >
        </EntityConfirmDelete>

        <PageCreateModal
            v-if="isPageModalOpen"
            propPageType="ENTITY"
            :modalHeader="pageCreatePathsObj.header"
            :modalDescription="pageCreatePathsObj.description"
            :propPageName="propPageName"
            :propMapping="mapping"
            :behaviors="behaviors"
            :item="item"
            @closeModal="closePageModal"
        >
        </PageCreateModal>

        <DuplicateItemModal
            v-if="isDuplicateModalOpen"
            :item="item"
            :contextItemType="contextItemType"
            :duplicateItemMethod="duplicateItemMethod"
            @closeModal="closeDuplicateModal"
        >
        </DuplicateItemModal>
    </div>
</template>

<script>

import EntityConfirmDelete from '@/components/assets/DeleteConfirmationModal.vue';
import OptionsPopup from '@/components/popups/OptionsPopup.vue';
import PageCreateModal from '@/components/customize/PageCreateModal.vue';
import DuplicateItemModal from '@/components/assets/DuplicateItemModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';

const buttonStyles = {
    MINI: {
        baseClass: 'c-extras-button__mini',
        canModifyClass: 'text-cm-400 hover:text-secondary-600',
        cantModifyClass: 'text-cm-400',
    },
    FULL: {
        baseClass: 'c-extras-button__full',
        canModifyClass: 'bg-cm-00 hover:bg-cm-100',
        cantModifyClass: '',
    },
};

export default {
    name: 'ExtrasButton',
    components: {
        EntityConfirmDelete,
        OptionsPopup,
        PageCreateModal,
        DuplicateItemModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        forceState: {
            type: [Boolean, null],
            default: () => (null),
        },
        cantModify: Boolean,
        options: {
            type: Array,
            required: true,
        },
        showConfirmDelete: Boolean,
        buttonStyleName: {
            type: String,
            default: 'MINI',
            validator(val) {
                return Object.keys(buttonStyles).includes(val);
            },
        },
        buttonSize: {
            type: String,
            default: 'sm',
            validator(val) {
                return ['sm', 'xs'].includes(val);
            },
        },
        pageCreatePathsObj: {
            type: Object,
            default() {
                return {
                    header: 'Create a new page',
                    description: '',
                };
            },
        },
        propPageName: {
            type: String,
            default: '',
        },
        mapping: {
            type: [Object, null],
            default: null,
        },
        behaviors: {
            type: [Array, null],
            default: null,
        },
        item: {
            type: [Object, null],
            default: null,
        },
        contextItemType: {
            type: String,
            default: '',
        },
        duplicateItemMethod: {
            type: Function,
            default: () => this.options.includes('DUPLICATE'),
        },
    },
    emits: [
        'selectOption',
    ],
    data() {
        return {
            dropdownVisible: false,
            isPageModalOpen: false,
            isDuplicateModalOpen: false,
        };
    },
    computed: {
        buttonClasses() {
            return [
                this.baseButtonClass,
                this.buttonAdditionalClass,
                this.buttonSizeClass,
            ];
        },
        baseButtonClass() {
            return buttonStyles[this.buttonStyleName].baseClass;
        },
        buttonAdditionalClass() {
            return this.cantModify
                ? buttonStyles[this.buttonStyleName].cantModifyClass
                : buttonStyles[this.buttonStyleName].canModifyClass;
        },
        buttonSizeClass() {
            return `c-extras-button__${this.camelButtonStyle}--${this.buttonSize}`;
        },
        camelButtonStyle() {
            return _.camelCase(this.buttonStyleName);
        },
        itemName() {
            return this.item?.name || '';
        },
    },
    methods: {
        togglePopup() {
            if (!this.cantModify) {
                this.dropdownVisible = !this.dropdownVisible;
            }
        },
        closePopup() {
            this.dropdownVisible = false;
        },
        arrowKeyElement() {
            return this.$refs.button;
        },
        seeDropdown() {
            this.dropdownVisible = true;
        },
        selectOption(event) {
            this.closePopup();
            if (event === 'DELETE' && this.showConfirmDelete) {
                this.openModal();
            } else if (event === 'MAKE_ENTITY_PAGE') {
                this.isPageModalOpen = true;
            } else if (event === 'DUPLICATE') {
                this.isDuplicateModalOpen = true;
            } else {
                this.emitOption(event);
            }
        },
        emitOption(event) {
            this.$emit('selectOption', event);
        },
        confirmAction(event) {
            this.emitOption(event);
            this.closeModal();
        },
        closePageModal() {
            this.isPageModalOpen = false;
        },
        closeDuplicateModal() {
            this.isDuplicateModalOpen = false;
        },
    },
    watch: {
        forceState(state) {
            if (_.isBoolean(state)) {
                this.dropdownVisible = state;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.c-extras-button {
    @apply
        relative
    ;

    &__button {
        transition: 0.2s ease-in-out;
    }

    &__mini {
        width: 10px;

        @apply
            bg-cm-200
            leading-none
            py-1
            rounded
        ;
    }

    &__full {
        @apply
            rounded-md
            text-cm-300
        ;

        &--sm {
            @apply
                h-8
                text-xl
                w-8
            ;
        }

        &--xs {
            @apply
                h-6
                text-lg
                w-6
            ;
        }
    }

    /*&__icon {
    }*/
}

</style>
