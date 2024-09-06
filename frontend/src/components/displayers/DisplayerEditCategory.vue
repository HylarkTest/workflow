<template>
    <div class="c-displayer-edit-category">
        <div
            v-if="!isMulti"
            class="w-full"
        >
            <CategoryItemPicker
                :categoryId="fieldOptions.category"
                :modelValue="modifiableFieldValue"
                comparator="id"
                bgColor="gray"
                :showClear="true"
                @update:modelValue="updateDataValue"
            >
            </CategoryItemPicker>
        </div>

        <template
            v-else
        >
            <div class="w-full mb-3">

                <CategoryItemPicker
                    :categoryId="fieldOptions.category"
                    :modelValue="modifiableFieldValue || []"
                    :placeholderCondition="() => true"
                    bgColor="gray"
                    comparator="id"
                    @update:modelValue="updateDataValue"
                >
                </CategoryItemPicker>
            </div>

            <div
                v-if="isMulti"
                class="list-value-parent"
            >
                <ValueBasic
                    v-for="val in modifiableFieldValue"
                    :key="val.id"
                    @removeValue="removeValue(val)"
                >
                    {{ val.name }}
                </ValueBasic>
            </div>
        </template>
    </div>
</template>

<script>

import interactsWithMultiDisplayers from '@/vue-mixins/displayers/interactsWithMultiDisplayers.js';
import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';
import CategoryItemPicker from '@/components/pickers/CategoryItemPicker.vue';

export default {
    name: 'DisplayerEditCategory',
    components: {
        CategoryItemPicker,
    },
    mixins: [
        interactsWithDisplayersEdit,
        interactsWithMultiDisplayers,
    ],
    props: {

    },
    data() {
        return {

        };
    },
    computed: {

    },
    methods: {
    },
    created() {
    },
};
</script>

<style scoped>

/*.c-displayer-edit-category {

} */

</style>
