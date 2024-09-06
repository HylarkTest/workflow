<template>
    <div class="o-display-edit-order">
        <SettingsHeaderLine>
            <template #header>
                Select the order and headers for your data
            </template>

            <div class="bg-cm-100 rounded-xl p-4">
                <div class="flex justify-end mb-2">
                    <button
                        class="button-primary button--sm"
                        type="button"
                        @click="addNewSection"
                    >
                        Add a new section
                    </button>
                </div>
                <Draggable
                    v-model="form.itemDisplay"
                    class="bg-cm-00 rounded-xl p-6"
                    itemKey="id"
                    group="sections"
                    :forceFallback="true"
                >
                    <template #item="{ element }">
                        <div class="o-display-edit-order__section relative">
                            <ClearButton
                                v-if="element.fields.length === 0"
                                class="bg-cm-00"
                                size="lg"
                                @click="removeSection(element)"
                            >
                            </ClearButton>

                            <button
                                class="absolute -top-2 -left-2 button-rounded--sm button-primary--light"
                                type="button"
                                @click="startEdit(element)"
                            >
                                {{ element.header ? 'Edit header' : 'Add header' }}
                            </button>

                            <div
                                class="relative"
                            >
                                <h2
                                    v-if="element.header || (editingId === element.id)"
                                    class="o-display-edit-order__header"
                                    :class="{ 'opacity-0': editingId === element.id }"
                                    @click="startEdit(element)"
                                >
                                    {{ element.header }}
                                </h2>

                                <InputSubtle
                                    v-if="editingId === element.id"
                                    ref="nameInput"
                                    v-blur="saveName"
                                    displayClasses="absolute top-0 -left-1 w-full"
                                    :modelValue="element.header"
                                    :alwaysHighlighted="true"
                                    maxlength="50"
                                    @update:modelValue="setHeader(element, $event)"
                                    @click.stop
                                    @keydown.enter.stop="saveName"
                                    @keydown.space.stop
                                >
                                </InputSubtle>
                            </div>

                            <Draggable
                                :list="element.fields"
                                itemKey="id"
                                group="fields"
                                :forceFallback="true"
                            >
                                <template #item="{ element: field }">
                                    <div
                                        :key="field.formattedId"
                                        class="o-display-edit-order__field"
                                    >
                                        <span class="text-xssm text-cm-500">
                                            {{ field.name }}
                                        </span>
                                    </div>
                                </template>
                            </draggable>
                        </div>
                    </template>
                </Draggable>
            </div>
        </SettingsHeaderLine>
        <SaveButtonSticky
            :disabled="processing"
            @click.stop="saveOrder"
        >
        </SaveButtonSticky>
    </div>
</template>

<script>

import Draggable from 'vuedraggable';
import ClearButton from '@/components/buttons/ClearButton.vue';

import { getFullFieldsInfoDefault } from '@/core/display/fullViewFunctions.js';
import { updatePageDesign } from '@/core/repositories/pageRepository.js';
import { itemDisplayFlatAndFormatted } from '@/core/display/theStandardizer.js';

export default {
    name: 'DisplayEditOrder',
    components: {
        Draggable,
        ClearButton,
    },
    mixins: [
    ],
    props: {
        page: {
            type: Object,
            required: true,
        },
        mapping: {
            type: Object,
            required: true,
        },
    },
    data() {
        const itemDisplay = this.page.design?.itemDisplay;
        return {
            form: this.$apolloForm({
                itemDisplay: itemDisplay
                    ? itemDisplayFlatAndFormatted(itemDisplay, this.mapping.fields)
                    : getFullFieldsInfoDefault(this.mapping),
            }, {
                reportValidation: true,
            }),
            editingId: null,
            processing: false,
        };
    },
    computed: {

    },
    methods: {
        setHeader(element, val) {
            const index = _.findIndex(this.form.itemDisplay, ['id', element.id]);
            this.form.itemDisplay[index].header = val;
        },
        startEdit(element) {
            this.editingId = element.id;
            // if (!element.header) {
            //     this.setHeader(element, 'Add a header');
            // }
            this.focusAndSelect();
        },
        saveName() {
            this.editingId = null;
        },
        async focusAndSelect() {
            await this.$nextTick();
            const input = this.$refs.nameInput;
            input.focus();
            input.select();
        },
        addNewSection() {
            const newElement = {
                header: 'Add a header!',
                id: _.random(1, 10000, 5),
                fields: [],
            };
            this.form.itemDisplay.unshift(newElement);
            this.startEdit(newElement);
        },
        removeSection(element) {
            const index = _.findIndex(this.form.itemDisplay, ['id', element.id]);
            this.form.itemDisplay.splice(index, 1);
        },
        async saveOrder() {
            this.processing = true;
            try {
                await updatePageDesign(this.form, this.page);
                this.$saveFeedback();
            } finally {
                this.processing = false;
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-display-edit-order {
    &__section {
        @apply
            border
            border-dashed
            border-primary-300
            cursor-move
            mb-6
            p-4
            rounded-lg
        ;

        &:last-child {
            @apply
                mb-0
            ;
        }
    }

    &__header {
        @apply
            border-b
            border-primary-400
            border-solid
            cursor-pointer
            font-bold
            mb-4
            pb-2
            text-xl
        ;
    }

    &__field {
        @apply
            border
            border-cm-300
            border-dashed
            mt-2
            px-2
            py-1
            rounded-lg
        ;

        &:first-child {
            @apply
                mt-0
            ;
        }

        /*&:last-child {
            @apply
                mb-0
            ;
        }*/
    }
}

</style>
