<template>
    <div class="o-field-extras">

        <template
            v-if="!$apollo.loading && noCategories && showCategory"
        >
            <p class="text-xssm mt-2 bg-rose-100 p-2 rounded-lg">
                Add categories in your customizations to use this field type.
            </p>
        </template>

        <FieldCategory
            v-if="showCategory && !noCategories"
            v-bind="$attrs"
            :categories="categories"
            :bgColor="bgColor"
            class="mt-4"
            :field="field"
            :isNew="isNew"
            formField="options.category"
        >
        </FieldCategory>

        <div
            v-if="showCategory"
            class="mt-2"
        >
            <button
                class="button--sm button-secondary"
                type="button"
                @click="openModal"
            >
                Customize categories
            </button>
        </div>

        <div
            v-if="showMultiSelect"
            class="mt-4"
        >
            <p
                class="header-uppercase-light mb-2"
            >
                {{ isNew ? 'Single or multiple values?' : 'Selection' }}
            </p>

            <div
                v-if="isNew"
                class="flex"
            >
                <button
                    class="button-rounded--sm mx-1"
                    :class="!multiSelect ? 'button-primary' : 'o-field-extras__off'"
                    type="button"
                    @click="updateMultiSelect(false)"
                >
                    Single
                </button>
                <button
                    class="button-rounded--sm mx-1"
                    :class="multiSelect ? 'button-primary' : 'o-field-extras__off'"
                    type="button"
                    @click="updateMultiSelect(true)"
                >
                    Multiple
                </button>
            </div>

            <div
                v-else
                class="button-rounded--sm bg-cm-200 inline-flex"
            >
                {{ field.options.multiSelect ? 'Multiple' : 'Single' }}
            </div>
        </div>

        <div
            v-if="showImage"
            class="mt-4"
        >
            <p
                class="header-uppercase-light mb-2"
            >
                Set primary image
            </p>

            <CheckHolder
                :disabled="isImageDisabled"
                :modelValue="primary"
                @update:modelValue="updatePrimary"
            >
                Primary
            </CheckHolder>
        </div>

        <div
            v-if="showMoney"
            class="mt-4"
        >
            <p
                class="header-uppercase-light mb-2"
            >
                Fixed or variable currency?
            </p>
            <div
                v-if="isNew"
            >
                <div class="flex">
                    <button
                        class="button-rounded--sm mx-1"
                        :class="fixedCurrency ? 'button-primary' : 'o-field-extras__off'"
                        type="button"
                        @click="updateFixedCurrency('USD')"
                    >
                        Fixed
                    </button>
                    <button
                        class="button-rounded--sm mx-1"
                        :class="!fixedCurrency ? 'button-primary' : 'o-field-extras__off'"
                        type="button"
                        @click="updateFixedCurrency(false)"
                    >
                        Variable
                    </button>
                </div>

                <div
                    v-if="fixedCurrency"
                    class="w-32 mt-2"
                >
                    <CurrencyPicker
                        :modelValue="fixedCurrency"
                        v-bind="$attrs"
                        :bgColor="bgColor"
                        @update:modelValue="updateFixedCurrency"
                    >
                    </CurrencyPicker>
                </div>
            </div>

            <div
                v-else
                class="button-rounded--sm bg-cm-200 inline-flex"
            >
                {{ fixedCurrency ? 'Fixed' : 'Variable' }}
            </div>

        </div>

        <div
            v-if="showSelectOptions"
            class="mt-4"
        >
            <p
                class="header-uppercase-light mb-2"
            >
                What are the {{ fieldValName }} options?
            </p>

            <SetOptions
                :max="15"
                :options="valueOptions"
                :bgColor="bgColor"
                @update:options="updateValueOptions"
            >
            </SetOptions>
        </div>
        <div
            v-if="showIconPicker"
            class="mt-4"
        >
            <p
                class="header-uppercase-light mb-2"
            >
                Select an icon to toggle on / off
            </p>

            <IconEdit
                v-bind="$attrs"
            >
            </IconEdit>
        </div>

        <CustomizeCategoriesModal
            v-if="isModalOpen"
            @closeModal="closeModal"
        >
        </CustomizeCategoriesModal>
    </div>
</template>

<script>

import FieldCategory from '@/components/customize/fields/FieldCategory.vue';
import IconEdit from '@/components/assets/IconEdit.vue';
import SetOptions from '@/components/assets/SetOptions.vue';
import CurrencyPicker from '@/components/pickers/CurrencyPicker.vue';
import CustomizeCategoriesModal from '@/components/customize/CustomizeCategoriesModal.vue';

import interactsWithModal from '@/vue-mixins/interactsWithModal.js';
import BASIC_CATEGORIES from '@/graphql/categories/queries/BasicCategories.gql';
import initializeConnections from '@/http/apollo/initializeConnections.js';

export default {
    name: 'FieldExtras',
    components: {
        FieldCategory,
        IconEdit,
        SetOptions,
        CurrencyPicker,
        CustomizeCategoriesModal,
    },
    mixins: [
        interactsWithModal,
    ],
    props: {
        form: {
            type: Object,
            required: true,
        },
        fixedCurrency: {
            type: [Boolean, String],
            default: 'USD',
        },
        multiSelect: Boolean,
        currentBg: {
            type: String,
            default: 'white',
            validator(val) {
                return ['white', 'gray'].includes(val);
            },
        },
        hasPrimaryImage: Boolean,
        valueOptions: {
            type: Object,
            default: () => ({}),
        },
        primary: Boolean,
        isNew: Boolean,
        field: {
            type: Object,
            required: true,
        },
        subField: Boolean,
    },
    emits: [
        'update:multiSelect',
        'update:valueOptions',
        'update:primary',
        'update:fixedCurrency',
    ],
    apollo: {
        categories: {
            query: BASIC_CATEGORIES,
            update: (data) => initializeConnections(data).categories,
            fetchPolicy: 'cache-first',
        },
    },
    data() {
        return {

        };
    },
    computed: {
        showMoney() {
            return this.form.type === 'MONEY'
                || this.field.type === 'MONEY'
                || this.form.type === 'SALARY'
                || this.field.type === 'SALARY';
        },
        showImage() {
            return !this.subField
                && (this.form.type === 'IMAGE' || this.field.type === 'IMAGE')
                && !this.field?.options?.list;
        },
        isImageDisabled() {
            return !this.hasPrimaryImage
                || this.form.options?.list;
        },
        showCategory() {
            return this.form.type === 'CATEGORY' || this.field.type === 'CATEGORY';
        },
        showMultiSelect() {
            if (this.form.type === 'CATEGORY' && this.noCategories) {
                return false;
            }
            const types = ['CATEGORY', 'DROPDOWN', 'LOCATION', 'CURRENCY'];
            return types.includes(this.form.type)
                || types.includes(this.form.val)
                || types.includes(this.field.type)
                || types.includes(this.field.val);
        },

        showIconPicker() {
            return this.form.val === 'ICON_TOGGLE'
                || this.field.val === 'ICON_TOGGLE';
        },
        showSelectOptions() {
            return this.form.type === 'SELECT' || this.field.type === 'SELECT';
        },
        bgColor() {
            if (this.currentBg === 'gray') {
                return 'white';
            }
            return 'gray';
        },
        fieldValName() {
            const val = this.form.val || this.field.val;
            return this.$t(`fields.types.${_.camelCase(val)}`);
        },
        noCategories() {
            return !this.categories?.length;
        },
    },
    methods: {
        updateMultiSelect(val) {
            this.$emit('update:multiSelect', val);
        },
        updateValueOptions(val) {
            this.$proxyEvent(val, this.valueOptions, '', 'update:valueOptions');
        },
        updatePrimary(val) {
            this.$proxyEvent(val, this.primary, '', 'update:primary');
        },
        updateFixedCurrency(val) {
            this.$emit('update:fixedCurrency', val);
        },
    },
    created() {
    },
};
</script>

<style scoped>

.o-field-extras {
    &__off {
        @apply
            bg-cm-00
        ;

        &:hover {
            @apply
                bg-cm-200
            ;
        }
    }
}

</style>
