<template>
    <div
        class="c-displayer-dropdown text-sm"
    >
        <div
            v-if="!isMulti"
            class="w-36"
        >
            <DropdownBox
                :modelValue="modelFieldValue"
                class="w-full"
                :options="valueOptionsFormatted"
                displayRule="selectValue"
                comparator="selectKey"
                placeholder="Select"
                :showClear="true"
                bgColor="gray"
                @update:modelValue="saveValue"
            >
            </DropdownBox>
        </div>

        <ButtonEl
            v-else
        >
            <div
                v-if="!modelFieldValue"
                class="button-rounded--sm button-primary--light u-ellipsis inline-flex items-center"
            >
                <i class="fal fa-circle-plus mr-1">
                </i>
                {{ dataInfo.name }}
            </div>

            <div
                v-else
                class="list-value-parent"
            >
                <ValueBasic
                    v-for="(val, index) in modelFieldValue"
                    :key="val?.selectKey || index"
                    :hideRemove="!isModifiable"
                    @removeValue="removeOption(val)"
                >
                    {{ val?.selectValue }}
                </ValueBasic>
            </div>
        </ButtonEl>

    </div>
</template>

<script>

import interactsWithDisplayers from '@/vue-mixins/displayers/interactsWithDisplayers.js';
import interactsWithDisplayersSelfEdit from '@/vue-mixins/displayers/interactsWithDisplayersSelfEdit.js';
import interactsWithMultiDisplayers from '@/vue-mixins/displayers/interactsWithMultiDisplayers.js';

export default {
    name: 'DisplayerDropdown',
    components: {

    },
    mixins: [
        interactsWithDisplayers,
        interactsWithDisplayersSelfEdit,
        interactsWithMultiDisplayers,
    ],
    props: {

    },
    emits: [
        'saveField',
        'update:dataValue',
    ],
    data() {
        return {
            typeKey: 'DROPDOWN',
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

/*.c-displayer-dropdown {

} */

</style>
