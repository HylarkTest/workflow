<template>
    <div class="c-displayer-edit-dropdown">
        <div
            v-if="!isMulti"
            class="w-40"
        >
            <DropdownBox
                :modelValue="modifiableFieldValue"
                class="w-full"
                :options="valueOptionsFormatted"
                displayRule="selectValue"
                comparator="selectKey"
                placeholder="Select"
                :showClear="true"
                bgColor="gray"
                @update:modelValue="updateDataValue"
            >
            </DropdownBox>
        </div>

        <template
            v-else
        >
            <div class="mb-6">
                <DropdownFree
                    :modelValue="modifiableFieldValue || []"
                    class="w-full"
                    :options="valueOptionsFormatted"
                    comparator="selectKey"
                    displayRule="selectValue"
                    placeholder="Select"
                    @update:modelValue="updateDataValue"
                >
                    <template
                        #selected="{ selectedEvents }"
                    >
                        <button
                            class="button-rounded--sm button-primary--light u-ellipsis"
                            type="button"
                            @click.stop="selectedEvents.click"
                        >
                            <i class="fal fa-circle-plus mr-0.5">
                            </i>
                            {{ dataInfo.name }}
                        </button>
                    </template>
                </DropdownFree>
            </div>

            <div
                class="list-value-parent"
            >
                <ValueBasic
                    v-for="val in formattedData"
                    :key="val.selectKey"
                    @removeValue="removeValue(val, 'selectKey')"
                >
                    {{ val.selectValue }}
                </ValueBasic>
            </div>
        </template>
    </div>
</template>

<script>

import interactsWithMultiDisplayers from '@/vue-mixins/displayers/interactsWithMultiDisplayers.js';
import interactsWithDisplayersEdit from '@/vue-mixins/displayers/interactsWithDisplayersEdit.js';

export default {
    name: 'DisplayerEditDropdown',
    components: {

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
        fieldValue() {
            return this.modifiableFieldValue;
        },
    },
    methods: {

    },
    created() {

    },
};
</script>

<style scoped>

/*.c-displayer-edit-dropdown {

} */

</style>
