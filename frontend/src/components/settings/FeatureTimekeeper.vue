<template>
    <div class="o-feature-timekeeper">
        <p class="feature-explanation">
            <!-- Configure your date labels here to reflect the important stages of your work.
            These fields will appear when creating or viewing an item.
            Click and drag the labels below to change the default order in which they appear on each item. -->
        </p>
        <FormWrapper
            :form="form"
        >
            <div
                class="mb-4"
            >
                <div class="flex">
                    <InputLine
                        ref="labelInput"
                        v-model="current"
                        class="flex-1"
                        :placeholder="placeholderText"
                        maxlength="40"
                        :error="form.errors().getFirst('name')"
                        :disabled="!editObj && reachedMax"
                        @keydown.enter.prevent="submitDate"
                    >
                    </InputLine>
                    <button
                        type="button"
                        class="o-feature-timekeeper__add bg-primary-600 circle-center hover:bg-primary-500"
                        :class="{ 'opacity-25 no-pointer': cannotSubmit }"
                        :disabled="cannotSubmit"
                        @click="submitDate"
                    >
                        <i
                            class="far"
                            :class="editObj ? 'fa-check' : 'fa-plus'"
                        >
                        </i>
                    </button>
                </div>
                <div
                    class="mt-2 text-cm-700 text-xs"
                    :class="{ 'opacity-0': !editObj }"
                >
                    <span class="uppercase">Editing: </span>
                    <span>{{ editObj ? editObj.name : '' }}</span>
                    <button
                        class="ml-2"
                        type="button"
                        title="Click to stop editing"
                        @click="clearEdit"
                    >
                        <i
                            class="fal fa-times text-normal hover:text-primary-600"
                        >
                        </i>
                    </button>
                </div>
            </div>
            <Component
                :is="editObj ? 'div' : 'Draggable'"
                v-model="form.dates"
            >
                <div
                    v-for="(date, index) in form.dates"
                    :key="index"
                    class="bg-primary-100 flex items-center justify-between my-1 px-2 py-1"
                    :class="{ 'cursor-move': !editObj }"
                >
                    <p
                        class="text-sm"
                    >
                        {{ date.name }}
                    </p>
                    <div class="flex">
                        <IconHover
                            class="c-icon-hover--sm"
                            :title="editTitle(index)"
                            :isActive="checkEditActive(index)"
                            @click="editDate(date, index)"
                        >
                        </IconHover>
                        <IconHover
                            class="c-icon-hover--sm"
                            :class="{ 'no-pointer opacity-50': isOneLeft || editObj }"
                            title="Remove this date label"
                            icon="far fa-trash-alt"
                            @click="removeDate(index)"
                        >
                        </IconHover>
                    </div>
                </div>
            </Component>
        </FormWrapper>
        <div
            v-if="reachedMax || isOneLeft"
            class="flex justify-center mt-4"
        >
            <p
                v-if="reachedMax"
                class="o-feature-timekeeper__notice max-w-300p"
            >
                <!-- You have reached the maximum number of date labels -->
            </p>
            <p
                v-if="isOneLeft"
                class="o-feature-timekeeper__notice max-w-300p"
            >
                <!-- There must be at least one date label when this feature is active -->
            </p>
        </div>
    </div>
</template>

<script>

import Draggable from 'vuedraggable';
import IconHover from '@/components/buttons/IconHover.vue';

import providesFeatureFunctions from '@/vue-mixins/settings/providesFeatureFunctions.js';

import { cloneFields } from '@/core/utils.js';

export default {

    name: 'FeatureTimekeeper',
    components: {
        Draggable,
        IconHover,
    },
    mixins: [
        providesFeatureFunctions,
    ],
    props: {
        options: {
            type: Object,
            required: true,
        },
    },
    emits: [
        'submit',
    ],
    data() {
        const form = this.$form(cloneFields({
            dates: [],
        }, this.options));
        return {
            currentDate: '',
            form,
        };
    },
    computed: {
        datesLength() {
            return this.options.dates.length;
        },
        reachedMax() {
            return this.datesLength > 9;
        },
        isOneLeft() {
            return this.datesLength === 1;
        },
        placeholderText() {
            return this.editObj ? `Editing: ${this.editObj.name}` : 'Add a date label';
        },
        cannotSubmit() {
            return !this.current.length || (!this.editObj && this.reachedMax);
        },
    },
    methods: {
        addDate() {
            if (this.checkExisting(this.form.dates, this.current)) {
                this.form.errors().record({
                    name: 'It looks like you already have a date label with this name. Date labels should be unique.',
                }, 5000);
            } else if (this.current.length && !this.reachedMax) {
                this.form.dates.push({
                    name: this.current,
                });
                this.clearInput();
                this.$emit('submit', this.form);
            }
        },
        clearEdit() {
            this.stopEdit();
            this.clearInput();
        },
        editTitle(index) {
            if (this.checkEditActive(index)) {
                return 'Click to stop editing';
            }
            return 'Edit this date label';
        },
        replaceDate() {
            this.form.dates[this.editObj.index].name = this.current;
            this.$emit('submit', this.form);
            this.clearEdit();
        },
        submitDate() {
            if (this.editObj) {
                this.replaceDate();
            } else {
                this.addDate();
            }
        },
        editDate(date, index) {
            if (this.editObj && this.editObj.index === index) {
                this.clearEdit();
            } else {
                this.editObj = {
                    index,
                    ...date,
                };
                this.current = date.name;
            }
        },
        removeDate(index) {
            if (!this.isOneLeft) {
                this.form.dates.splice(index, 1);
                this.$emit('submit', this.form);
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>
.o-feature-timekeeper {
    width: 600px;

    &__add {
        @apply
            cursor-pointer
            h-6
            min-w-6
            ml-3
            text-13p
            text-cm-00
            w-6
        ;
    }

    &__notice {
        @apply
            bg-primary-100
            leading-normal
            px-6
            py-3
            rounded
            text-primary-700
            text-sm
        ;
    }
}
</style>
