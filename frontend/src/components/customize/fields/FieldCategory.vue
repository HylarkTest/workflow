<template>
    <div class="o-field-category">

        <h3 class="header-uppercase-light mb-2">
            {{ isNew ? 'Select category' : 'Category' }}
        </h3>

        <CategoryPicker
            v-if="isNew"
            :modelValue="category"
            :bgColor="bgColor"
            property="id"
            :formField="formField"
            @update:modelValue="$emit('update:category', $event)"
        >
        </CategoryPicker>

        <div
            v-else-if="categoryObject"
            class="text-sm font-semibold"
        >
            {{ categoryObject.name }}
        </div>
    </div>
</template>

<script>

import CategoryPicker from '@/components/pickers/CategoryPicker.vue';

export default {
    name: 'FieldCategory',
    components: {
        CategoryPicker,
    },
    mixins: [
    ],
    props: {
        category: {
            type: [String, null],
            required: true,
        },
        bgColor: {
            type: String,
            default: 'gray',
        },
        isNew: Boolean,
        formField: {
            type: String,
            default: '',
        },
        categories: {
            type: [null, Array],
            default: null,
        },
    },
    emits: [
        'update:category',
    ],
    data() {
        return {

        };
    },
    computed: {
        categoryObject() {
            return _.find(this.categories, { id: this.category });
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<!-- <style scoped>
.o-field-category {

}
</style> -->
