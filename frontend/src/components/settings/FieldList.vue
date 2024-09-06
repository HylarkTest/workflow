<template>
    <div class="o-field-list">
        <h4 class="settings-form__title">
            Number of values
        </h4>
        <div>
            <CheckHolder
                v-model="listValues"
                class="mb-2"
                type="radio"
                :val="false"
            >
                <div>
                    Single
                </div>
            </CheckHolder>
            <CheckHolder
                v-model="listValues"
                type="radio"
                :val="true"
            >
                <div>
                    List
                </div>
            </CheckHolder>
        </div>
        <div
            v-if="listValues"
            class="mt-8"
        >
            <h5 class="font-semibold mb-3 text-sm">
                Maximum number of values allowed in this list
            </h5>
            <InputLine
                class="w-20"
                type="number"
                max="999"
                min="2"
                :modelValue="list.max"
                @update:modelValue="$proxyEvent($event, list, 'max', 'update:list')"
            >
            </InputLine>
        </div>
        <InputHeader
            v-if="listValues"
            class="mt-8"
        >
            <ToggleButton
                :modelValue="list.oneRequired"
                @update:modelValue="$proxyEvent($event, list, 'oneRequired', 'update:list')"
            >
            </ToggleButton>
            <template #header>
                Require at least one value in the list
            </template>
        </InputHeader>
    </div>
</template>

<script>

import InputHeader from '@/components/display/InputHeader.vue';

const listObject = {
    oneRequired: false,
    max: 5,
};

export default {

    name: 'FieldList',
    components: {
        InputHeader,
    },
    mixins: [
    ],
    props: {
        list: {
            type: Object,
            default: null,
        },
    },
    emits: [
        'update:list',
    ],
    data() {
        return {

        };
    },
    computed: {
        listValues: {
            get() {
                return !!this.list;
            },
            set(value) {
                if (value) {
                    this.$emit('update:list', listObject);
                } else {
                    this.$emit('update:list', null);
                }
            },
        },
    },
    methods: {
    },
    created() {

    },
};
</script>

<!-- <style scoped>
.o-field-list {

}
</style> -->
