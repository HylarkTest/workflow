<template>
    <div class="o-feature-collaboration">
        <p class="feature-explanation">
            <!-- Users can be assigned to these roles when creating or viewing an item.
            Click and drag the roles below to change their order in the list. -->
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
                        @keydown.enter.prevent="submitRole"
                    >
                    </InputLine>
                    <button
                        type="button"
                        class="o-feature-collaboration__add bg-primary-600 circle-center hover:bg-primary-500"
                        :class="{ 'opacity-25 no-pointer': cannotSubmit }"
                        :disabled="cannotSubmit"
                        @click="submitRole"
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
                v-model="form.roles"
            >
                <div
                    v-for="(role, index) in form.roles"
                    :key="index"
                    class="o-feature-collaboration__sortable"
                    :class="{ 'cursor-move': !editObj }"
                >
                    <p>{{ role.name }}</p>
                    <div class="flex">
                        <IconHover
                            class="c-icon-hover--sm"
                            :title="editTitle(index)"
                            :isActive="checkEditActive(index)"
                            @click="editRole(role, index)"
                        >
                        </IconHover>
                        <IconHover
                            class="c-icon-hover--sm"
                            :class="{ 'no-pointer opacity-50': isOneLeft || editObj }"
                            title="Remove this role"
                            icon="far fa-trash-alt"
                            @click="removeRole(index)"
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
                class="o-feature-collaboration__notice max-w-300p"
            >
                <!-- You have reached the maximum number of roles -->
            </p>
            <p
                v-if="isOneLeft"
                class="o-feature-collaboration__notice max-w-300p"
            >
                <!-- There must be at least one role when this feature is active -->
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

    name: 'FeatureCollaboration',
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
            roles: [],
        }, this.options));
        return {
            form,
        };
    },
    computed: {
        reachedMax() {
            return this.rolesLength > 9;
        },
        rolesLength() {
            return this.options.roles && this.options.roles.length;
        },
        isOneLeft() {
            return this.rolesLength === 1;
        },
        placeholderText() {
            return this.editObj ? `Editing: ${this.editObj.name}` : 'Add a role';
        },
        cannotSubmit() {
            return !this.current.length || (!this.editObj && this.reachedMax);
        },
    },
    methods: {
        addRole() {
            if (this.checkExisting(this.form.roles, this.current)) {
                this.form.errors().record({
                    name: 'It looks like you already have a role with this name. Role names should be unique.',
                }, 5000);
            } else if (this.current.length && !this.reachedMax) {
                this.form.roles.push({
                    name: this.current,
                });
                this.clearInput();
                this.$emit('submit', this.form);
            }
        },
        editTitle(index) {
            if (this.checkEditActive(index)) {
                return 'Click to stop editing';
            }
            return 'Edit this role';
        },
        replaceRole() {
            this.form.roles[this.editObj.index].name = this.current;
            this.$emit('submit', this.form);
            this.clearEdit();
        },
        submitRole() {
            if (this.editObj) {
                this.replaceRole();
            } else {
                this.addRole();
            }
        },
        clearEdit() {
            this.stopEdit();
            this.clearInput();
        },
        editRole(role, index) {
            if (this.editObj && this.editObj.index === index) {
                this.clearEdit();
            } else {
                this.editObj = {
                    index,
                    ...role,
                };
                this.current = role.name;
            }
        },
        removeRole(index) {
            if (!this.isOneLeft) {
                this.form.roles.splice(index, 1);
                this.$emit('submit', this.form);
            }
        },
    },
    created() {

    },
};
</script>

<style scoped>
.o-feature-collaboration {
    width: 500px;

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

    .sortable-ghost {
        @apply
            bg-cm-00
        ;
    }

    &__sortable {
        @apply
            bg-cm-00
            flex
            items-center
            justify-between
            p-1
            text-sm
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
