<template>
    <ButtonEl
        class="c-list-line"
        :class="buttonClass"
        @click="selectList"
        @keydown.enter="selectList"
        @keydown.space="selectList"
        @dragleave="unsetHighlight"
    >
        <FormWrapper
            class="flex-1 min-w-0"
            :class="draggableChildClass"
            :form="form"
            dontscroll
        >
            <div class="c-list-line__main">
                <div
                    v-if="!hideColorSquare"
                    class="mr-2 mt-1"
                >
                    <ColorSquare
                        v-model:currentColor="form.color"
                        :isModifiable="true"
                        @update:currentColor="saveList"
                    >
                    </ColorSquare>
                </div>

                <div
                    v-if="showTotal && list.total"
                    class="rounded-full bg-secondary-600 h-2 w-2 mr-2 mt-1.5"
                >
                </div>

                <i
                    v-if="list.icon"
                    class="fal fa-fw mr-2 mt-1 text-cm-400"
                    :class="list.icon"
                >

                </i>

                <div class="relative flex-1 min-w-0">

                    <div class="u-text">
                        {{ trimmedName }}
                    </div>

                    <InputSubtle
                        v-if="editMode"
                        ref="nameInput"
                        v-blur="saveListName"
                        displayClasses="absolute top-0 -left-1 w-full"
                        formField="name"
                        :alwaysHighlighted="true"
                        @click.stop
                        @keydown.enter.stop="saveListName"
                        @keydown.space.stop
                    >
                    </InputSubtle>
                </div>
            </div>
        </FormWrapper>
        <div
            class="flex items-center"
            :class="draggableChildClass"
        >
            <div
                v-if="isShared"
                class="text-gray-400 text-xs"
                title="Shared"
            >
                <i
                    class="fa-fw fal fa-user-group"
                ></i>
            </div>

            <div
                v-if="list.count"
                class="c-list-line__count centered ml-1.5"
                :class="countColorClasses"
            >
                {{ list.count }}
            </div>

            <ExtrasButton
                v-if="showExtras"
                class="ml-1.5"
                :options="options"
                :forceState="showOptionsPopup"
                alignRight
                nudgeDownProp="0.375rem"
                nudgeRightProp="0.375rem"
                @selectOption="selectOption"
            >
            </ExtrasButton>
        </div>
    </ButtonEl>
</template>

<script>

import ExtrasButton from '@/components/buttons/ExtrasButton.vue';
import ColorSquare from '@/components/assets/ColorSquare.vue';

export default {
    name: 'ListLine',
    components: {
        ExtrasButton,
        ColorSquare,
    },
    mixins: [
    ],
    props: {
        list: {
            type: Object,
            required: true,
        },
        source: {
            type: Object,
            required: true,
        },
        displayedList: {
            type: Object,
            default: null,
        },
        highlightedList: {
            type: Object,
            default: null,
        },
        hideColorSquare: Boolean,
        hideAllLineOptions: Boolean,
        countColorClasses: {
            type: String,
            default: 'bg-cm-200 text-cm-600',
        },
        processing: Boolean,
        showTotal: Boolean,
    },
    emits: [
        'selectAction',
        'saveList',
        'deleteList',
        'selectList',
        'update:highlight',
        'removePending',
    ],
    data() {
        const form = this.$apolloForm(() => {
            const data = {
                name: this.list.name,
                color: this.list.color || '#038c5e',
            };
            if (!this.list.new) {
                data.id = this.list.id;
            } else if (this.source.__typename === 'Space') {
                data.spaceId = this.source.id;
            }
            if (this.source.provider) {
                data.sourceId = this.source.id;
            }
            return data;
        });

        return {
            showOptionsPopup: null,
            editMode: false,
            form,
            lastSentName: null,
        };
    },
    computed: {
        isHighlighted() {
            return this.highlightedList === this.list;
        },
        draggableChildClass() {
            return { 'pointer-events-none': this.isHighlighted };
        },
        isDisplayedList() {
            return this.list?.is(this.displayedList);
        },
        trimmedName() {
            return _.truncate(this.list?.name, { length: 50 });
        },
        buttonClass() {
            return {
                'c-list-line--selected': this.isDisplayedList,
                'c-list-line--hover': !this.editMode,
                'c-list-line--highlight': this.isHighlighted,
                unclickable: this.processing,
                'pointer-events-none': this.newList,
            };
        },
        options() {
            const options = [];
            if (this.list?.canBeRenamed()) {
                options.push('RENAME');
            }
            if (this.list?.canBeDeleted()) {
                options.push('DELETE');
            } else if (this.isShared) {
                options.push({
                    namePath: 'todos.leave',
                    icon: 'fal fa-person-to-door',
                    color: 'peach',
                    val: 'DELETE',
                });
            }
            return options;
        },
        isSystemList() {
            return this.list.isDefault;
        },
        isReadOnly() {
            return this.list.isReadOnly;
        },
        isShared() {
            return this.list.isShared;
        },
        isReadOnlyAndNotShared() {
            return this.isReadOnly && !this.isShared;
        },
        showExtras() {
            return !this.hideAllLineOptions
                && !this.isSystemList
                && !this.isReadOnlyAndNotShared
                && !this.newList;
        },
        newList() {
            return this.list.new || !this.list.id;
        },
        hasErrors() {
            return this.form.errors().any();
        },
    },
    methods: {
        // Let the parent component be in charge of which list is highlighted.
        setHighlight() {
            if (!this.isDisplayedList) {
                this.$emit('update:highlight', true);
            }
        },
        unsetHighlight() {
            if (this.isHighlighted) {
                this.$emit('update:highlight', false);
            }
        },
        selectList() {
            this.$emit('selectList', { list: this.list, source: this.source });
        },
        saveListName() {
            if (this.editMode) {
                if (this.hasErrors && this.form.name === this.lastSentName) {
                    this.form.reset();
                    this.stopEdit();
                    this.lastSentName = null;

                    if (!this.form.id) {
                        this.$emit('removePending', this.list, this.source);
                    }
                } else if (this.form.name) {
                    this.saveList();
                    this.lastSentName = this.form.name;
                    this.stopEdit();
                } else {
                    this.form.name = this.list.name;
                    this.focusAndSelect();
                }
            }
        },
        saveList() {
            this.$emit('saveList', { form: this.form, list: this.list, source: this.source });
        },
        selectOption(action) {
            this.closePopup();
            const camelAction = _.camelCase(action);
            this[`${camelAction}Option`]();
        },
        stopEdit() {
            this.editMode = false;
        },
        renameOption() {
            this.editMode = true;
            this.focusAndSelect();
        },
        async focusAndSelect() {
            await this.$nextTick();
            const input = this.$refs.nameInput;
            input.focus();
            input.select();
        },
        deleteOption() {
            this.$emit('deleteList', { list: this.list, source: this.source });
        },
        closePopup() {
            this.showOptionsPopup = false;

            setTimeout(() => {
                this.showOptionsPopup = null;
            }, 200);
        },
    },
    watch: {
        list() {
            this.form.reset();
        },
        hasErrors(val) {
            if (val) {
                this.editMode = true;
            }
        },
    },
    mounted() {
        if (this.list.new) {
            this.renameOption();
        }
        // Expose the highlight method on the component element as that is
        // what is exposed to Sortable when a list item is being dragged over.
        // If there is a better way to do this then I am all ears.
        // I'd like to do it with a sortable plugin
        // (https://github.com/SortableJS/Sortable/blob/master/plugins/README.md)
        // but at first try I can't see how to get it to turn off the highlight
        // when the element is moved away.
        // It's done manually here with the `dragleave` event.
        this.$el._onItemEnter = this.setHighlight;
    },
};
</script>

<style scoped>

.c-list-line {
    margin:  1px 0;

    @apply
        flex
        items-start
        justify-between
        px-2
        py-1.5
        rounded-lg
        text-sm
    ;

    &--selected {
        @apply
            bg-gradient-to-r
            font-medium
            from-secondary-200
            to-transparent
        ;
    }

    &--highlight {
        @apply
            border
            border-primary-600
            border-solid
        ;
    }

    &--hover:not(.c-list-line--selected):hover {
        @apply
            bg-cm-100
        ;
    }

    &__main {
        @apply
            flex
            flex-1
            mr-2
        ;
    }

    &__count {
        height:  19px;
        min-width: 19px;

        @apply
            font-semibold
            p-1
            rounded-md
            text-xs
        ;
    }
}

</style>
