<template>
    <div class="o-timekeeper-individual">
        <FormWrapper
            :form="form"
            @submit="saveDeadlines"
        >
            <div
                v-if="phase"
                class="flex items-center mb-10"
            >
                <h2
                    v-t="'timekeeper.phase'"
                    class="header-2 mr-4"
                >
                </h2>
                <TimekeeperPhase
                    v-if="phase"
                    :phase="phase"
                >
                </TimekeeperPhase>
            </div>
            <div class="mb-10">
                <h2
                    v-t="'timekeeper.deadlineDates'"
                    class="header-2 mb-2"
                >
                </h2>

                <div
                    class="mb-3"
                >
                    <h3
                        v-t="'labels.start'"
                        class="font-semibold text-cm-500"
                    >
                    </h3>

                    <TimekeeperDate
                        v-model:dateTime="form.startAt"
                        :editing="editingStart"
                        :savedValue="startAt"
                        :processing="processingStart"
                        @setEdit="setStartEdit($event, 'startAt')"
                        @removeDate="removeStart"
                    >
                    </TimekeeperDate>
                </div>

                <div>
                    <h3
                        v-t="'labels.due'"
                        class="font-semibold text-cm-500"
                    >
                    </h3>

                    <TimekeeperDate
                        v-model:dateTime="form.dueBy"
                        :editing="editingDue"
                        :savedValue="dueBy"
                        :processing="processingDue"
                        :error="form.errors().getFirst('dueBy')"
                        @setEdit="setDueEdit($event, 'dueBy')"
                        @removeDate="removeDue"
                    >
                    </TimekeeperDate>
                </div>

                <SaveButton
                    class="mt-2"
                    buttonClass="button"
                    :disabled="!showSave"
                    :pulse="true"
                >
                </SaveButton>

            </div>

            <div>
                <h2
                    v-t="'timekeeper.completion'"
                    class="header-2"
                >
                </h2>

                <p
                    v-if="isCompleted"
                    class="text-sm mb-3"
                >
                    {{ $t('timekeeper.isComplete', { itemName: item.name }) }}
                </p>
                <p
                    v-else
                    class="text-sm mb-3"
                >
                    {{ $t('timekeeper.markComplete', { itemName: item.name }) }}
                </p>
                <button
                    v-if="deadlines && !isCompleted"
                    class="inline-flex"
                    type="button"
                    @click="completeItem"
                >
                    <div
                        class="o-timekeeper-individual__done button hover:bg-emerald-500 shadow-emerald-600/20"
                    >
                        <i
                            class="far fa-circle-check mr-1"
                        >
                        </i>
                        {{ $t('timekeeper.done') }}
                    </div>
                </button>

                <button
                    v-if="deadlines && isCompleted"
                    class="inline-flex"
                    type="button"
                    @click="incompleteItem"
                >
                    <div
                        class="button bg-cm-200 hover:bg-cm-300 shadow-lg"
                    >
                        <i
                            class="far fa-backward mr-1"
                        >
                        </i>
                        {{ $t('timekeeper.markIncomplete') }}
                    </div>
                </button>
            </div>
        </FormWrapper>
    </div>
</template>

<script>

import TimekeeperDate from './TimekeeperDate.vue';
import TimekeeperPhase from './TimekeeperPhase.vue';

import { completeItem, updateDeadlines } from '@/core/repositories/itemRepository.js';

export default {
    name: 'TimekeeperIndividual',
    components: {
        TimekeeperDate,
        TimekeeperPhase,
    },
    mixins: [
    ],
    props: {
        item: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            form: this.$apolloForm({
                id: this.item.id,
                startAt: this.item.deadlines.startAt || null,
                dueBy: this.item.deadlines.dueBy || null,
            }, {
                timeout: 3000,
            }),
            editingStart: false,
            editingDue: false,
            processingStart: false,
            processingDue: false,
        };
    },
    computed: {
        isCompleted() {
            return this.deadlines.isCompleted;
        },
        deadlines() {
            return this.item.deadlines;
        },
        phase() {
            return this.deadlines.status;
        },
        startAt() {
            return this.deadlines.startAt;
        },
        dueBy() {
            return this.deadlines.dueBy;
        },
        showSave() {
            return this.editingDue
                || this.editingStart
                || this.startAt !== this.form.startAt
                || this.dueBy !== this.form.dueBy;
        },
    },
    methods: {
        async saveDeadlines() {
            await updateDeadlines(this.form, this.item.mapping);
            this.editingDue = false;
            this.editingStart = false;
            this.processingDue = false;
            this.processingStart = false;
        },
        completeItem() {
            completeItem(this.item, this.item.mapping);
        },
        incompleteItem() {
            completeItem(this.item, this.item.mapping, false);
        },
        removeStart() {
            this.processingStart = true;
            this.form.startAt = null;
            this.saveDeadlines();
        },
        removeDue() {
            this.processingDue = true;
            this.form.dueBy = null;
            this.saveDeadlines();
        },
        setStartEdit(val, formKey = null) {
            if (!val && formKey) {
                this.form[formKey] = this.deadlines[formKey];
            }
            this.editingStart = val;
        },
        setDueEdit(val, formKey = null) {
            if (!val && formKey) {
                this.form[formKey] = this.deadlines[formKey];
            }
            this.editingDue = val;
        },
    },
    created() {

    },
};
</script>

<style scoped>

.o-timekeeper-individual {
    &__done {
        @apply
            bg-emerald-600
            shadow-lg
            text-cm-00
        ;
    }
}

</style>
